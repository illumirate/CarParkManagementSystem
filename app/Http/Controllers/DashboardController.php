<?php
//  Author: Leo Chia Chuen

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display user dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Get upcoming bookings
        $upcomingBookings = $user->bookings()
            ->with(['parkingSlot.zone', 'vehicle'])
            ->upcoming()
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Get active booking (if any)
        $activeBooking = $user->bookings()
            ->with(['parkingSlot.zone', 'vehicle'])
            ->where('status', 'active')
            ->first();

        // Get recent transactions
        $recentTransactions = $user->creditTransactions()
            ->latest()
            ->take(5)
            ->get();

        // Get vehicle count
        $vehicleCount = $user->vehicles()->active()->count();

        // Basic stats (credit balance and vehicles are local data)
        // Booking stats will be loaded via frontend AJAX to avoid blocking
        $stats = [
            'credit_balance' => $user->credit_balance,
            'vehicles' => $vehicleCount,
        ];

        return view('dashboard.index', [
            'user' => $user,
            'upcomingBookings' => $upcomingBookings,
            'activeBooking' => $activeBooking,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
        ]);
    }
}
