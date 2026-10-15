<?php

namespace App\Http\Controllers;

use App\Models\BlackList;
use App\Models\Client;
use Illuminate\Http\Request;

class BlackListController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($client->activeBlackList()->exists()) {
            return back()->with('error', 'العميل موجود بالفعل في القائمة السوداء.');
        }

        BlackList::create([
            'client_id' => $client->id,
            'reason' => $request->reason,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة العميل إلى القائمة السوداء.');
    }

    public function destroy(Client $client)
    {
        $blackList = $client->activeBlackList;

        if (!$blackList) {
            return back()->with('error', 'العميل غير محظور.');
        }

        $blackList->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'تم إزالة العميل من القائمة السوداء.');
    }
}