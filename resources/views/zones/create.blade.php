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
<h2>Add New Zone</h2><br/>
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
        <label for="total_slots">Total Slots:</label>
        <input type="number" name="total_slots" value="{{ old('total_slots') }}">
    </p>
    <p>
        <button type="submit">Submit</button>
    </p>
</form>