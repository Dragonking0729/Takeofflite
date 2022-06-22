@extends('layouts.app')

@section('title')
    {{ trans('titles.invoices') }}
@endsection

@section('content')
    @include('partials.tabs')
    <div class="d-flex p-1 mb-2">
        @include('partials.invoice-sidebar')
        @include('invoices.invoice')
    </div>
@endsection