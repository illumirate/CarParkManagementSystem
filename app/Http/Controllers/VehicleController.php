<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Display list of user's vehicles.
     */
    public function index(): View
    {
        $vehicles = Auth::user()->vehicles()->latest()->get();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Display add vehicle form.
     */
    public function create(): View
    {
        return view('vehicles.create');
    }

    /**
     * Store new vehicle.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:15',
                'unique:vehicles,plate_number',
                'regex:/^[A-Z]{1,3}\s?[0-9]{1,4}\s?[A-Z]{0,3}$/i',
            ],
            'vehicle_type' => ['required', 'in:car,motorcycle'],
            'brand' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
        ], [
            'plate_number.regex' => 'Please enter a valid Malaysian plate number (e.g., ABC 1234, W 1234 A).',
            'plate_number.unique' => 'This plate number is already registered.',
        ]);

        $user = Auth::user();

        // Check if this is the first vehicle (make it primary)
        $isPrimary = $user->vehicles()->count() === 0;

        $user->vehicles()->create([
            'plate_number' => strtoupper(str_replace(' ', '', $request->plate_number)),
            'vehicle_type' => $request->vehicle_type,
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'is_primary' => $isPrimary,
            'status' => 'active',
        ]);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle added successfully!');
    }

    /**
     * Display edit vehicle form.
     */
    public function edit(Vehicle $vehicle): View|RedirectResponse
    {
        // Ensure user owns this vehicle
        if ($vehicle->user_id !== Auth::id()) {
            return redirect()->route('vehicles.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        return view('vehicles.edit', [
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Update vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        // Ensure user owns this vehicle
        if ($vehicle->user_id !== Auth::id()) {
            return redirect()->route('vehicles.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:15',
                'unique:vehicles,plate_number,' . $vehicle->id,
                'regex:/^[A-Z]{1,3}\s?[0-9]{1,4}\s?[A-Z]{0,3}$/i',
            ],
            'vehicle_type' => ['required', 'in:car,motorcycle'],
            'brand' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'plate_number.regex' => 'Please enter a valid Malaysian plate number.',
        ]);

        $vehicle->update([
            'plate_number' => strtoupper(str_replace(' ', '', $request->plate_number)),
            'vehicle_type' => $request->vehicle_type,
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'status' => $request->status,
        ]);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Delete vehicle.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        // Ensure user owns this vehicle
        if ($vehicle->user_id !== Auth::id()) {
            return redirect()->route('vehicles.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        // Check if vehicle has active bookings
        $hasActiveBookings = $vehicle->user->bookings()
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->exists();

        if ($hasActiveBookings) {
            return redirect()->route('vehicles.index')
                ->withErrors(['error' => 'Cannot delete vehicle with active bookings.']);
        }

        $wasPrimary = $vehicle->is_primary;
        $vehicle->delete();

        // If deleted vehicle was primary, set another as primary
        if ($wasPrimary) {
            $newPrimary = Auth::user()->vehicles()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle removed successfully!');
    }

    /**
     * Set vehicle as primary.
     */
    public function setPrimary(Vehicle $vehicle): RedirectResponse
    {
        // Ensure user owns this vehicle
        if ($vehicle->user_id !== Auth::id()) {
            return redirect()->route('vehicles.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        // Remove primary from all user's vehicles
        Auth::user()->vehicles()->update(['is_primary' => false]);

        // Set this vehicle as primary
        $vehicle->update(['is_primary' => true]);

        return redirect()->route('vehicles.index')
            ->with('success', 'Primary vehicle updated!');
    }
}
