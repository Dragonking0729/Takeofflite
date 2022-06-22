<div class="container-fluid">
    {{--invoice area--}}
    <div class="row" id="invoice_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_invoice" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="invoice_preview_block">

    </div>
    {{--end invoice area--}}


    {{--proposal area--}}
    <div class="row" id="proposal_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_proposal" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="proposal_preview_block">

    </div>
    {{--end proposal area--}}


    {{--daily log area--}}
    <div class="row" id="daily_log_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_daily_log" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="daily_log_preview_block">

    </div>
    {{--end daily log area--}}


    {{--picture area--}}
    <div class="row" id="picture_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_picture" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="picture_preview_block">

    </div>
    {{--end picture area--}}


    {{--video area--}}
    <div class="row" id="video_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_video" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="video_preview_block">

    </div>
    {{--end video area--}}


    {{--other files area--}}
    <div class="row" id="other_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center">
            <button type="button" class="btn btn-sm btn-link mr-2 print_other" title="Print" data-id="">
            </button>
        </div>
    </div>

    <div class="row mt-1" id="other_preview_block">

    </div>
    {{--end other files area--}}

</div>

@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    <script src="{{ asset('js/print/printThis.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>

    @include('scripts.customer.customer_portal_sidebar-js')
    @include('scripts.customer.customer_portal-js')

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js"></script>--}}
    {{--@include('scripts.resizable-sidebar-js');--}}
@endsection