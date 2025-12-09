<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use DB;

class EducationStagesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $stages = [
      'الابتدائية',
      'الإعدادية',
      'الثانوية',
      'الجامعة',
      'دراسات عليا',
      'ماجستير',
      'دكتوراه',
      'خريج',
    ];

    foreach ($stages as $name) {
      DB::table('education_stages')->updateOrInsert(
        ['name' => $name], // إذا موجود مسبقًا يتخطى
        ['slug' => Str::slug($name), 'created_at' => now(), 'updated_at' => now()]
      );
    }
  }
}
