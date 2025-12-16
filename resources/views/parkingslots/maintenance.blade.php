@extends('layout')

@section('content')
<div class="container mt-4">
    <h2>Manage Parking Slots for Floor: {{ $floor->level_name }} (Zone: {{ $zone->zone_name }})</h2><br />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.zones.floors.index', $zone->id) }}" class="btn btn-secondary mb-3">
        Back to Floors
    </a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Slot ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Maintenance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($slots as $slot)
                <tr>
                    <td>{{ $slot->id }}</td>
                    <td>{{ $slot->slot_id }}</td>
                    <td>{{ $slot->type }}</td>
                    <td>{{ ucfirst($slot->status) }}</td>
                    <td>
                        @if($slot->status === 'maintenance')
                            Maintenance until {{ $slot->maintenance?->end_time ?? '-' }}
                        @else
                            <form method="POST" action="{{ route('admin.zones.floors.slots.scheduleMaintenance', [$zone->id, $floor->id, $slot->id]) }}">
                                @csrf
                                <input type="datetime-local" name="start_time" required>
                                <input type="datetime-local" name="end_time" required>
                                <button type="submit" class="btn btn-warning btn-sm">Set Maintenance</button>
                            </form>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.zones.floors.slots.edit', [$zone->id, $floor->id, $slot->id]) }}"
                           class="btn btn-primary btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No parking slots found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
