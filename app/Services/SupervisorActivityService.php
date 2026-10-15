<?php

namespace App\Services;

use App\Models\SupervisorActivity;

class SupervisorActivityService
{
    public static function log($action, $description = null, $model = null, $old = null, $new = null)
    {
        if (!auth()->check() || auth()->user()->role !== 'supervisor') {
            return; // يمنع تسجيل أي حد غير السوبر فايزر
        }

        SupervisorActivity::create([
            'supervisor_id' => auth()->id(),
            'action'        => $action,
            'description'   => $description,
        ]);
    }
}
