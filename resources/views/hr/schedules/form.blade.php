@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Schedule' : 'Edit Schedule')
@section('content')
@php
    $selectedUserId = (int) old('user_id', $schedule->user_id);
    $selectedBranchId = (int) old('branch_id', $schedule->branch_id);
    $selectedDate = old('schedule_date', optional($schedule->schedule_date)->format('Y-m-d') ?: now()->toDateString());
    $selectedType = old('schedule_type', $schedule->schedule_type ?: 'fixed');
    $selectedTimeIn = old('time_in', $schedule->time_in);
    $selectedTimeOut = old('time_out', $schedule->time_out);
    $selectedBreakStart = old('break_start', $schedule->break_start);
    $selectedBreakEnd = old('break_end', $schedule->break_end);
    $isRestDay = (bool) old('is_rest_day', $schedule->is_rest_day);
    $isBulkMode = (bool) old('bulk_mode', false);
    $bulkDateFrom = old('date_from', now()->startOfWeek()->toDateString());
    $bulkDateTo = old('date_to', now()->endOfWeek()->toDateString());
    $selectedWeekdays = collect(old('weekdays', [1, 2, 3, 4, 5]))->map(fn ($day) => (int) $day)->all();
    $weekdayOptions = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        0 => 'Sun',
    ];
@endphp
<form method="POST" action="{{ $mode === 'create' ? route('hr.schedules.store') : route('hr.schedules.update', $schedule) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2 mb-md-0">
                    <strong>{{ $mode === 'create' ? 'Create Staff Schedule' : 'Edit Staff Schedule' }}</strong>
                    <div class="small text-muted">Tip: Use a shift preset to fill common hours quickly.</div>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Shift presets">
                    <button type="button" class="btn btn-outline-secondary js-shift-preset" data-type="fixed" data-time-in="08:00" data-time-out="17:00" data-break-start="12:00" data-break-end="13:00">Day Shift</button>
                    <button type="button" class="btn btn-outline-secondary js-shift-preset" data-type="fixed" data-time-in="09:00" data-time-out="18:00" data-break-start="13:00" data-break-end="14:00">Mid Shift</button>
                    <button type="button" class="btn btn-outline-secondary js-shift-preset" data-type="rotating" data-time-in="22:00" data-time-out="06:00" data-break-start="02:00" data-break-end="02:30">Night Shift</button>
                    <button type="button" class="btn btn-outline-danger js-rest-day">Rest Day</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label>Employee *</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-primary-branch-id="{{ $user->primary_branch_id }}" @selected($selectedUserId === $user->id)>{{ $user->display_name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Selecting an employee can auto-pick their primary branch.</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Branch *</label>
                    <select name="branch_id" id="branch_id" class="form-control" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Schedule Date *</label>
                    <input type="date" name="schedule_date" id="schedule_date" class="form-control" value="{{ $selectedDate }}" {{ $mode === 'create' ? '' : 'required' }}>
                    @if ($mode === 'create')
                        <small class="form-text text-muted">Single date mode only. Enable weekly bulk create below for date ranges.</small>
                    @endif
                </div>
            </div>

            @if ($mode === 'create')
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                        <div>
                            <strong>Weekly Bulk Create</strong>
                            <div class="small text-muted">Generate schedules for multiple dates in one save.</div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bulk_mode" name="bulk_mode" value="1" @checked($isBulkMode)>
                            <label class="custom-control-label" for="bulk_mode">Enable bulk mode</label>
                        </div>
                    </div>

                    <div id="bulk_controls" class="{{ $isBulkMode ? '' : 'd-none' }}">
                        <div class="form-row">
                            <div class="col-md-3 mb-3">
                                <label>Date From *</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $bulkDateFrom }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Date To *</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $bulkDateTo }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Weekdays *</label>
                                <div class="d-flex flex-wrap align-items-center">
                                    @foreach($weekdayOptions as $weekdayValue => $weekdayLabel)
                                        <div class="custom-control custom-checkbox mr-3 mb-1">
                                            <input type="checkbox" class="custom-control-input" id="weekday_{{ $weekdayValue }}" name="weekdays[]" value="{{ $weekdayValue }}" @checked(in_array($weekdayValue, $selectedWeekdays, true))>
                                            <label class="custom-control-label" for="weekday_{{ $weekdayValue }}">{{ $weekdayLabel }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label>Schedule Type *</label>
                    <select name="schedule_type" id="schedule_type" class="form-control" required>
                        @foreach(['fixed','rotating','flexible'] as $type)
                            <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Time In</label>
                    <input type="time" name="time_in" id="time_in" class="form-control js-time-field" value="{{ $selectedTimeIn }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Time Out</label>
                    <input type="time" name="time_out" id="time_out" class="form-control js-time-field" value="{{ $selectedTimeOut }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Break Start</label>
                    <input type="time" name="break_start" id="break_start" class="form-control js-time-field" value="{{ $selectedBreakStart }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Break End</label>
                    <input type="time" name="break_end" id="break_end" class="form-control js-time-field" value="{{ $selectedBreakEnd }}">
                </div>
                <div class="col-md-1 mb-3">
                    <label>Rest</label>
                    <div>
                        <input type="checkbox" id="is_rest_day" name="is_rest_day" value="1" @checked($isRestDay)>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.schedules.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Schedule</button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var userSelect = document.getElementById('user_id');
    var branchSelect = document.getElementById('branch_id');
    var scheduleDate = document.getElementById('schedule_date');
    var scheduleType = document.getElementById('schedule_type');
    var restDayCheckbox = document.getElementById('is_rest_day');
    var bulkModeCheckbox = document.getElementById('bulk_mode');
    var bulkControls = document.getElementById('bulk_controls');
    var bulkFields = ['date_from', 'date_to'];
    var timeFields = Array.prototype.slice.call(document.querySelectorAll('.js-time-field'));
    var presetButtons = Array.prototype.slice.call(document.querySelectorAll('.js-shift-preset'));
    var restDayButton = document.querySelector('.js-rest-day');

    function setTimeFieldsDisabled(disabled) {
        timeFields.forEach(function (field) {
            field.disabled = disabled;
            if (disabled) {
                field.value = '';
            }
        });
    }

    function applyRestDay(isRestDay) {
        restDayCheckbox.checked = isRestDay;
        setTimeFieldsDisabled(isRestDay);
    }

    function applyPreset(button) {
        scheduleType.value = button.getAttribute('data-type') || 'fixed';
        document.getElementById('time_in').value = button.getAttribute('data-time-in') || '';
        document.getElementById('time_out').value = button.getAttribute('data-time-out') || '';
        document.getElementById('break_start').value = button.getAttribute('data-break-start') || '';
        document.getElementById('break_end').value = button.getAttribute('data-break-end') || '';
        applyRestDay(false);
    }

    function syncBranchFromEmployee() {
        if (!userSelect || !branchSelect) {
            return;
        }

        var selectedOption = userSelect.options[userSelect.selectedIndex];
        var primaryBranchId = selectedOption ? selectedOption.getAttribute('data-primary-branch-id') : '';
        if (!primaryBranchId) {
            return;
        }

        branchSelect.value = primaryBranchId;
    }

    function setBulkMode(enabled) {
        if (!bulkModeCheckbox || !bulkControls) {
            return;
        }

        bulkControls.classList.toggle('d-none', !enabled);

        if (scheduleDate) {
            scheduleDate.disabled = enabled;
            scheduleDate.required = !enabled;
        }

        bulkFields.forEach(function (fieldId) {
            var field = document.getElementById(fieldId);
            if (!field) {
                return;
            }

            field.required = enabled;
        });
    }

    restDayCheckbox.addEventListener('change', function () {
        setTimeFieldsDisabled(restDayCheckbox.checked);
    });

    presetButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyPreset(button);
        });
    });

    if (restDayButton) {
        restDayButton.addEventListener('click', function () {
            applyRestDay(true);
        });
    }

    if (userSelect) {
        userSelect.addEventListener('change', function () {
            syncBranchFromEmployee();
        });
    }

    if (bulkModeCheckbox) {
        bulkModeCheckbox.addEventListener('change', function () {
            setBulkMode(bulkModeCheckbox.checked);
        });
    }

    setTimeFieldsDisabled(restDayCheckbox.checked);
    setBulkMode(bulkModeCheckbox ? bulkModeCheckbox.checked : false);
});
</script>
@endsection
