@extends('layouts.app')

@section('page_title', 'Branch Dashboard')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Branch</li>
@endsection

@section('content')
<div class="row">
    @foreach ($metrics as $metric)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-white border metric-card">
                <div class="inner">
                    <p class="mb-1 text-muted">{{ $metric['label'] }}</p>
                    <h4>{{ $metric['value'] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
