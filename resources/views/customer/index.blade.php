@extends('customer.layout')

@section('title')
    {{ trans('titles.customer_portal') }}
@endsection

@section('content')
    <div class="d-flex p-1 mb-2">
        @include('customer.customer_portal_sidebar')
        @include('customer.customer_portal')
    </div>
@endsection