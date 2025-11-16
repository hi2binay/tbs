<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEvents = Event::count();
        $totalBookings = Booking::count();
        $totalRevenue = Booking::where('status', 'confirmed')->sum('total_amount_cents') / 100;
        $totalUsers = User::count();

        $recentBookings = Booking::with(['user', 'reservation.event'])
            ->latest()
            ->take(10)
            ->get();

        return view('backend.dashboard.index', compact(
            'totalEvents',
            'totalBookings',
            'totalRevenue',
            'totalUsers',
            'recentBookings'
        ));
    }
}
