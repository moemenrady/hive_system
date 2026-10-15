<?php

namespace App\Http\Controllers;

use App\Models\Sation;
use App\Models\SessionPurchase;
use GuzzleHttp\Psr7\Request;

class PrintController extends Controller
{
    /**
     * طباعة المشتريات المضافة للجلسة
     */
    public function sessionPurchases(Sation $session,)
    {

        // إضافة هذا السطر لتعريف المتغير

        $purchases = SessionPurchase::with('product')
            ->where('sation_id', $session->id)
            ->get();

        $total = $purchases->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('print.session-purchases', compact(
            'session',
            'purchases',
            'total'
        ));
    }

    /**
     * طباعة الفاتورة النهائية للجلسة
     */
    public function sessionInvoice(Sation $session)
    {
        $purchases = SessionPurchase::with('product')
            ->where('sation_id', $session->id)
            ->get();

        $totalPurchases = $purchases->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('print.session-invoice', compact(
            'session',
            'purchases',
            'totalPurchases'
        ));
    }
}
