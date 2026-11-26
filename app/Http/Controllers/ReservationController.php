<?php

namespace App\Http\Controllers;

use App\Mail\ReservationNotification;
use App\Models\Reservation;
use App\Models\ReservationWaitlist;
use App\Models\House;
use App\Models\User;
use App\Validations\ReservationsValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        //reservations of actual year, approved or not approved but visible
        $reservations = Reservation::whereYear('reservation_date', date('Y'))->where(function (Builder $query) {
            $query->where('is_approved', 1)->orWhere(function (Builder $query2) {
                $query2->where('is_approved', 0)->where('is_visible', 1);
            });
        })->withExists([
            'waitlists as in_waitlist' => fn ($query) => $query->where('user_id', $userId),
        ])->get();

        return response()->json($reservations);
    }

    /**
     * Join waitlist for an approved reservation
     */
    public function joinWaitlist(Request $request)
    {
        $user = $request->user();
        $reservation = Reservation::where('id', $request->reservation_id)
            ->where('is_approved', 1)
            ->firstOrFail();

        if ((int) $reservation->house_id === (int) $user->house_id) {
            return response()->json(['Esta reserva ya pertenece a tu domicilio'], 422);
        }

        if (ReservationWaitlist::where('reservation_id', $reservation->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['Ya estás registrado en la lista de espera para esta fecha'], 422);
        }

        $validator = new ReservationsValidator($user->house_id, $reservation->reservation_date);
        $errors = $validator->validate(true);

        if ($errors) {
            return response()->json($errors, 422);
        }

        ReservationWaitlist::create([
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Te has anotado en la lista de espera exitosamente',
        ]);
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

        $reservation->rejection_reason = $request->is_approved == 2
            ? ($request->filled('rejection_reason') ? trim($request->rejection_reason) : null)
            : null;

        $reservation->save();

        if ($reservation->is_approved == 1) {
            $this->notify([$reservation->user?->email], $reservation, 'approved');
            $this->rejectReservations($reservation->id, $reservation->reservation_date);

            return response()->json([
                'message' => 'Reservación actualizada exitosamente, las demás reservaciones para esta fecha han sido rechazadas!',
            ]);
        }

        if ($reservation->is_approved == 2) {
            $this->notify([$reservation->user?->email], $reservation, 'rejected');
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
        $reservations = Reservation::where('reservation_date', $reservation_date)
            ->where('is_approved', 0)
            ->where('id', '!=', $approved_id)
            ->with('user')
            ->get();

        foreach ($reservations as $reservation) {
            $reservation->is_approved = 2;
            $reservation->rejection_reason = 'Otra solicitud para esta misma fecha fue aprobada';
            $reservation->save();
            $this->notify([$reservation->user?->email], $reservation, 'rejected');
        }
    }

    private function notify(array $emails, ?Reservation $reservation, string $type, ?int $seasonRequestCount = null): void
    {
        $recipients = array_values(array_filter(array_unique($emails)));
        if (empty($recipients)) {
            return;
        }

        if (config('mail.default') === 'resend' && empty(config('services.resend.key')) && empty(config('resend.api_key'))) {
            Log::warning('No se encontró la API key de Resend (RESEND_KEY); no se envió el correo.', [
                'reservation_id' => $reservation?->id,
                'type' => $type,
            ]);
            return;
        }

        try {
            Mail::to($recipients)->send(new ReservationNotification($reservation, $type, $seasonRequestCount));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de reservación: ' . $e->getMessage(), [
                'reservation_id' => $reservation?->id,
                'type' => $type,
            ]);
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

        $reservation = Reservation::create([
            'house_id' => $user->house_id,
            'user_id' => $user->id,
            'is_signed' => $request->acceptTerms,
            'reservation_date' => date('Y-m-d 12:00:00', strtotime($request->reservation_date)),
        ]);

        $adminEmails = User::where('role_id', 1)->whereNotNull('email')->pluck('email')->all();
        $this->notify($adminEmails, $reservation, 'created', $validator->currentSeasonRequestCount());

        return response()->json([
            'message' => 'Fecha solicitada, en espera de confirmación',
            'reservation_date' => date('Y-m-d', strtotime($request->reservation_date)),
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
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1 && (int) $id !== (int) $user->house_id) {
            abort(403);
        }

        $reservations = Reservation::where('house_id', $id)
            ->where('is_visible', 1)
            ->with(['house.street', 'user', 'cleaningReport'])
            ->orderBy('is_approved', 'asc')
            ->get();

        foreach ($reservations as $reservation) {
            $prevRes = Reservation::where('is_approved', 1)
                ->where('reservation_date', '<', $reservation->reservation_date)
                ->where('house_id', '!=', $reservation->house_id)
                ->orderBy('reservation_date', 'desc')
                ->first();

            $prevReported = false;
            if (!$prevRes) {
                $prevReported = true;
            } else {
                $prevReported = \App\Models\CleaningReport::where('reservation_id', $prevRes->id)
                    ->whereNotNull('completed_at')
                    ->exists();
            }

            $ownReported = $reservation->cleaningReport && $reservation->cleaningReport->completed_at !== null;

            $reservation->cleaning_supervised = $prevReported || $ownReported;
        }

        return response()->json($reservations);
    }

    /**
     * Return the list of reservations approved
     */
    public function events(Request $request)
    {
        $reservations = Reservation::where('is_approved', 1);

        if (($request->showAll == false || $request->showAll == 'false' || !isset($request->showAll)) 
            && !$request->filled('year') && !$request->filled('month')) {
            $reservations = $reservations->where('reservation_date', '>=', date('Y-m-d 12:00:00'));
        }

        if ($request->filled('year')) {
            $reservations = $reservations->whereYear('reservation_date', $request->year);
        }

        if ($request->filled('month')) {
            $reservations = $reservations->whereMonth('reservation_date', $request->month);
        }

        $reservations = $reservations->with([
            'house.street',
            'user',
            'approvedBy',
            'cleaningReport.reviewer',
            'cleaningReport.criteria.criteria'
        ])->orderBy('reservation_date', 'asc')->get();

        $minDate = Reservation::where('is_approved', 1)->min('reservation_date');
        $minYear = $minDate ? (int)date('Y', strtotime($minDate)) : (int)date('Y');

        return response()->json([
            'events' => $reservations,
            'minYear' => $minYear,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::find($id);

        $reservation->is_signed = $request->is_signed ? 1 : 0;

        $reservation->notes = $request->notes;

        $reservation->save();

        return response()->json([
            'message' => 'Reservación actualizada exitosamente',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = $request->user();

        if (
            $user->role_id !== 1
            && (int) $reservation->house_id !== (int) $user->house_id
            && (int) $reservation->user_id !== (int) $user->id
        ) {
            abort(403);
        }

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
        if ($reservation = Reservation::with('waitlists.user')->find($id)) {
            $waitlistEmails = $reservation->waitlists
                ->map(fn ($w) => $w->user?->email)
                ->filter()
                ->all();

            $this->notify($waitlistEmails, $reservation, 'waitlist_released');
            $reservation->delete();

            return response()->json([
                'message' => 'Reservación eliminada exitosamente',
            ]);
        }

        return response()->json([
            'message' => 'Reservación no encontrada',
        ], 404);
    }

    /**
     * Cancel reservations expired payment time
     */

    public function cancelExpiredReservations()
    {
        $config = Config::where('slug', 'mdtpr')->first();
        try {
            $daysToPay = $config ? (int)$config->setting : 7;
            $validatedDaysToPay = $daysToPay > 0 ? $daysToPay : 7;
        } catch (\Exception $e) {
            $validatedDaysToPay = 7;
        }

        $now = now();

        $expiredReservations = Reservation::where('is_approved', 0)
            ->where('created_at', '<=', $now->copy()->subDays($validatedDaysToPay))
            ->with('user')
            ->get();

        foreach ($expiredReservations as $reservation) {
            $reservation->is_approved = 2;
            $reservation->rejection_reason = 'El plazo para realizar el pago se alcanzó';
            $reservation->save();
            $this->notify([$reservation->user?->email], $reservation, 'rejected');
        }

        $pendingReservations = Reservation::where('is_approved', 0)
            ->with('user')
            ->get();

        $reminderEmails = [];
        foreach ($pendingReservations as $reservation) {
            if (!$reservation->created_at || !$reservation->user?->email) {
                continue;
            }
            $deadline = $reservation->created_at->copy()->addDays($validatedDaysToPay);
            $secondsRemaining = $deadline->getTimestamp() - $now->getTimestamp();

            // 1-hour rounded window around 48h (171000s..174600s) and 24h (84600s..88200s)
            $in48hWindow = $secondsRemaining > (48 * 3600 - 1800) && $secondsRemaining <= (48 * 3600 + 1800);
            $in24hWindow = $secondsRemaining > (24 * 3600 - 1800) && $secondsRemaining <= (24 * 3600 + 1800);

            if ($in48hWindow || $in24hWindow) {
                $reminderEmails[] = $reservation->user->email;
            }
        }

        if (!empty($reminderEmails)) {
            $this->notify($reminderEmails, null, 'payment_reminder');
        }

        return response()->json([
            'message' => 'Expired reservations cancelled successfully',
        ]);
    }
}
