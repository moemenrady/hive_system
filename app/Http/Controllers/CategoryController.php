<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'تم إضافة الصنف بنجاح!');
    }

    // دالة الحذف الجديدة
    public function destroy(Category $category)
    {
        // خطوة اختيارية: التأكد أن الصنف لا يحتوي على منتجات قبل الحذف
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف هذا الصنف لأنه مرتبط بمنتجات!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'تم حذف الصنف بنجاح!');
    }
}
