<?php

namespace App\Http\Controllers;

use App\Models\CleaningCriteria;
use App\Models\CleaningReport;
use App\Models\CleaningReportCriteria;
use App\Models\Reservation;
use Illuminate\Http\Request;

class CleaningReportController extends Controller
{
    /**
     * Dashboard data for the authenticated user.
     * Returns host banner info and/or reviewer banner info.
     */
    public function dashboardData(Request $request)
    {
        $user = $request->user();
        $houseId = $user->house_id;
        $now = now();

        $result = [
            'host' => null,
            'reviewers' => [],
        ];

        if (!$houseId) {
            return response()->json($result);
        }

        // --- HOST BANNER ---
        // Find approved reservation for this house that is today or within the last 24 hours
        $hostReservation = Reservation::where('house_id', $houseId)
            ->where('is_approved', 1)
            ->whereBetween('reservation_date', [
                $now->copy()->subHours(24)->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->orderBy('reservation_date', 'desc')
            ->first();

        if ($hostReservation) {
            // Find the next approved reservation AFTER this one (from a different house)
            $nextReservation = Reservation::where('is_approved', 1)
                ->where('reservation_date', '>', $hostReservation->reservation_date)
                ->where('house_id', '!=', $houseId)
                ->orderBy('reservation_date', 'asc')
                ->with('house')
                ->first();

            if ($nextReservation && $nextReservation->house) {
                $nextUser = $nextReservation->house->primaryUser();
                $report = CleaningReport::where('reservation_id', $hostReservation->id)->first();

                $result['host'] = [
                    'reservation_id' => $hostReservation->id,
                    'next_user_name' => $nextUser ? $nextUser->name : 'Sin usuario asignado',
                    'next_user_phone' => $nextUser ? $nextUser->phone : null,
                    'report_completed' => $report && $report->completed_at !== null,
                ];
            }
        }

        // --- REVIEWERS LIST (ACCORDION) ---
        // All approved upcoming reservations of current user in current year
        $myReservations = Reservation::where('house_id', $houseId)
            ->where('is_approved', 1)
            ->whereYear('reservation_date', $now->year)
            ->where('reservation_date', '>=', $now->copy()->startOfDay())
            ->orderBy('reservation_date', 'asc')
            ->get();

        $activeCriteria = CleaningCriteria::where('is_active', true)->orderBy('order')->get();

        $reviewers = [];
        foreach ($myReservations as $myRes) {
            $prevRes = Reservation::where('is_approved', 1)
                ->where('reservation_date', '<', $myRes->reservation_date)
                ->where('house_id', '!=', $houseId)
                ->orderBy('reservation_date', 'desc')
                ->with('house')
                ->first();

            if ($prevRes && $prevRes->house) {
                $prevUser = $prevRes->house->primaryUser();
                $existingReport = CleaningReport::where('reservation_id', $prevRes->id)->first();

                $myResDay = \Carbon\Carbon::parse($myRes->reservation_date)->startOfDay();
                $prevResDate = \Carbon\Carbon::parse($prevRes->reservation_date);
                $today = $now->copy()->startOfDay();

                // Show host contact details only within 7 days of my event (or if event is today/past)
                $daysToMyEvent = $today->diffInDays($myResDay, false);
                $isNear = ($daysToMyEvent <= 7);

                // Event passed when current time is past the end of previous reservation day
                $eventPassed = $now->gt($prevResDate->copy()->endOfDay());

                $reviewers[] = [
                    'my_reservation_id' => $myRes->id,
                    'my_reservation_date' => $myRes->reservation_date,
                    'previous_reservation_id' => $prevRes->id,
                    'previous_reservation_date' => $prevRes->reservation_date,
                    'host_name' => $prevUser ? $prevUser->name : 'Sin usuario asignado',
                    'host_phone' => $prevUser ? $prevUser->phone : null,
                    'is_near' => $isNear,
                    'event_passed' => $eventPassed,
                    'already_reported' => $existingReport && $existingReport->completed_at !== null,
                    'criteria' => $activeCriteria,
                ];
            }
        }

        $result['reviewers'] = $reviewers;

        return response()->json($result);
    }

    /**
     * Store a cleaning report with criteria responses.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'responses' => 'required|array',
            'responses.*.cleaning_criteria_id' => 'required|exists:cleaning_criteria,id',
            'responses.*.response' => 'nullable|string',
        ]);

        $user = $request->user();

        // Prevent duplicate reports
        $existing = CleaningReport::where('reservation_id', $request->reservation_id)
            ->whereNotNull('completed_at')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Ya se ha reportado la limpieza para esta reservación'], 422);
        }

        $report = CleaningReport::create([
            'reservation_id' => $request->reservation_id,
            'reviewer_user_id' => $user->id,
            'completed_at' => now(),
        ]);

        foreach ($request->responses as $resp) {
            CleaningReportCriteria::create([
                'cleaning_report_id' => $report->id,
                'cleaning_criteria_id' => $resp['cleaning_criteria_id'],
                'response' => $resp['response'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Reporte de limpieza guardado exitosamente']);
    }
}
