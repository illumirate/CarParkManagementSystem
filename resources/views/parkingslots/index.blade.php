@extends('layout')

@section('content')
    <div class="container mt-4">
        <h2>Manage Parking Slots for Floor: {{ $floor->level_name }} (Zone: {{ $zone->zone_name }})</h2><br />
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

        <form method="POST" action="{{ route('admin.zones.floors.slots.bulkMarkUnavailable', [$zone->id, $floor->id]) }}">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirmBulkDelete(this.form)">
                Mark Unavailable
            </button>

            <button type="submit"
                formaction="{{ route('admin.zones.floors.slots.bulkMarkAvailable', [$zone->id, $floor->id]) }}"
                class="btn btn-success" onclick="return confirmBulkDelete(this.form)">
                Mark Available
            </button>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Slot ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        <tr>
                            <td>
                                <input type="checkbox" name="slot_ids[]" value="{{ $slot->id }}">
                            </td>
                            <td>{{ $slot->id }}</td>
                            <td>{{ $slot->slot_id }}</td>
                            <td>
                                <select
                                    onchange="updateSlotType({{ $slot->id }}, {{ $zone->id }}, {{ $floor->id }}, this.value)">
                                    <option value="Car" {{ $slot->type == 'Car' ? 'selected' : '' }}>Car
                                    </option>
                                    <option value="Motorcycle" {{ $slot->type == 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                </select>

                            </td>
                            <td>{{ ucfirst($slot->status) }}</td>
                            <td>{{ $floor->level_name }}</td>
                            <td>
                                <a href="{{ route('admin.zones.floors.slots.scheduleMaintenanceForm',  [$zone->id, $floor->id, $slot->id]) }}"
                                    class="btn btn-info btn-sm">Maintenance</a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No parking slots found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>

    </div>

    <script>
        function confirmBulkDelete(form) {
            const checked = form.querySelectorAll('input[name="slot_ids[]"]:checked');
            if (checked.length === 0) {
                alert('Please select at least one slot.');
                return false;
            }
        }

        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="slot_ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        function updateSlotType(slotId, zoneId, floorId, newType) {
            fetch(`/admin/zones/${zoneId}/floors/${floorId}/slots/${slotId}/update-type`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        type: newType
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        console.log('Slot type updated successfully');
                    } else {
                        console.error('Failed to update slot type');
                    }
                })
                .catch(err => console.error(err));
        }
    </script>
@endsection
