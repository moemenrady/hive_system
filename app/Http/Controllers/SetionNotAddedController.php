<?php

namespace App\Http\Controllers;

use App\Models\SetionNotAdded;
use Illuminate\Http\Request;

class SetionNotAddedController extends Controller
{
public function clearAll()
{
    SetionNotAdded::truncate(); 
    // أو delete() لو خايف على auto increment

    return back()->with('success', 'تم حذف جميع الجلسات غير المسجلة');
}

public function bulkDelete(Request $request)
{
    if (!$request->filled('ids')) {
        return back()->with('error', 'لم يتم تحديد أي جلسة');
    }

    $ids = array_filter(explode(',', $request->ids));

    SetionNotAdded::whereIn('id', $ids)->delete();

    return back()->with('success', 'تم حذف الجلسات المحددة');
}

  public function ajaxNotAdded(Request $request)
  {
    $query = $request->get('q', '');
    $sessions = SetionNotAdded::with('client')
      ->when($query, fn($q) => $q->whereHas('client', fn($q2) => $q2->where('name', 'like', "%$query%")->orWhere('phone', 'like', "%$query%")))
      ->get();
    return response()->json($sessions);
  }

  public function index(Request $request)
  {
    $sessions = SetionNotAdded::get();
    return view('setion-not-added.index', compact("sessions"));
  }
  public function store(Request $request)
  {
    // التحقق من البيانات الأساسية
    $request->validate([
      'client_id' => 'required|exists:clients,id',
      'persons' => 'required|integer|min:1',
    ]);

    $clientId = $request->client_id;

    // 1️⃣ التحقق من وجود جلسة نشطة حالية
    $existingActive = SetionNotAdded::where('client_id', $clientId)
      ->where('start_time', '<=', now())   // الجلسة بدأت
      ->where('start_time', '>=', now()->subHours(2)) // أو مدة افتراضية للجلسة
      ->first();

    if ($existingActive) {
      return response()->json([
        'success' => false,
        'message' => 'هذا العميل لديه جلسة نشطة بالفعل.'
      ], 400);
    }

    // 2️⃣ التحقق من وجود أي سجل سابق للعميل في الجدول
    $existsAny = SetionNotAdded::where('client_id', $clientId)->exists();

    if ($existsAny) {
      return response()->json([
        'success' => false,
        'message' => 'هذا العميل موجود بالفعل في سجل الجلسات.'
      ], 400);
    }

    // إنشاء سجل جديد
    $setion = SetionNotAdded::create([
      'client_id' => $clientId,
      'persons' => $request->persons,
      'start_time' => $request->start_time,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'تم تخزين بيانات الجلسة بنجاح',
      'data' => $setion
    ]);
  }
  public function destroy($id)
  {
    $session = SetionNotAdded::find($id);

    if (!$session) {
      return redirect()->back()->with('error', 'الجلسة غير موجودة');
    }

    $session->delete();

    return redirect()->back()->with('success', 'تم حذف الجلسة بنجاح');
  }

}
