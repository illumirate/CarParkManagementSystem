@extends('layout')

@section('content')
    <div class="container mt-4">

        <h2>Edit Zone</h2><br />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('zones.update', $zone->id) }}">
            @csrf
            @method('PATCH')

            <p>
                <label for="zone_code">Zone Code:</label>
                <input type="text" name="zone_code" value="{{ old('zone_code', $zone->zone_code) }}">
            </p>

            <p>
                <label for="zone_name">Zone Name:</label>
                <input type="text" name="zone_name" value="{{ old('zone_name', $zone->zone_name) }}">
            </p>

            <p>
                <label for="type">Type:</label>
                <select name="type" id="zone_type" class="form-control">
                    <option value="single" {{ $zone->type == 'single' ? 'selected' : '' }}>Single-Leveled</option>
                    <option value="multi" {{ $zone->type == 'multi' ? 'selected' : '' }}>Multi-Leveled</option>
                </select>
            </p>

            {{-- Single level field --}}
            <div id="single_fields" style="display: {{ $zone->type == 'single' ? 'block' : 'none' }}">
                <p>
                    <label for="total_slots">Total Slots:</label>
                    <input type="number" name="total_slots" value="{{ old('total_slots', $zone->total_slots) }}">
                </p>
            </div>

            {{-- Multi-level fields --}}
            <div id="multi_fields" style="display: {{ $zone->type == 'multi' ? 'block' : 'none' }}">
                <h4>Floors</h4>

                <div id="floor_inputs">
                    @if ($zone->type == 'multi')
                        @foreach ($parkingLevels as $i => $floor)
                            <div class="border p-2 mb-2">
                                <label>Floor Name:</label>
                                <input type="text" name="floors[{{ $i }}][name]"
                                    value="{{ $floor->level_name }}">
                                <label>Total Slots:</label>
                                <input type="number" name="floors[{{ $i }}][slots]"
                                    value="{{ $floor->total_slots }}">
                            </div>
                        @endforeach
                    @endif
                </div>

                <p>
                    <label for="floor_count">Add more floors:</label>
                    <input type="number" id="floor_count" min="1">
                    <button type="button" id="generateFloors">Generate</button>
                </p>
            </div>

            <p>
                <button type="submit" class="btn btn-primary">Update Zone</button>
                <a href="{{ route('zones.index') }}" class="btn btn-secondary">Cancel</a>
            </p>
        </form>

    </div>
    <script>
        const zoneType = document.getElementById('zone_type');
        const singleFields = document.getElementById('single_fields');
        const multiFields = document.getElementById('multi_fields');
        const floorInputs = document.getElementById('floor_inputs');

        zoneType.addEventListener('change', function() {
            if (this.value === 'single') {
                singleFields.style.display = 'block';
                multiFields.style.display = 'none';
            } else {
                singleFields.style.display = 'none';
                multiFields.style.display = 'block';
            }
        });

        document.getElementById('generateFloors').addEventListener('click', function() {
            const count = parseInt(document.getElementById('floor_count').value);
            if (isNaN(count) || count < 1) return;

            for (let i = 0; i < count; i++) {
                floorInputs.innerHTML += `
                <div class="border p-2 mb-2">
                    <label>Floor Name:</label>
                    <input type="text" name="floors[new][name][]" required>
                    <label>Total Slots:</label>
                    <input type="number" name="floors[new][slots][]" required>
                </div>
            `;
            }
        });
    </script>
@endsection
