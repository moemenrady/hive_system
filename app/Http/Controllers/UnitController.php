<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Pest\Support\View;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();

        return view("units.index", compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|unique:units,name',
            'symbol' => 'required|string',
        ]);


        Unit::create($data);
        return redirect()->back()->with('success', 'تم حفظ وحدة القياس الجديده');
    }

    public function show(Unit $unit)
    {
        return $unit;
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name'   => 'required|string|unique:units,name,' . $unit->id,
            'symbol' => 'required|string',
        ]);

        $unit->update($data);
        return $unit;
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return response()->noContent();
    }
}
