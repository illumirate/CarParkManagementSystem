<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\SlotMaintenance;
use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ParkingLevel;
use Database\Factories\Slot\SlotFactory;
use App\Jobs\SetSlotAvailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class SlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($zoneId, $floorId)
    {
        $zone = Zone::findOrFail($zoneId);
        $floor = ParkingLevel::findOrFail($floorId);

        $slots = ParkingSlot::where('zone_id', $zone->id)
            ->where('level_id', $floor->id)
            ->get();

        return view('parkingslots.index', compact('zone', 'floor', 'slots'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ParkingSlot $parkingSlot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $zone = Zone::find($id);
        $parkingLevels = ParkingLevel::where('zone_id', $zone->id)->get();
        return view('zones.edit', compact('zone', 'id', 'parkingLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParkingSlot $parkingSlot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (is_array($id)) {
            ParkingSlot::destroy($id);
        } else {
            ParkingSlot::findOrFail($id)->delete();
        }
    }

    public function generate($zoneId, $floorId, $slotType = 'Car')
    {
        $zone = Zone::findOrFail($zoneId);
        $floor = ParkingLevel::findOrFail($floorId);

        ParkingSlot::where('zone_id', $zone->id)->where('level_id', $floor->id)->delete();

        $totalSlots = $floor->total_slots;

        for ($i = 1; $i <= $totalSlots; $i++) {
            $slotId = $zone->zone_code . '-' . $floor->level_name . '-' . $i;

            SlotFactory::make($slotType)->create([
                'slot_id' => $slotId,
                'zone_id' => $zone->id,
                'level_id' => $floor->id,
            ]);
        }

        return redirect()->route('admin.zones.floors.slots.index', [$zone->id, $floor->id])
            ->with('success', 'Parking slots generated successfully.');
    }

    public function bulkMarkUnavailable(Request $request, $zoneId, $floorId)
    {
        $slotIds = $request->input('slot_ids', []);

        if (empty($slotIds)) {
            return redirect()->back()->with('error', 'No slots selected.');
        }

        ParkingSlot::whereIn('id', $slotIds)->update([
            'status' => 'unavailable'
        ]);

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', 'Selected slots marked as unavailable.');
    }

    public function bulkMarkAvailable(Request $request, $zoneId, $floorId)
    {
        $slotIds = $request->input('slot_ids', []);

        if (empty($slotIds)) {
            return redirect()->back()->with('error', 'No slots selected.');
        }

        ParkingSlot::whereIn('id', $slotIds)->update([
            'status' => 'available'
        ]);

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', 'Selected slots marked as available.');
    }

    public function updateType(Request $request, $zoneId, $floorId, $slotId)
    {
        $request->validate([
            'type' => 'required|in:Car,Motorcycle',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);

        $slot->type = $request->type;


        $slot->save();

        return response()->json([
            'success' => true,
            'slot_id' => $slot->id,
            'new_type' => $slot->type,
            'new_status' => $slot->status,
        ]);
    }
    public function scheduleMaintenance(Request $request, $zoneId, $floorId, $slotId)
    {
        $request->validate([
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);
        $requestId = Str::uuid();
        $timestamp = now()->format('Y-m-d H:i:s');

        $token = $request->bearerToken();
        $authResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/verify-token');
        $user = $authResponse->successful() ? $authResponse->json() : null;


        if (!$user || !in_array($user['role'], ['admin', 'maintenance'])) {
            return redirect()->back()->with('error', 'Unauthorized or forbidden.');
        }

        $bookingResponse = Http::get('http://booking-module/api/bookings', [
            'requestId' => $requestId,
            'timestamp' => $timestamp,
            'slot_id' => $slotId
        ]);

        $bookings = $bookingResponse->successful() ? collect($bookingResponse->json()['data']) : collect();
        $activeBookings = $bookings->filter(fn($b) => in_array($b['status'], ['confirmed', 'ongoing']));

        if ($activeBookings->isNotEmpty()) {
            foreach ($activeBookings as $booking) {
                Http::post('http://payment-module/api/payments/refund', [
                    'requestId' => $requestId,
                    'timestamp' => $timestamp,
                    'slot_id' => $slotId,
                    'booking_id' => $booking['booking_id'],
                    'amount' => $booking['total_fee']
                ]);
            }

            return redirect()->back()->with('error', 'Slot has active bookings. Maintenance cannot proceed.');
        }

        $slot->update(['status' => 'maintenance']);
        SlotMaintenance::create([
            'slot_id' => $slot->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $end = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->end_time, 'Asia/Kuala_Lumpur');
        $delay = max(0, now()->diffInSeconds($end));
        SetSlotAvailable::dispatch($slot->id)->delay($delay);

        return redirect()->back()->with('success', 'Slot scheduled for maintenance.');
    }

    public function updateMaintenance(Request $request, $zoneId, $floorId, $slotId)
    {
        $request->validate([
            'end_time' => 'required|date|after:start_time',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);
        $maintenance = $slot->maintenance;

        if (!$maintenance) {
            return redirect()->back()->with('error', 'No maintenance record found for this slot.');
        }

        $maintenance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        if (now()->between($request->start_time, $request->end_time)) {
            $slot->update(['status' => 'maintenance']);
        }

        return redirect()->back()->with('success', 'Maintenance updated successfully.');
    }

    public function completeMaintenance($zoneId, $floorId, $slotId)
    {
        $slot = ParkingSlot::findOrFail($slotId);
        $maintenance = $slot->maintenance;

        if ($maintenance) {
            $maintenance->end_time = now();
            $maintenance->save();
        }
        $slot->update(['status' => 'available']);

        return redirect()->back()->with('success', 'Maintenance marked as complete.');
    }

    public function showMaintenanceForm($zoneId, $floorId, $slotId)
    {
        $slot = ParkingSlot::with('maintenance')->findOrFail($slotId);
        $floor = $slot->parkingLevel;
        $zone = $slot->zone;

        return view('parkingslots.maintenance', compact('slot', 'floor', 'zone'));

    }

}
