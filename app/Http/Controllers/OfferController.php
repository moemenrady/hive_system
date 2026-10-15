<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index()
{
    $offers = Offer::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->get();
    return view('client_site.home', compact('offers')); // بفرض أن السيكشن جزء من الهوم
}
}
