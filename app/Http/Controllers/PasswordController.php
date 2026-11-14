<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Password;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $passwords = Password::all();

        return response()->json($passwords);
    }

    /**
     * Display passwords for public access based on reservation date
     */
    public function indexPublic(Request $request, string $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $user = $request->user();

        if (
            $user->role_id !== 1
            && (int) $reservation->house_id !== (int) $user->house_id
            && (int) $reservation->user_id !== (int) $user->id
        ) {
            abort(403);
        }

        $reservationDate = Carbon::parse($reservation->reservation_date)->startOfDay();
        $today = Carbon::today();
        
        $passwords = Password::where('is_active', 1)->get();
        
        // Only reveal passwords for approved reservations on the day of the event
        if ((int) $reservation->is_approved !== 1 || $reservationDate->notEqualTo($today)) {
            $passwords->transform(function ($password) {
                $password->password = '********';
                return $password;
            });
        }

        return response()->json($passwords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePasswordRequest $request)
    {
        Password::create($request->all());

        return response()->json(['message' => 'Password created successfully']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePasswordRequest $request, string $id)
    {
        $password = Password::find($id);
        $password->update($request->except('id'));

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Password::destroy($request->id);

        return response()->json(['message' => 'Password deleted successfully']);
    }
}
