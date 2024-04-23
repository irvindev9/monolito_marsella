<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restriction;
use App\Models\House;

class RestrictionController extends Controller
{
    // store restriction in database
    public function store(Request $request)
    {
        $request->validate([
            'street_id' => 'required|integer',
            'house_number' => 'required|integer',
            'block_date_end' => 'required|date',
            'reason' => 'required|string',
        ]);

        $house_id = House::where([
            'street_id' => $request->street_id,
            'house_number' => $request->house_number,
        ])->first();

        if (!$house_id) {
            return response()->json(['message' => 'Domicilio no encontrado'], 404);
        }

        $restriction = new Restriction();
        $restriction->house_id = $house_id->id;
        $restriction->block_date_end = date('Y-m-d 12:00:00', strtotime($request->block_date_end));
        $restriction->block_by = $request->user()->id;
        $restriction->reason = $request->reason;
        $restriction->save();

        return response()->json(['message' => 'Restricción creada con éxito'], 201);
    }

    // get all restrictions
    public function index()
    {
        $restrictions = Restriction::with(['house.street', 'blockBy'])
            // ->where('is_active', 1)
            ->where('block_date_end', '>=', date('Y-m-d 12:00:00'))
            ->get();

        return response()->json($restrictions, 200);
    }

    // update restriction
    public function update(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|integer',
        ]);

        $restriction = Restriction::find($id);

        if (!$restriction) {
            return response()->json(['message' => 'Restricción no encontrada'], 404);
        }

        $restriction->is_active = $request->is_active;
        $restriction->save();

        return response()->json(['message' => 'Restricción actualizada con éxito'], 200);
    }
}
