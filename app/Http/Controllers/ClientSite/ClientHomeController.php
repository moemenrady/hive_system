<?php

namespace App\Http\Controllers\ClientSite;

use App\Http\Controllers\Controller;
use App\Models\Offer;

class ClientHomeController extends Controller
{
    public function index()
    {
      $collections = [];
        $offers = Offer::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();

        return view('client_site.home.index', compact('offers','collections'));
    }
    public function search()
    {
    }
    public function profile(){}
}
