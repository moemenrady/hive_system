<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    // جلب الاشعارات
    public function index()
    {
        $notifications = Notification::where('is_read', false)
            ->latest()
            ->get();

        return response()->json($notifications);
    }


    // تحويل الاشعار لمقروء
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => true
        ]);

        return response()->json([
            'success' => true
        ]);
    }


    // حذف الاشعار
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true
        ]);
    }

}