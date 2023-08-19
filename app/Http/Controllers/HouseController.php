<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Reservation;
use App\Models\User;

class HouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $houses = House::with(['street', 'users'])->get();

        return response()->json($houses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        House::updateOrCreate([
            'street_id' => $request->street_id,
            'house_number' => $request->house_number,
        ]);

        return response()->json(['message' => 'House created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = User::where('house_id', $request->id)->first();

        if ($user) {
            return response()->json(['message' => 'No se puede eliminar por que pertenece a un usuario'], 422);
        }

        House::destroy($request->id);
        Reservation::where('house_id', $request->id)->delete();

        return response()->json(['message' => 'Dirección eliminada, junto con sus reservaciones']);
    }
}
