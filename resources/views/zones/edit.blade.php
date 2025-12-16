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

        <form method="post" action="{{ route('admin.zones.update', $zone->id) }}">
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
                <input type="text" class="form-control"
                    value="{{ $zone->type == 'single' ? 'Single-Leveled' : 'Multi-Leveled' }}" readonly>
                <input type="hidden" name="type" value="{{ $zone->type }}">
            </p>


            <div id="single_fields" style="display: {{ $zone->type == 'single' ? 'block' : 'none' }}">
                <p>
                    <a href="{{ route('admin.zones.floors.index', $zone->id) }}" class="btn btn-info mb-3">
                        Floor Management
                    </a>
                </p>
            </div>

            <div id="multi_fields" style="display: {{ $zone->type == 'multi' ? 'block' : 'none' }}">
                <h4>This is a Multi-Level Zone</h4>

                <a href="{{ route('admin.zones.floors.index', $zone->id) }}" class="btn btn-info mb-3">
                    Floor Management
                </a>
            </div>

            <p>
                <button type="submit" class="btn btn-primary">Update Zone</button>
                <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary">Cancel</a>
            </p>
        </form>

    </div>

    <script>
        const zoneType = document.getElementById('zone_type');
        const singleFields = document.getElementById('single_fields');
        const multiFields = document.getElementById('multi_fields');

        zoneType.addEventListener('change', function() {
            if (this.value === 'single') {
                singleFields.style.display = 'block';
                multiFields.style.display = 'none';
            } else {
                singleFields.style.display = 'none';
                multiFields.style.display = 'block';
            }
        });
    </script>
@endsection
