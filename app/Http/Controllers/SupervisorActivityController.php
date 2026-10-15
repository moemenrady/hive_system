<?php

namespace App\Http\Controllers;

use App\Models\SupervisorActivity;
use Illuminate\Http\Request;

class SupervisorActivityController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'supervisor') {
          return   response()->json(['message' => 'Unauthorized'], 403);
        }
        // جلب البيانات مع العلاقة وترتيبها من الأحدث للأقدم، ثم تجميعها حسب المشرف
        $groupedActivities = SupervisorActivity::with('supervisor')
            ->latest()
            ->get()
            ->groupBy('supervisor_id');

        return view('supervisor.activities.index', compact('groupedActivities'));
    }

    // تحديث بيانات الحركة
    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = SupervisorActivity::findOrFail($id);
        $activity->update($request->only(['action', 'description']));

        return redirect()->back()->with('success', 'تم تعديل الحركة بنجاح!');
    }

    // حذف الحركة
    public function destroy($id)
    {
        $activity = SupervisorActivity::findOrFail($id);
        $activity->delete();

        return redirect()->back()->with('success', 'تم حذف الحركة بنجاح!');
    }
}
