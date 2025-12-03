@extends('layout') 

@section('content') 
<div class="container">
    <h1>Car Park Management System</h1>

    @if (\Session::has('success'))
    <div class="alert alert-success">
        <p>{{ \Session::get('success') }}</p>
    </div>
    @endif

    <a href="{{ route('zones.create') }}" class="btn btn-primary mb-3">Add Zone</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Zone</th>
                <th>Slot Number</th>
                <th>Status</th>
                <th>Level</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($zones as $zone)
            <tr>
                <td>{{ $zone->id }}</td>
                <td>{{ $zone->zone_code }}</td>
                <td>{{ $zone->zone_name }}</td>
                <td>{{ $zone->total_slots }}</td>
                <td>{{ $zone->available_slots }}</td>
                <td><a href="{{ route('zones.edit',$zone->id) }}" class="btn btn-warning">Edit</a></td>
                <td>
                    <form action="{{ route('zones.destroy',$zone->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
