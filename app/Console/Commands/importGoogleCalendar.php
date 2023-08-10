<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;

class importGoogleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-google-calendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Google Calendar from Google API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $archivo = fopen(public_path('calendar/calendar.ics'), 'r');

        if ($archivo) {
            while (($linea = fgets($archivo)) !== false) {
                if (strpos($linea, 'DTSTART') !== false) {
                     // date format from text: 20230401
                    $reservationDate = str_replace('DTSTART;VALUE=DATE:', '', $linea);
                    $reservationDate = substr($reservationDate, 0, 4) . '-' . substr($reservationDate, 4, 2) . '-' . substr($reservationDate, 6, 2);
                    $reservationDate = date('Y-m-d H:i:s', strtotime($reservationDate . ' +12 hours'));

                    $this->info($reservationDate);
                    $description = '';
                    while (($linea = fgets($archivo)) !== false) {
                        if (strpos($linea, 'DESCRIPTION') !== false) {
                            $description .= $linea;
                            $linea = fgets($archivo);
                            if (str_contains($linea, 'LAST-MODIFIED') !== true) {
                                $description .= $linea;
                            }
                            break;
                        }
                    }
                    while (($linea = fgets($archivo)) !== false) {
                        if (strpos($linea, 'SUMMARY') !== false) {
                            $description .= $linea;
                            break;
                        }
                    }
                    $notes = str_replace('\n', ' / ', str_replace('SUMMARY:', '', str_replace('DESCRIPTION:', '', $description)));
                    $this->info($notes);

                    $reservation = Reservation::where('reservation_date', $reservationDate)->first();

                    if (!$reservation) {
                        $reservation = new Reservation();
                        $reservation->reservation_date = $reservationDate;
                        $reservation->notes = $notes;
                        $reservation->is_approved = 1;
                        $reservation->save();
                        $this->info('Reservation created.');
                    } else {
                        $this->error('Reservation already exists.');
                    }
                }
            }

            fclose($archivo);
        } else {
            echo 'No se pudo abrir el archivo.';
        }
    }
}
