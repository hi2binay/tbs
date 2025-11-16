<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $bookings = auth()->user()
            ->bookings()
            ->with(['reservation.event', 'payment', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.index', compact('bookings'));
    }
}
