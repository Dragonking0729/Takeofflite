@extends('layouts.app')

@section('title')
    {{ trans('titles.estimate') }}
@endsection

@section('content')
    @include('partials.tabs')
    <div class="p-1 mb-2" style="display: -webkit-box;">
        @include('partials.spreadsheet-sidebar')
        @include('estimate.spreadsheet')
    </div>
@endsection