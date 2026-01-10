<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ReservationController;

class CancelReservationsExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-reservations-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel reservations that have expired payment time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new ReservationController();

        $response =$controller->cancelExpiredReservations();

        $this->info($response->getContent());
    }
}
