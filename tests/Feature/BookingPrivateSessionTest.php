<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Hall;
use App\Models\Shift;
use App\Models\User;
use App\Services\BookingConflictService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingPrivateSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Hall $hall;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->hall = Hall::create([
            'name' => 'قاعة VIP',
            'min_capacity' => 2,
            'max_capacity' => 10,
        ]);

        DB::table('venue_pricing')->insert([
            'base_hour_price' => 100.00,
            'setter_name' => 'Admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_booking_conflict_service_methods(): void
    {
        $service = app(BookingConflictService::class);

        // لا يوجد أي حجز حالياً
        $this->assertFalse($service->hasInProgressConflict($this->hall->id));
        $this->assertNull($service->getInProgressBooking($this->hall->id));

        $client = Client::create(['name' => 'عميل تجريبي', 'phone' => '01012345678']);

        // إنشاء حجز جاري
        $booking = Booking::create([
            'hall_id' => $this->hall->id,
            'client_id' => $client->id,
            'title' => 'حجز جاري تجريبي',
            'attendees' => 4,
            'start_at' => now()->subMinutes(10),
            'real_start_at' => now()->subMinutes(10),
            'end_at' => now()->addMinutes(50),
            'duration_minutes' => 60,
            'status' => 'in_progress',
            'base_hour_price' => 100,
            'extra_person_hour_price' => 50,
            'estimated_total' => 100,
            'min_capacity_snapshot' => 2,
        ]);

        // يجب أن يكتشف الحجز الجاري
        $this->assertTrue($service->hasInProgressConflict($this->hall->id));
        $this->assertNotNull($service->getInProgressBooking($this->hall->id));
        $this->assertEquals($booking->id, $service->getInProgressBooking($this->hall->id)->id);

        // استبعاد نفس الحجز
        $this->assertFalse($service->hasInProgressConflict($this->hall->id, $booking->id));

        // فحص التعارض الزمني
        $this->assertTrue($service->hasConflict($this->hall->id, now()->subMinutes(5), now()->addMinutes(30)));
    }

    public function test_check_ongoing_api_endpoint(): void
    {
        // 1. القاعة فارغة
        $response = $this->actingAs($this->staff)->getJson(route('bookings.check_ongoing', ['hall_id' => $this->hall->id]));
        $response->assertStatus(200);
        $response->assertJson(['ongoing' => false]);

        // 2. إنشاء حجز جاري
        $client = Client::create(['name' => 'محمد أحمد', 'phone' => '01099887766']);
        Booking::create([
            'hall_id' => $this->hall->id,
            'client_id' => $client->id,
            'title' => 'حجز نشط',
            'attendees' => 2,
            'start_at' => now(),
            'real_start_at' => now(),
            'end_at' => now()->addMinutes(60),
            'duration_minutes' => 60,
            'status' => 'in_progress',
            'base_hour_price' => 100,
            'extra_person_hour_price' => 50,
            'estimated_total' => 100,
            'min_capacity_snapshot' => 2,
        ]);

        // 3. القاعة مشغولة بحجز جاري
        $response2 = $this->actingAs($this->staff)->getJson(route('bookings.check_ongoing', ['hall_id' => $this->hall->id]));
        $response2->assertStatus(200);
        $response2->assertJson(['ongoing' => true]);
        $this->assertStringContainsString('محمد أحمد', $response2->json('message'));
    }

    public function test_estimate_api_endpoint(): void
    {
        $response = $this->actingAs($this->staff)->getJson(route('bookings.estimate', [
            'hall_id' => $this->hall->id,
            'attendees' => 4,
            'duration_minutes' => 60,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotNull($response->json('estimated'));
    }

    public function test_start_booking_now_creates_private_session_successfully(): void
    {
        // فتح شيفت للموظف
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 0,
            'total_expense' => 0,
        ]);

        $response = $this->actingAs($this->staff)->post(route('bookings.start-now'), [
            'hall_id' => $this->hall->id,
            'client_name' => 'أحمد علي',
            'client_phone' => '01012345678',
            'attendees' => 3,
            'duration_minutes' => 60,
        ]);

        $response->assertRedirect(route('bookings.index-manager'));
        $response->assertSessionHas('success');

        // التأكد من إنشاء الحجز في قاعدة البيانات
        $this->assertDatabaseHas('bookings', [
            'hall_id' => $this->hall->id,
            'attendees' => 3,
            'status' => 'in_progress',
        ]);

        // التأكد من إنشاء الفاتورة
        $booking = Booking::where('hall_id', $this->hall->id)->first();
        $this->assertNotNull($booking);
        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'type' => 'booking',
        ]);
    }

    public function test_start_booking_now_blocks_when_hall_has_ongoing_booking(): void
    {
        $client = Client::create(['name' => 'عميل حالي', 'phone' => '01011112222']);
        Booking::create([
            'hall_id' => $this->hall->id,
            'client_id' => $client->id,
            'title' => 'حجز حالي',
            'attendees' => 2,
            'start_at' => now(),
            'real_start_at' => now(),
            'end_at' => now()->addMinutes(60),
            'duration_minutes' => 60,
            'status' => 'in_progress',
            'base_hour_price' => 100,
            'extra_person_hour_price' => 50,
            'estimated_total' => 100,
            'min_capacity_snapshot' => 2,
        ]);

        // محاولة بدء جلسة خاصة في نفس القاعة
        $response = $this->actingAs($this->admin)->post(route('bookings.start-now'), [
            'hall_id' => $this->hall->id,
            'client_name' => 'عميل جديد',
            'client_phone' => '01099998888',
            'attendees' => 2,
            'duration_minutes' => 60,
        ]);

        // يجب منعه مع رسالة خطأ وإعادة المدخلات
        $response->assertSessionHas('error');
        $this->assertStringContainsString('لا يمكن بدء الحجز', session('error'));
    }

    public function test_start_booking_now_validation_fails_on_missing_fields(): void
    {
        $response = $this->actingAs($this->staff)->post(route('bookings.start-now'), [
            'hall_id' => '',
            'client_name' => '',
            'client_phone' => '',
            'attendees' => 0,
            'duration_minutes' => -10,
        ]);

        $response->assertSessionHasErrors(['hall_id', 'client_name', 'attendees', 'duration_minutes']);
    }

    public function test_start_booking_now_reuses_existing_client_by_phone(): void
    {
        $existingClient = Client::create([
            'name' => 'سارة محمود',
            'phone' => '01144556677',
        ]);

        $response = $this->actingAs($this->staff)->post(route('bookings.start-now'), [
            'hall_id' => $this->hall->id,
            'client_name' => 'سارة محمود القديمة',
            'client_phone' => '01144556677',
            'attendees' => 2,
            'duration_minutes' => 90,
        ]);

        $response->assertSessionHas('success');

        // يجب ألا يتكرر العميل بل يُربط بالعميل الموجود
        $this->assertEquals(1, Client::where('phone', '01144556677')->count());
        $booking = Booking::where('hall_id', $this->hall->id)->first();
        $this->assertEquals($existingClient->id, $booking->client_id);
    }
}
