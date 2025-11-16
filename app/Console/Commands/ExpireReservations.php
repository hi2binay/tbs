<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire stale reservations and free up reserved inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(\App\Services\Reservation\ReservationService::class);
        $count = $service->expireStaleReservations();

        $this->info("Expired {$count} stale reservations");
    }
}
