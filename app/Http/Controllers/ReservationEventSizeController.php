<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservationEventSize;

class ReservationEventSizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservationEventSizes = ReservationEventSize::all();

        return response()->json($reservationEventSizes);
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
        $is_valid = $request->validate([
            'size' => 'required|string|max:255',
            'price' => 'required|integer',
            'capacity' => 'required|integer',
        ]);

        if (!$is_valid) {
            return response()->json(['message' => 'Datos inválidos'], 422);
        }
        
        ReservationEventSize::create([
            'size' => $request->size,
            'price' => $request->price,
            'capacity' => $request->capacity,
        ]);

        return response()->json([
            'message' => 'Tamaño de aforo creado exitosamente',
        ]);
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
        $reservationEventSize = ReservationEventSize::find($id);

        $reservationEventSize->size = $request->size;
        $reservationEventSize->price = $request->price;
        $reservationEventSize->capacity = $request->capacity;

        $reservationEventSize->save();

        return response()->json([
            'message' => 'Tamaño de aforo actualizado exitosamente',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reservationEventSize = ReservationEventSize::find($id);

        $reservationEventSize->delete();

        return response()->json([
            'message' => 'Tamaño de aforo eliminado exitosamente',
        ]);
    }
}
