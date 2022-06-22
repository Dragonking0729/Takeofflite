@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>

    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <script>document.write("<link rel='stylesheet' href='{{asset("css/invoice.css")}}?v=" + Date.now() + "'><\/link>");</script>
@endsection

{{--<aside>--}}
<nav id="sidebar" class="border-right border-light mb-3 {{$is_sidebar_open ? '' : 'active'}}">
    @csrf
    <div class="d-flex ask_interview_div">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse"
                    class="btn btn-outline-secondary {{$is_sidebar_open ? 'open' : ''}}"
                    data-sidebar_status="{{$is_sidebar_open}}">
            </button>
        </div>
        <a href="?helphero_tour=PEtSgtRwWe" class="ml-2 my-auto ss_info" title="Guide"></a>
    </div>

    <div id="invoice_item_list_panel">
        <div class="py-1">
            <div class="tree_search_area">
                <div class="my-auto mr-1">
                    <input class="form-control form-control-sm" name="a_search_tree_key" id="a_search_tree_key"
                           style="height: 36px;" placeholder="SEARCH E.G BRICKS">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 p-0 ss_search_tree_icon"
                        id="a_search_tree"
                        title="Search">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm p-0 ss_clear_search_icon"
                        id="a_clear_search"
                        title="Clear">
                </button>
            </div>
        </div>

        <div class="invoice_list_area">
            <div class="d-flex justify-content-around" style="padding-top: 1px; padding-bottom: 5px;">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_expand" title="Expand All"
                        onclick="$('#invoice_list').jstree('open_all');">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_collapse"
                        title="Collapse All"
                        onclick="$('#invoice_list').jstree('close_all');">
                </button>
            </div>
            <div id="invoice_list">
                Invoice Item List
            </div>
        </div>
    </div>

</nav>
{{--</aside>--}}


