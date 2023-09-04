<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\House;
use App\Validations\ReservationsValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //reservations of actual year, approved or not approved but visible
        $reservations = Reservation::whereYear('reservation_date', date('Y'))->where(function (Builder $query) {
            $query->where('is_approved', 1)->orWhere(function (Builder $query2) {
                $query2->where('is_approved', 0)->where('is_visible', 1);
            });
        })->get();

        return response()->json($reservations);
    }

    /**
     * list of reservations to approve
     */
    public function approvalRequestsList()
    {
        $reservations = Reservation::where('is_approved', 0)->where('is_visible', 1)->with(['house.street', 'user'])->get();

        return response()->json($reservations);
    }

    /**
     * number of reservations to approve
     */
    public function approvalRequestsCount()
    {
        $reservations = Reservation::where('is_approved', 0)->where('is_visible', 1)->count();

        return response()->json($reservations);
    }

    /**
     * Update reservation status
     */
    public function approvalRequests(Request $request)
    {
        $reservation = Reservation::find($request->id);

        $reservation->is_approved = $request->is_approved;

        $reservation->approved_by = $request->user()->id;

        $reservation->save();

        if ($reservation->is_approved == 1){
            $this->rejectReservations($reservation->id, $reservation->reservation_date);

            return response()->json([
                'message' => 'Reservación actualizada exitosamente, las demás reservaciones para esta fecha han sido rechazadas!',
            ]);
        }

        return response()->json([
            'message' => 'Reservación actualizada exitosamente',
        ]);
    }

    /**
     * Reject reservations with same date except the one that is being updated
     */
    public function rejectReservations($approved_id, $reservation_date)
    {
        $reservations = Reservation::where('reservation_date', $reservation_date)->where('is_approved', 0)->where('id', '!=', $approved_id)->get();

        foreach ($reservations as $reservation) {
            $reservation->is_approved = 2;
            $reservation->save();
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = new ReservationsValidator($user->house_id, $request->reservation_date);

        $errors = $validator->validate();

        if ($errors) {
            return response()->json($errors, 422);
        }

        Reservation::create([
            'house_id' => $user->house_id,
            'user_id' => $user->id,
            'reservation_date' => date('Y-m-d 12:00:00', strtotime($request->reservation_date)),
        ]);

        return response()->json([
            'message' => 'Reservación creada exitosamente',
            'reservation' => $request->reservation_date,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAdmin(Request $request)
    {

        $newHouse = House::updateOrCreate([
            'street_id' => $request->street_id,
            'house_number' => $request->house_number,
        ]);

        $validator = new ReservationsValidator($newHouse->id, $request->reservation_date);

        $errors = $validator->validateAdmin();

        if ($errors) {
            return response()->json($errors, 422);
        }

        Reservation::create([
            'house_id' => $newHouse->id,
            'user_id' => null,
            'reservation_date' => date('Y-m-d 12:00:00', strtotime($request->reservation_date)),
            'is_approved' => 1,
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Reservación creada exitosamente',
            'reservation' => $request->reservation_date,
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
     * Return the list of reservations approved
     */
    public function events()
    {
        $reservations = Reservation::where('is_approved', 1)->with(['house.street', 'user', 'approvedBy'])->get();

        return response()->json($reservations);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::find($id);

        $reservation->is_signed = $request->is_signed ? 1 : 0;

        $reservation->is_paid = $request->is_paid ? 1 : 0;

        $reservation->notes = $request->notes;

        $reservation->save();

        return response()->json([
            'message' => 'Reservación actualizada exitosamente',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reservation = Reservation::find($id);

        if ($reservation->is_approved == 0) {
            $reservation->delete();

            return response()->json([
                'message' => 'Reservación eliminada exitosamente',
            ]);
        }
        
        $reservation->is_visible = 0;

        $reservation->save();

        return response()->json([
            'message' => 'Reservación archivada exitosamente',
        ]);
    }

    /**
     * Admin remove reservation
     */

    public function remove(string $id)
    {
        if($reservation = Reservation::find($id)) {
            $reservation->delete();

            return response()->json([
                'message' => 'Reservación eliminada exitosamente',
            ]);
        }

        return response()->json([
            'message' => 'Reservación no encontrada',
        ], 404);
    }
}
