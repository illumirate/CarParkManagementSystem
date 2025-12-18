{{-- Author: Adam Chin Wai Kin --}}@extends('layout')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h2>Add New Floor</h2><br />
    <form method="post" action="{{ route('admin.zones.floors.store', $zone->id) }}">
        @csrf
        <p>
            <label for="floor_name">Floor Name:</label>
            <input type="text" name="floor_name" value="{{ old('floor_name') }}">
        </p>
        <p>
            <label for="total_slots">Total Slots:</label>
            <input type="number" name="total_slots" value="{{ old('total_slots') }}">
        </p>
        <p>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.zones.floors.index', $zone->id) }}" class="btn btn-secondary">Cancel</a>
        </p>
    </form>
@endsection
