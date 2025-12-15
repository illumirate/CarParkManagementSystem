@extends('layout')

@section('title', 'Book Parking Slot - TARUMT Car Park')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-parking me-2"></i>Book a Parking Slot</h5>
            </div>
            <div class="card-body">
                @if($vehicles->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You need to register a vehicle before booking.
                    <a href="{{ route('vehicles.create') }}" class="alert-link">Add a vehicle now</a>.
                </div>
                @else
                <form id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="zone_id" class="form-label">Parking Zone <span class="text-danger">*</span></label>
                            <select class="form-select" id="zone_id" name="zone_id" required>
                                <option value="">Select a zone</option>
                                @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->zone_name }} ({{ $zone->zone_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="level_id" class="form-label">Level</label>
                            <select class="form-select" id="level_id" name="level_id" disabled>
                                <option value="">All levels</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date"
                                   min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+6 days')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time"
                                   min="06:00" max="21:00" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="end_time" name="end_time"
                                   min="07:00" max="22:00" required>
                            <div class="form-text">Max until 10:00 PM</div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search me-1"></i>Search Available Slots
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                {{-- Search Results --}}
                <div id="searchResults" style="display: none;">
                    <h5 class="mb-3">Available Slots</h5>
                    <div id="slotsContainer" class="row"></div>
                </div>

                <div id="noResults" style="display: none;" class="text-center py-4">
                    <i class="fas fa-parking fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No available slots found for the selected criteria.</p>
                </div>

                <div id="loadingSpinner" style="display: none;" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Searching for available slots...</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Booking Summary --}}
        <div class="card shadow" id="bookingSummary" style="display: none;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <input type="hidden" name="parking_slot_id" id="selected_slot_id">
                    <input type="hidden" name="booking_date" id="booking_date">
                    <input type="hidden" name="start_time" id="booking_start_time">
                    <input type="hidden" name="end_time" id="booking_end_time">

                    <div class="mb-3">
                        <label class="form-label text-muted small">Selected Slot</label>
                        <p class="mb-0 fw-bold" id="summary_slot">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Date & Time</label>
                        <p class="mb-0" id="summary_datetime">-</p>
                    </div>

                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">
                            Select Vehicle <span class="text-danger">*</span>
                            <small class="text-muted">(via Web Service API)</small>
                        </label>
                        <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                            <option value="">Loading vehicles...</option>
                        </select>
                        <div id="vehicles-api-status" class="form-text text-muted"></div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Duration</span>
                        <span id="summary_duration">-</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Fee</span>
                        <span class="fw-bold text-success fs-5" id="summary_fee">RM 0.00</span>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        Fee: RM 2/hour, capped at RM 5 max
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check me-1"></i>Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pricing Info --}}
        <div class="card mt-3">
            <div class="card-header bg-light">
                <i class="fas fa-info-circle me-1"></i>Pricing Information
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li>RM 2.00 per hour</li>
                    <li>Maximum RM 5.00 per booking</li>
                    <li>Minimum 1 hour booking</li>
                    <li>Bookings must end by 10:00 PM</li>
                </ul>
            </div>
        </div>

        {{-- Your Balance --}}
        <div class="card mt-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <span><i class="fas fa-wallet me-2"></i>Your Balance</span>
                <span class="badge bg-success fs-6">RM {{ number_format(auth()->user()->credit_balance, 2) }}</span>
            </div>
            <div class="card-footer bg-transparent">
                <a href="{{ route('credits.index') }}" class="btn btn-outline-success btn-sm w-100">
                    <i class="fas fa-plus me-1"></i>Top Up Credits
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const zoneSelect = document.getElementById('zone_id');
    const levelSelect = document.getElementById('level_id');
    const searchResults = document.getElementById('searchResults');
    const noResults = document.getElementById('noResults');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const slotsContainer = document.getElementById('slotsContainer');
    const bookingSummary = document.getElementById('bookingSummary');
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    // WEB SERVICES - Consume Vehicles API from Auth Module via Frontend AJAX
    const vehicleSelect = document.getElementById('vehicle_id');
    const vehiclesApiStatus = document.getElementById('vehicles-api-status');
    const userId = {{ Auth::id() }};
    const requestId = 'REQ_' + Date.now();
    const timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

    fetch(`/api/users/${userId}/vehicles?requestId=${requestId}&timestamp=${encodeURIComponent(timestamp)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'S' && data.data.length > 0) {
                vehicleSelect.innerHTML = '';
                data.data.forEach(vehicle => {
                    const option = document.createElement('option');
                    option.value = vehicle.id;
                    option.textContent = `${vehicle.plate_number} (${vehicle.vehicle_type.charAt(0).toUpperCase() + vehicle.vehicle_type.slice(1)})`;
                    if (vehicle.is_primary) option.selected = true;
                    vehicleSelect.appendChild(option);
                });
                vehiclesApiStatus.innerHTML = '<i class="fas fa-check-circle text-success"></i> Loaded via API';
                console.log('[API CONSUMED] Vehicles API called successfully via frontend', { requestId, count: data.count });
            } else {
                vehicleSelect.innerHTML = '<option value="">No vehicles found</option>';
                vehiclesApiStatus.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i> No vehicles available';
            }
        })
        .catch(error => {
            console.error('[API CONSUMED] Failed to fetch vehicles:', error);
            vehicleSelect.innerHTML = '<option value="">Error loading vehicles</option>';
            vehiclesApiStatus.innerHTML = '<i class="fas fa-times-circle text-danger"></i> API error';
        });

    // Function to update minimum start time based on selected date
    function updateMinStartTime() {
        const selectedDate = dateInput.value;
        const today = new Date().toISOString().split('T')[0];

        if (selectedDate === today) {
            // If today is selected, minimum start time should be current time (rounded up to next 15 min)
            const now = new Date();
            const minutes = Math.ceil(now.getMinutes() / 15) * 15;
            now.setMinutes(minutes);
            now.setSeconds(0);

            let hours = now.getHours().toString().padStart(2, '0');
            let mins = now.getMinutes().toString().padStart(2, '0');

            // Ensure minimum is at least 06:00
            const minTime = hours + ':' + mins;
            startTimeInput.min = minTime > '06:00' ? minTime : '06:00';

            // If current start time is less than minimum, clear it
            if (startTimeInput.value && startTimeInput.value < startTimeInput.min) {
                startTimeInput.value = '';
            }
        } else {
            // For future dates, reset to default minimum
            startTimeInput.min = '06:00';
        }
    }

    // Update minimum time on date change
    dateInput.addEventListener('change', updateMinStartTime);

    // Also update on page load if date is already set
    if (dateInput.value) {
        updateMinStartTime();
    }

    // Load levels when zone changes
    zoneSelect.addEventListener('change', function() {
        const zoneId = this.value;
        levelSelect.innerHTML = '<option value="">All levels</option>';

        if (zoneId) {
            levelSelect.disabled = false;
            fetch(`/api/zones/${zoneId}/levels`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.levels.forEach(level => {
                            const option = document.createElement('option');
                            option.value = level.id;
                            option.textContent = `${level.level_name} (${level.available_slots} available)`;
                            levelSelect.appendChild(option);
                        });
                    }
                });
        } else {
            levelSelect.disabled = true;
        }
    });

    // Search for available slots
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();

        searchResults.style.display = 'none';
        noResults.style.display = 'none';
        loadingSpinner.style.display = 'block';
        bookingSummary.style.display = 'none';

        const formData = new FormData(searchForm);

        fetch('{{ route("bookings.search") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.slots.length > 0) {
                searchResults.style.display = 'block';
                renderSlots(data.slots, data.fee_formatted);
            } else if (data.success && data.slots.length === 0) {
                noResults.style.display = 'block';
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            loadingSpinner.style.display = 'none';
            alert('Failed to search. Please try again.');
        });
    });

    function renderSlots(slots, feeFormatted) {
        slotsContainer.innerHTML = '';

        slots.forEach(slot => {
            const col = document.createElement('div');
            col.className = 'col-md-4 col-6 mb-3';
            col.innerHTML = `
                <div class="card slot-card h-100" data-slot-id="${slot.id}" data-slot-label="${slot.slot_id}" data-zone="${slot.zone.zone_name}" style="cursor: pointer;">
                    <div class="card-body text-center">
                        <i class="fas fa-parking fa-2x text-success mb-2"></i>
                        <h6 class="mb-1">${slot.slot_id}</h6>
                        <small class="text-muted">${slot.parking_level ? slot.parking_level.level_name : 'Ground'}</small>
                    </div>
                </div>
            `;
            slotsContainer.appendChild(col);
        });

        // Add click handlers to slots
        document.querySelectorAll('.slot-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
                this.classList.add('border-primary', 'bg-light');
                selectSlot(this.dataset.slotId, this.dataset.slotLabel, this.dataset.zone, feeFormatted);
            });
        });
    }

    function selectSlot(slotId, slotLabel, zoneName, feeFormatted) {
        document.getElementById('selected_slot_id').value = slotId;
        document.getElementById('booking_date').value = document.getElementById('date').value;
        document.getElementById('booking_start_time').value = document.getElementById('start_time').value;
        document.getElementById('booking_end_time').value = document.getElementById('end_time').value;

        document.getElementById('summary_slot').textContent = `${slotLabel} - ${zoneName}`;

        const date = new Date(document.getElementById('date').value);
        const dateStr = date.toLocaleDateString('en-MY', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        // Convert to 12-hour format
        function formatTime12h(time24) {
            const [hours, minutes] = time24.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${minutes} ${ampm}`;
        }

        document.getElementById('summary_datetime').textContent = `${dateStr}, ${formatTime12h(startTime)} - ${formatTime12h(endTime)}`;

        // Calculate duration
        const start = new Date(`2000-01-01 ${startTime}`);
        const end = new Date(`2000-01-01 ${endTime}`);
        const totalMinutes = (end - start) / (1000 * 60);
        const hours = Math.floor(totalMinutes / 60);
        const minutes = Math.round(totalMinutes % 60);

        let durationStr = '';
        if (hours > 0) {
            durationStr += `${hours} hour${hours !== 1 ? 's' : ''}`;
        }
        if (minutes > 0) {
            if (hours > 0) durationStr += ' ';
            durationStr += `${minutes} minute${minutes !== 1 ? 's' : ''}`;
        }
        document.getElementById('summary_duration').textContent = durationStr;

        document.getElementById('summary_fee').textContent = feeFormatted;

        bookingSummary.style.display = 'block';
    }
});
</script>
@endpush
