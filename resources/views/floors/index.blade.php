@extends('layout')

@section('content')
    <div class="container">
        <h1>Floor Management — {{ $zone->zone_name }} </h1>

        @if (Session::has('success'))
            <div class="alert alert-success">
                <p>{{ Session::get('success') }}</p>
            </div>
        @endif

        <a href="{{ route('admin.zones.floors.create', $zone->id) }}" class="btn btn-primary mb-3">Add Floor</a>

        <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary mb-3">Back to Zones</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Floor Name</th>
                    <th>Total Slots</th>
                    <th>Available Slots</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($floors as $floor)
                    <tr>
                        <td>{{ $floor->id }}</td>
                        <td>{{ $floor->level_name }}</td>
                        <td>{{ $floor->total_slots }}</td>
                        <td>{{ $floor->available_slots }}</td>

                        <td>
                            <a href="{{ route('admin.zones.floors.slots.index', [$zone->id, $floor->id]) }}"
                                class="btn btn-warning">Manage Slots</a>
                        </td>


                        <td>
                            <form action="{{ route('admin.zones.floors.destroy', [$zone->id, $floor->id]) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">
                                    Delete
                                </button>
                            </form>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
