<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //reservations of actual year
        $reservations = Reservation::whereYear('reservation_date', date('Y'))->where('is_visible', 1)->where(function(Builder $query){
            $query->where('is_approved', 1)->orWhere('is_approved', 0);
        })->get();

        return response()->json($reservations);
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
        $user = $request->user();

        // $date_time_javascript_to_php = strtotime($request->reservation_date);

       $reservation =  Reservation::create([
            'house_id' => $user->house_id,
            'user_id' => $user->id,
            'reservation_date' => date('Y-m-d 12:00:00', strtotime($request->reservation_date)),
        ]);

        return response()->json([
            'message' => 'Reservación creada exitosamente',
            'reservation' => $request->reservation_date
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Reservation::where('house_id', $id)->where('is_visible', 1)->with(['house.street', 'user'])->orderBy('is_approved', 'asc')->get();

        return response()->json($reservation);
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
    public function destroy(string $id)
    {
        $reservation = Reservation::find($id);

        $reservation->is_visible = 0;

        $reservation->save();

        return response()->json([
            'message' => 'Reservación eliminada exitosamente'
        ]);
    }
}
