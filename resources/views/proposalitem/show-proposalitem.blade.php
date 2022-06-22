@extends('layouts.app')

@section('title')
{{ trans('titles.proposalitem') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/proposalitem.css') }}" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    PROPOSAL ITEMS
                </div>

                @include('proposalitem.default-proposalitem')
                @include('proposalitem.create-proposalitem')

            </div>
        </div>
    </div>
</div>

@include('modals.proposalitem.proposalitem-tree')
@include('modals.proposalitem.proposalitem-create-tree')

@endsection



@section('script')

{{-- hot-formula-parser library --}}
<script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>

{{-- cost item script --}}
@include('scripts.proposalitem.proposalitem-js')
@include('scripts.proposalitem.proposalitem-treejs')
@endsection