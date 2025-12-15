@extends('layout')

@section('content')
    <div class="container mt-4">
        <h2>Manage Parking Slots for Floor: {{ $floor->level_name }} (Zone: {{ $zone->zone_name }})</h2><br />

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <form method="POST" action="{{ route('admin.zones.floors.slots.generate', [$zone->id, $floor->id]) }}">
                @csrf
                <div class="form-inline">
                    <label for="slot_count">Number of slots to generate:</label>
                    <input type="number" name="slot_count" min="1" max="{{ $floor->total_slots }}"
                        value="{{ $floor->total_slots }}" class="form-control mx-2">
                    <button type="submit" class="btn btn-success">Generate Slots</button>
                </div>
            </form>
        </div>

        <a href="{{ route('admin.zones.floors.index', $zone->id) }}" class="btn btn-secondary mb-3">
            Back to Floors
        </a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slot ID</th>
                    <th>Status</th>
                    <th>Level</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($slots as $slot)
                    <tr>
                        <td>{{ $slot->id }}</td>
                        <td>{{ $slot->slot_id }}</td>
                        <td>{{ ucfirst($slot->status) }}</td>
                        <td>{{ $floor->level_name }}</td>
                        <td>
                            <a href="{{ route('admin.zones.floors.slots.edit', [$zone->id, $floor->id, $slot->id]) }}"
                                class="btn btn-warning">Edit</a>
                        </td>
                        <td>
                            <form action="{{ route('admin.zones.floors.slots.destroy', [$zone->id, $floor->id, $slot->id]) }}"
                                method="post">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
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
