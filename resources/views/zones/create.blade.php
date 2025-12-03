@extends('layout')

@section('content')
    <div class="container mt-4">

        <h2>Add New Zone</h2><br />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('zones.store') }}">
            @csrf

            <p>
                <label for="zone_code">Zone Code:</label>
                <input type="text" name="zone_code" value="{{ old('zone_code') }}">
            </p>

            <p>
                <label for="zone_name">Zone Name:</label>
                <input type="text" name="zone_name" value="{{ old('zone_name') }}">
            </p>


            <p>
                <label for="type">Type:</label>
                <select name="type" id="zone_type" class="form-control">
                    <option value="">-- Select Type --</option>
                    <option value="single" {{ old('type') == 'single' ? 'selected' : '' }}>Single-Leveled</option>
                    <option value="multi" {{ old('type') == 'multi' ? 'selected' : '' }}>Multi-Leveled</option>
                </select>
            </p>

            <div id="single_fields">
                <p>
                    <label for="total_slots">Total Slots:</label>
                    <input type="number" name="total_slots" value="{{ old('total_slots') }}">
                </p>
            </div>

            <div id="multi_fields" style="display:none;">
                <p>
                    <label for="floor_count">How many floors?</label>
                    <input type="number" id="floor_count" min="1">
                    <button type="button" id="generateFloors">Generate</button>
                </p>

                <div id="floor_inputs"></div>
            </div>


            <p>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('zones.index') }}" class="btn btn-secondary">Cancel</a>
            </p>
        </form>
    </div>
    <script>
    document.getElementById('zone_type').addEventListener('change', function() {
        const type = this.value;
        document.getElementById('single_fields').style.display = type === 'single' ? 'block' : 'none';
        document.getElementById('multi_fields').style.display = type === 'multi' ? 'block' : 'none';
    });

    document.getElementById('generateFloors').addEventListener('click', function() {
        const count = parseInt(document.getElementById('floor_count').value);
        const container = document.getElementById('floor_inputs');
        container.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            container.innerHTML += `
            <div>
                <p>
                    <label>Floor ${i} Name:</label>
                    <input type="text" name="floors[${i}][name]" required>
                </p>
                <p>
                    <label>Floor ${i} Total Slots:</label>
                    <input type="number" name="floors[${i}][slots]" required>
                </p>
                <hr>
            </div>
        `;
        }
    });
</script>

@endsection

