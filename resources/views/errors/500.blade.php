@extends('layouts.app')

@section('title')
    {{ trans('titles.500') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}" />
@endsection

@section('content')
    <div id="notfound">
        <div class="notfound">
            <div class="notfound-404"></div>
            <h1>500</h1>
            <h2>Oops! Server error</h2>
            <p>Hm... It appears the server crashed. We will be back soon!</p>
            <a href="{{url('dashboard')}}">Back to homepage</a>
        </div>
    </div>

@endsection



@section('script')
@endsection