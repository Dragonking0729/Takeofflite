@extends('layouts.app')

@section('title')
    {{ trans('titles.sheet') }}
@endsection

@section('content')
    @include('partials.tabs')
    <div class="d-flex p-1 mb-2">
        @include('partials.sheet-sidebar')
        @include('sheet.sheet')
    </div>
@endsection