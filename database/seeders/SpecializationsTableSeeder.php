<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use DB;

class SpecializationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            'برمجة',
            'تصميم جرافيك',
            'شبكات',
            'هندسة برمجيات',
            'ذكاء اصطناعي',
            'تحليل بيانات',
            'أمن سيبراني',
            'إدارة نظم',
            'تسويق رقمي',
            'إلكترونيات',
            'حاسوب سحابي',
            'روبوتات',
            'تطوير تطبيقات الموبايل',
            'واقع افتراضي',
            'تجارة إلكترونية',
            'محاسبة',
            'مالية',
            'إدارة أعمال',
            'لغات',
            'إعلام'
        ];

        foreach ($specializations as $name) {
            DB::table('specializations')->updateOrInsert(
                ['name' => $name], // إذا موجود مسبقًا يتخطى
                ['slug' => Str::slug($name), 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
