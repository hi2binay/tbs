<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        DB::table('payments')->truncate();
        DB::table('booking_items')->truncate();
        DB::table('bookings')->truncate();
        DB::table('reservations')->truncate();
        DB::table('ticket_types')->truncate();
        DB::table('events')->truncate();
        DB::table('users')->truncate();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }

        $this->call([
            UserSeeder::class,
            EventSeeder::class,
            TicketTypeSeeder::class,
            BookingSeeder::class,
        ]);

        $this->command->info('Database seeded successfully with demo data!');
        $this->command->info('Admin credentials: admin@example.com / password');
    }
}
