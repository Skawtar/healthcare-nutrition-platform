@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Patient Details: {{ $patient->nom }} {{ $patient->prenom }}</h1>

    <!-- Blood Sugar Calendar Section -->
    <div class="card shadow-lg mt-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Blood Sugar Measurements</h3>
                <div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light" id="prev-period">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <button class="btn btn-sm btn-light" id="current-period">
                            @if($viewType === 'day')
                                Today
                            @elseif($viewType === 'week')
                                This Week
                            @else
                                This Month
                            @endif
                        </button>
                        <button class="btn btn-sm btn-light" id="next-period">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="btn-group ml-2">
                        <button class="btn btn-sm btn-light {{ $viewType === 'day' ? 'active' : '' }}" data-period="day">
                            Day
                        </button>
                        <button class="btn btn-sm btn-light {{ $viewType === 'week' ? 'active' : '' }}" data-period="week">
                            Week
                        </button>
                        <button class="btn btn-sm btn-light {{ $viewType === 'month' ? 'active' : '' }}" data-period="month">
                            Month
                        </button>
                    </div>
                    <button class="btn btn-sm btn-light ml-2" id="print-btn">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($measurements->isEmpty())
                <div class="alert alert-info m-4">No blood sugar measurements found for this period.</div>
            @else
                <!-- Day View -->
                @if($viewType === 'day')
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width: 20%;">Time</th>
                                <th class="text-center" style="width: 20%;">Measurement Type</th>
                                <th class="text-center" style="width: 15%;">Value</th>
                                <th class="text-center" style="width: 30%;">Notes</th>
                                <th class="text-center" style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($measurements as $measurement)
                            <tr class="{{ $measurement->value > 180 ? 'table-danger' : ($measurement->value < 70 ? 'table-warning' : 'table-success') }}">
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($measurement->measurement_at)->format('H:i') }}
                                </td>
                                <td class="text-center">
                                    {{ $measurement->measurement_type }}
                                </td>
                                <td class="text-center font-weight-bold">
                                    {{ $measurement->value }}
                                    <small class="d-block text-muted">
                                        @if($measurement->measurement_type === 'Fasting' || $measurement->measurement_type === 'Before Meal')
                                            (Target: 70-130)
                                        @elseif($measurement->measurement_type === 'After Meal')
                                            (Target: <180)
                                        @elseif($measurement->measurement_type === 'Bedtime')
                                            (Target: 90-150)
                                        @else
                                            (Target: <200)
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    {{ $measurement->notes ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-measurement" data-id="{{ $measurement->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-measurement" data-id="{{ $measurement->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Week View -->
                @elseif($viewType === 'week')
                <div class="weekly-grid">
                    <div class="grid-header">
                        <div class="time-header"></div>
                        @foreach($days as $day)
                        <div class="day-header {{ $day['isToday'] ? 'today' : '' }}">
                            <div>{{ $day['dayName'] }}</div>
                            <div>{{ $day['date'] }}</div>
                        </div>
                        @endforeach
                    </div>
                    
                    @foreach($timeSlots as $timeSlot)
                    <div class="grid-row">
                        <div class="time-slot">
                            {{ $timeSlot }}
                        </div>
                        @foreach($days as $day)
                        <div class="day-slot {{ $day['isToday'] ? 'today' : '' }}">
                            @if(isset($groupedMeasurements[$day['date']][$timeSlot]))
                                @foreach($groupedMeasurements[$day['date']][$timeSlot] as $measurement)
                                <div class="measurement-card 
                                    {{ $measurement->value > 180 ? 'high' : ($measurement->value < 70 ? 'low' : 'normal') }}">
                                    <div class="measurement-value">{{ $measurement->value }}</div>
                                    <div class="measurement-type">{{ $measurement->measurement_type }}</div>
                                    @if($measurement->notes)
                                    <div class="measurement-notes" title="{{ $measurement->notes }}">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                
                <!-- Month View -->
                @else
                <div class="monthly-calendar">
                    <div class="calendar-header">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="calendar-day-header">{{ $day }}</div>
                        @endforeach
                    </div>
                    <div class="calendar-body">
                        @foreach($weeks as $week)
                        <div class="calendar-week">
                            @foreach($week as $day)
                            <div class="calendar-day {{ $day['isToday'] ? 'today' : '' }} {{ $day['isCurrentMonth'] ? '' : 'other-month' }}">
                                <div class="day-number">{{ $day['day'] }}</div>
                                @if(isset($day['measurements']))
                                <div class="day-measurements">
                                    @foreach($day['measurements'] as $measurement)
                                    <div class="measurement-dot 
                                        {{ $measurement->value > 180 ? 'high' : ($measurement->value < 70 ? 'low' : 'normal') }}"
                                        title="{{ $measurement->measurement_type }}: {{ $measurement->value }} at {{ \Carbon\Carbon::parse($measurement->measurement_at)->format('H:i') }}">
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Summary Stats -->
                <div class="row mt-4 px-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="stat-box p-2 text-center">
                                            <h6 class="text-muted">Readings</h6>
                                            <h4>{{ $measurements->count() }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-box p-2 text-center">
                                            <h6 class="text-muted">Average</h6>
                                            <h4>{{ round($measurements->avg('value'), 1) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-box p-2 text-center">
                                            <h6 class="text-muted">Highest</h6>
                                            <h4 class="text-danger">{{ $measurements->max('value') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-box p-2 text-center">
                                            <h6 class="text-muted">Lowest</h6>
                                            <h4 class="text-warning">{{ $measurements->min('value') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-box p-2">
                                            <h6 class="text-muted">Measurement Legend</h6>
                                            <div class="d-flex flex-wrap">
                                                <span class="badge badge-success mr-2 mb-1">Normal</span>
                                                <span class="badge badge-warning mr-2 mb-1">Low</span>
                                                <span class="badge badge-danger mr-2 mb-1">High</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="card-footer text-muted">
            <small>Last updated: {{ now()->format('M d, Y H:i') }}</small>
        </div>
    </div>
</div>

<!-- Add Measurement Modal -->
<div class="modal fade" id="measurementModal" tabindex="-1" role="dialog" aria-labelledby="measurementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="measurementModalLabel">Add Blood Sugar Measurement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="measurementForm" method="POST">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="measurement_at">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="measurement_at" name="measurement_at" required>
                    </div>
                    <div class="form-group">
                        <label for="measurement_type">Measurement Type</label>
                        <select class="form-control" id="measurement_type" name="measurement_type" required>
                            <option value="Fasting">Fasting</option>
                            <option value="Before Meal">Before Meal</option>
                            <option value="After Meal">After Meal</option>
                            <option value="Bedtime">Bedtime</option>
                            <option value="Random">Random</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="value">Blood Sugar Value (mg/dL)</label>
                        <input type="number" class="form-control" id="value" name="value" min="0" max="500" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Measurement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Base styles */
    .weekly-grid, .monthly-calendar {
        display: flex;
        flex-direction: column;
        font-size: 0.9rem;
    }
    
    .grid-header, .calendar-header {
        display: flex;
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .day-header, .calendar-day-header {
        flex: 1;
        text-align: center;
        padding: 8px;
        border-right: 1px solid #dee2e6;
    }
    
    .day-header.today, .calendar-day.today {
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    .grid-row, .calendar-week {
        display: flex;
        min-height: 60px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .time-slot, .calendar-day {
        flex: 1;
        padding: 4px;
        border-right: 1px solid #dee2e6;
        position: relative;
    }
    
    .time-slot {
        width: 60px;
        text-align: right;
        padding-right: 8px;
        background-color: #f8f9fa;
        font-size: 0.8em;
    }
    
    .measurement-card {
        border-radius: 4px;
        padding: 4px;
        margin: 2px;
        font-size: 0.8em;
        text-align: center;
        cursor: pointer;
    }
    
    .measurement-card.normal {
        background-color: #d4edda;
        color: #155724;
    }
    
    .measurement-card.low {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .measurement-card.high {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .measurement-value {
        font-weight: bold;
        font-size: 1.1em;
    }
    
    .measurement-type {
        font-size: 0.8em;
    }
    
    .measurement-notes {
        color: #6c757d;
    }
    
    .calendar-day {
        min-height: 80px;
    }
    
    .calendar-day.other-month {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    
    .day-number {
        text-align: right;
        padding: 2px;
        font-weight: bold;
    }
    
    .day-measurements {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .measurement-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin: 1px;
    }
    
    .measurement-dot.normal {
        background-color: #28a745;
    }
    
    .measurement-dot.low {
        background-color: #ffc107;
    }
    
    .measurement-dot.high {
        background-color: #dc3545;
    }
    
    .stat-box {
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    @media print {
        .card-header {
            background-color: white !important;
            color: black !important;
        }
        
        .table-info {
            background-color: #f8f9fa !important;
        }
    }
</style>

<script>
$(document).ready(function() {
    // Navigation between periods
    $('#prev-period').click(function() {
        navigatePeriod(-1);
    });
    
    $('#next-period').click(function() {
        navigatePeriod(1);
    });
    
    $('#current-period').click(function() {
        window.location.href = "{{ route('patients.blood-calendar', ['patient' => $patient->id]) }}?view={{ $viewType }}";
    });
    
    // View type switching
    $('[data-period]').click(function() {
        const period = $(this).data('period');
        window.location.href = "{{ route('patients.blood-calendar', ['patient' => $patient->id]) }}?view=" + period;
    });
    
    // Print functionality
    $('#print-btn').click(function() {
        window.print();
    });
    
    // Add new measurement
    $('#add-measurement').click(function() {
        $('#measurementForm')[0].reset();
        $('#measurementModalLabel').text('Add Blood Sugar Measurement');
        $('#measurement_at').val(new Date().toISOString().slice(0, 16));
        $('#measurementModal').modal('show');
    });
    
    // Edit measurement
    $('.edit-measurement').click(function() {
        const id = $(this).data('id');
        // AJAX call to get measurement data
        // Then populate the modal and show it
        $('#measurementModalLabel').text('Edit Blood Sugar Measurement');
        $('#measurementModal').modal('show');
    });
    
    // Delete measurement
    $('.delete-measurement').click(function() {
        if(confirm('Are you sure you want to delete this measurement?')) {
            const id = $(this).data('id');
            // AJAX call to delete measurement
            // Then reload the page or remove the row
        }
    });
    
    function navigatePeriod(direction) {
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        
        const currentDate = params.get('date') || new Date().toISOString().split('T')[0];
        const viewType = params.get('view') || 'week';
        
        let newDate;
        if(viewType === 'day') {
            newDate = new Date(currentDate);
            newDate.setDate(newDate.getDate() + direction);
        } else if(viewType === 'week') {
            newDate = new Date(currentDate);
            newDate.setDate(newDate.getDate() + (direction * 7));
        } else { // month
            newDate = new Date(currentDate);
            newDate.setMonth(newDate.getMonth() + direction);
        }
        
        params.set('date', newDate.toISOString().split('T')[0]);
        window.location.href = url.pathname + '?' + params.toString();
    }
});
</script>
@endsection