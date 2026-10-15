<?php

namespace App\Http\Controllers;

use App\Models\UnitConversion;
use Illuminate\Http\Request;

class UnitConversionController extends Controller
{
    public function index()
    {
        $unitConversions = UnitConversion::with(['fromUnit', 'toUnit'])->get();
        return view("unit_conversions.index", compact('unitConversions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_unit_id' => 'required|exists:units,id',
            'to_unit_id'   => 'required|exists:units,id',
            'factor'       => 'required|numeric|min:0.0001',
        ]);

        return UnitConversion::create($data);
    }

    public function show(UnitConversion $unitConversion)
    {
        return $unitConversion->load(['fromUnit', 'toUnit']);
    }

    public function update(Request $request, UnitConversion $unitConversion)
    {
        $data = $request->validate([
            'from_unit_id' => 'required|exists:units,id',
            'to_unit_id'   => 'required|exists:units,id',
            'factor'       => 'required|numeric|min:0.0001',
        ]);

        $unitConversion->update($data);
        return $unitConversion;
    }

    public function destroy(UnitConversion $unitConversion)
    {
        $unitConversion->delete();
        return response()->noContent();
    }
}
