<?php

namespace App\Validations;

use App\Models\Config;
use App\Models\Reservation;

class ReservationsValidator
{
    private $firstDate;

    private $secondDate;

    private $terraceTimesPerSeason;

    private $seasonDuration;

    private $IsRegisterOpen;

    private $houseId;

    private $dateToReserve;

    private $errors = [];

    public function __construct($houseId, $dateToReserve)
    {
        $this->houseId = $houseId;

        $this->dateToReserve = $dateToReserve;

        $config = Config::all();

        $this->terraceTimesPerSeason = $config->filter(function ($value) {
            return $value->slug == 'ttps';
        })->first()->setting;

        $this->seasonDuration = $config->filter(function ($value) {
            return $value->slug == 'sd';
        })->first()->setting;

        $this->IsRegisterOpen = $config->filter(function ($value) {
            return $value->slug == 'iro';
        })->first()->setting;
    }

    public function validate()
    {
        if (!$this->houseId) {
            $this->errors[] = 'No se te ha asignado un domicilio';
            return $this->errors;
        }

        [$firstMonthOfSeason, $lastMonthOfSeason] = $this->monthsRange();

        $this->firstDate = date('Y') . '-' . $firstMonthOfSeason . '-01';

        $this->secondDate = date('Y') . '-' . $lastMonthOfSeason . '-31';

        $activeReservations = $this->getApprovedReservationsByMonth();

        if ($activeReservations->count() >= $this->terraceTimesPerSeason) {
            $this->errors[] = 'No se puede reservar más veces la terraza en esta temporada (' . $this->firstDate . ' - ' . $this->secondDate . ')';
        }

        $pendingReservations = $this->getPendingsReservationsByMonth();

        if ($pendingReservations->count() > 0) {
            $this->errors[] = 'Ya tienes una reserva pendiente de aprobación';
        }

        if (!$this->IsRegisterOpen) {
            $this->errors[] = 'El registro de la terraza está cerrado';
        }

        if (date('Y', strtotime($this->dateToReserve)) != date('Y')) {
            $this->errors[] = 'Solo se puede reservar para el año actual';
        }
        
        if (date('Y-m-d', strtotime($this->dateToReserve)) < date('Y-m-d')) {
            $this->errors[] = 'No se puede reservar para fechas anteriores a hoy';
        }

        if (!$this->isInRange()) {
            $months = $this->seasonDuration == 1 ? $this->getMonthName($firstMonthOfSeason) : $this->getMonthName($firstMonthOfSeason) . ' - ' . $this->getMonthName($lastMonthOfSeason);
            $this->errors[] = 'Solo se puede reservar en el rango de fechas (' . $months . ')';
        }

        return $this->errors;
    }

    private function monthsRange()
    {
        $actualMonth = date('m');

        switch ($this->seasonDuration) {
            case 12:
                return [1, 12];

            case 6:
                return $actualMonth <= 6 ? [1, 6] : [7, 12];

            case 4:
                if ($actualMonth <= 4) {
                    return [1, 4];
                }
                if ($actualMonth <= 8) {
                    return [5, 8];
                }

                return [9, 12];

            case 3:
                if ($actualMonth <= 3) {
                    return [1, 3];
                }
                if ($actualMonth <= 6) {
                    return [4, 6];
                }
                if ($actualMonth <= 9) {
                    return [7, 9];
                }

                return [10, 12];

            case 2:
                if ($actualMonth <= 2) {
                    return [1, 2];
                }
                if ($actualMonth <= 4) {
                    return [3, 4];
                }
                if ($actualMonth <= 6) {
                    return [5, 6];
                }
                if ($actualMonth <= 8) {
                    return [7, 8];
                }
                if ($actualMonth <= 10) {
                    return [9, 10];
                }

                return [11, 12];

            default:
                return [intval($actualMonth), intval($actualMonth)];
        }
    }

    private function getApprovedReservationsByMonth()
    {
        return Reservation::where('house_id', $this->houseId)
            ->whereBetween('reservation_date', [$this->firstDate, $this->secondDate])
            ->where('is_approved', 1)
            ->get();
    }

    private function getPendingsReservationsByMonth()
    {
        return Reservation::where('house_id', $this->houseId)
            ->whereBetween('reservation_date', [$this->firstDate, $this->secondDate])
            ->where('is_approved', 0)
            ->where('is_visible', 1)
            ->get();
    }

    private function isInRange()
    {
        return date('Y-m-d', strtotime($this->dateToReserve)) >= date('Y-m-d', strtotime($this->firstDate)) && date('Y-m-d', strtotime($this->dateToReserve)) <= date('Y-m-d', strtotime($this->secondDate));
    }

    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $months[$month];
    }
}
