<?php

namespace Tests\Feature;

use App\Models\RefundRequest;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundRequestSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

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
    }

    public function test_employee_cannot_submit_refund_without_open_shift(): void
    {
        $response = $this->actingAs($this->staff)->post(route('refund-requests.store'), [
            'amount' => 50,
            'notes' => 'مرتجع مشروب خاطئ',
            'payment_type' => 'cash',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('لا يوجد شيفت مفتوح', session('error'));

        $this->assertDatabaseCount('refund_requests', 0);
        $this->assertDatabaseCount('shift_actions', 0);
    }

    public function test_employee_with_open_shift_submits_refund_request_successfully(): void
    {
        // 1. فتح شيفت للموظف مع حركة إيراد أولية
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 500,
            'total_expense' => 0,
        ]);
        ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'separate_sale',
            'amount' => 500.00,
            'expense_amount' => 0,
            'notes' => 'مبيعات أولية في الشيفت',
        ]);

        // 2. إرسال طلب الارتجاع
        $response = $this->actingAs($this->staff)->post(route('refund-requests.store'), [
            'amount' => 75.50,
            'notes' => 'العميل ألغى طلب قهوة تم دفعها كاش',
            'payment_type' => 'cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 3. التحقق من إنشاء RefundRequest بحالة معلقة
        $this->assertDatabaseHas('refund_requests', [
            'created_by' => $this->staff->id,
            'shift_id' => $shift->id,
            'amount' => 75.50,
            'notes' => 'العميل ألغى طلب قهوة تم دفعها كاش',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        // 4. التحقق من تسجيل الحركة في الشيفت وخصم المبلغ
        $this->assertDatabaseHas('shift_actions', [
            'shift_id' => $shift->id,
            'action_type' => 'refund_request',
            'expense_amount' => 75.50,
            'amount' => 0,
        ]);

        $shift->refresh();
        $this->assertEquals(75.50, $shift->total_expense);
        $this->assertEquals(424.50, $shift->net_profit); // 500 - 75.50
    }

    public function test_admin_can_view_refund_requests_list(): void
    {
        // مستخدم عادي لا يملك صلاحية دخول صفحة إدارة المرتجعات
        $forbiddenResponse = $this->actingAs($this->staff)->get(route('admin.refund-requests.index'));
        $forbiddenResponse->assertStatus(403);

        // الأدمن يملك صلاحية الدخول
        $adminResponse = $this->actingAs($this->admin)->get(route('admin.refund-requests.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('إدارة طلبات الارتجاع للموظفين');
    }

    public function test_admin_can_approve_pending_refund_request(): void
    {
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 1000,
            'total_expense' => 120,
        ]);

        $refund = RefundRequest::create([
            'created_by' => $this->staff->id,
            'shift_id' => $shift->id,
            'amount' => 120.00,
            'notes' => 'مرتجع شاي بالنعناع لعدم توفر نعناع',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'refund_request',
            'refund_request_id' => $refund->id,
            'amount' => 0,
            'expense_amount' => 120.00,
            'notes' => 'طلب ارتجاع (معلق): مرتجع شاي بالنعناع',
        ]);

        // الأدمن يؤكد الطلب
        $response = $this->actingAs($this->admin)->post(route('admin.refund-requests.approve', $refund->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refund->refresh();
        $this->assertTrue($refund->isApproved());
        $this->assertEquals($this->admin->id, $refund->approved_by);
        $this->assertNotNull($refund->approved_at);

        // الخصم يظل مستقراً ومؤكداً على الشيفت
        $shift->refresh();
        $this->assertEquals(120.00, $shift->total_expense);
    }

    public function test_admin_can_reject_refund_request_and_reverses_shift_deduction(): void
    {
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 1000,
            'total_expense' => 200, // مخصوم منه الارتجاع
        ]);

        $refund = RefundRequest::create([
            'created_by' => $this->staff->id,
            'shift_id' => $shift->id,
            'amount' => 200.00,
            'notes' => 'مرتجع بدون سبب واضح',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'refund_request',
            'refund_request_id' => $refund->id,
            'amount' => 0,
            'expense_amount' => 200.00,
            'notes' => 'طلب ارتجاع: بدون سبب',
        ]);

        // الأدمن يرفض الطلب مع سبب الرفض
        $response = $this->actingAs($this->admin)->post(route('admin.refund-requests.reject', $refund->id), [
            'rejection_reason' => 'ممنوع استرجاع المنتجات المستهلكة جزئياً',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refund->refresh();
        $this->assertTrue($refund->isRejected());
        $this->assertEquals($this->admin->id, $refund->approved_by);
        $this->assertEquals('ممنوع استرجاع المنتجات المستهلكة جزئياً', $refund->rejection_reason);

        // تم إلغاء الخصم من الشيفت وعودة total_expense إلى 0
        $shift->refresh();
        $this->assertEquals(0, $shift->total_expense);

        // تم تصفير المصروف في الحركة الأصلية
        $this->assertDatabaseHas('shift_actions', [
            'shift_id' => $shift->id,
            'action_type' => 'refund_request',
            'refund_request_id' => $refund->id,
            'expense_amount' => 0,
        ]);

        // تم تسجيل حركة تعويضية في ShiftAction
        $this->assertDatabaseHas('shift_actions', [
            'shift_id' => $shift->id,
            'action_type' => 'refund_rejected',
            'refund_request_id' => $refund->id,
        ]);
    }

    public function test_employee_can_view_own_refund_requests(): void
    {
        RefundRequest::create([
            'created_by' => $this->staff->id,
            'amount' => 50,
            'notes' => 'طلبي الأول',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staff)->get(route('refund-requests.index'));
        $response->assertStatus(200);
        $response->assertSee('طلبي الأول');
        $response->assertSee('طلب ارتجاع جديد');
    }

    public function test_validation_requires_notes_and_positive_amount(): void
    {
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 500,
            'total_expense' => 0,
        ]);

        // ملاحظة فارغة
        $responseNoNotes = $this->actingAs($this->staff)->post(route('refund-requests.store'), [
            'amount' => 50,
            'notes' => '',
            'payment_type' => 'cash',
        ]);
        $responseNoNotes->assertSessionHasErrors('notes');

        // مبلغ صفر أو سالب
        $responseInvalidAmount = $this->actingAs($this->staff)->post(route('refund-requests.store'), [
            'amount' => -10,
            'notes' => 'ملاحظة تجريبية',
            'payment_type' => 'cash',
        ]);
        $responseInvalidAmount->assertSessionHasErrors('amount');
    }

    public function test_unauthorized_user_cannot_approve_or_reject_refund(): void
    {
        $refund = RefundRequest::create([
            'created_by' => $this->staff->id,
            'amount' => 60,
            'notes' => 'طلب قيد الانتظار',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        // موظف عادي يحاول الاعتماد -> 403
        $approveResponse = $this->actingAs($this->staff)->post(route('admin.refund-requests.approve', $refund->id));
        $approveResponse->assertStatus(403);

        // موظف عادي يحاول الرفض -> 403
        $rejectResponse = $this->actingAs($this->staff)->post(route('admin.refund-requests.reject', $refund->id));
        $rejectResponse->assertStatus(403);
    }

    public function test_cannot_approve_or_reject_already_processed_refund(): void
    {
        $refund = RefundRequest::create([
            'created_by' => $this->staff->id,
            'amount' => 80,
            'notes' => 'طلب معتمد مسبقاً',
            'payment_type' => 'cash',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // محاولة اعتماد طلب معتمد بالفعل
        $response = $this->actingAs($this->admin)->post(route('admin.refund-requests.approve', $refund->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('تم اتخاذ إجراء عليه مسبقاً', session('error'));

        // محاولة رفض طلب معتمد بالفعل
        $response2 = $this->actingAs($this->admin)->post(route('admin.refund-requests.reject', $refund->id));
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('تم اتخاذ إجراء عليه مسبقاً', session('error'));
    }

    public function test_shift_details_page_renders_refund_action_and_badge(): void
    {
        $shift = Shift::create([
            'user_id' => $this->staff->id,
            'start_time' => now(),
            'total_amount' => 300,
            'total_expense' => 50,
        ]);

        $refund = RefundRequest::create([
            'created_by' => $this->staff->id,
            'shift_id' => $shift->id,
            'amount' => 50.00,
            'notes' => 'استرجاع مشروب بارد',
            'payment_type' => 'cash',
            'status' => 'pending',
        ]);

        ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'refund_request',
            'refund_request_id' => $refund->id,
            'amount' => 0,
            'expense_amount' => 50.00,
            'notes' => 'طلب ارتجاع: استرجاع مشروب بارد',
        ]);

        // فتح صفحة تفاصيل الشيفت
        $response = $this->actingAs($this->admin)->get(route('shift.show', $shift->id));
        $response->assertStatus(200);
        $response->assertSee('طلب ارتجاع');
        $response->assertSee('استرجاع مشروب بارد');
        $response->assertSee('معلق');
    }
}
