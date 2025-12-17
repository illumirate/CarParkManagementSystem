@extends('layout')

@section('content')
<div class="container mt-4">
    <h2>Manage Maintenance for Slot: {{ $slot->slot_id }} (Floor: {{ $floor->level_name }}, Zone: {{ $zone->zone_name }})</h2><br />

    <a href="{{ route('admin.zones.floors.slots.index', [$zone->id, $floor->id]) }}" class="btn btn-secondary mb-3">
        Back to Slots
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Slot ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Maintenance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $slot->slot_id }}</td>
                <td>{{ $slot->type }}</td>
                <td>{{ ucfirst($slot->status) }}</td>
                <td>
                    @if($slot->maintenance)
                        <form method="POST" action="{{ route('admin.zones.floors.slots.updateMaintenance', [$zone->id, $floor->id, $slot->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-2">
                                <label>Start Time:</label>
                                <input type="datetime-local" name="start_time" class="form-control"
                                       value="{{ $slot->maintenance->start_time->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <label>End Time:</label>
                                <input type="datetime-local" name="end_time" class="form-control"
                                       value="{{ $slot->maintenance->end_time->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm">Update Maintenance</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.zones.floors.slots.scheduleMaintenance', [$zone->id, $floor->id, $slot->id]) }}">
                            @csrf
                            <div class="form-group mb-2">
                                <label>Start Time:</label>
                                <input type="datetime-local" name="start_time" class="form-control" required>
                            </div>
                            <div class="form-group mb-2">
                                <label>End Time:</label>
                                <input type="datetime-local" name="end_time" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm">Set Maintenance</button>
                        </form>
                    @endif
                </td>
                <td>
                    @if($slot->maintenance)
                        <form method="POST" action="{{ route('admin.zones.floors.slots.completeMaintenance', [$zone->id, $floor->id, $slot->id]) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success btn-sm">Mark Complete</button>
                        </form>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
