@extends('layout')
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
<h2>Product Details</h2><br/>
<form method="post" action="{{ route('admin.zones.update', $id) }}">
    @csrf
    <input name="_method" type="hidden" value="PATCH">
    <p>
        <label for="zone_code">Zone Code:</label>
        <input type="text" name="zone_code" value="{{ $zone->zone_code }}">
    </p>
    <p>
        <label for="zone_name">Zone Name:</label>
        <input type="text" name="zone_name" value="{{ $zone->zone_name }}">
    </p>
    <p>
        <label for="total_slots">Total Slots:</label>
        <input type="number" name="total_slots" value="{{ $zone->total_slots }}">
    </p>
    <p>
        <button type="submit">Submit</button>
    </p>
</form>
