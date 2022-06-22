@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>

    <script>document.write("<link rel='stylesheet' href='{{asset("css/customer_portal_sidebar.css")}}?v=" + Date.now() + "'><\/link>");</script>
    <script>document.write("<link rel='stylesheet' href='{{asset("css/customer_proposal.css")}}?v=" + Date.now() + "'><\/link>");</script>
    <script>document.write("<link rel='stylesheet' href='{{asset("css/customer_invoice.css")}}?v=" + Date.now() + "'><\/link>");</script>
    <script>document.write("<link rel='stylesheet' href='{{asset("css/customer_daily_log.css")}}?v=" + Date.now() + "'><\/link>");</script>
    <script>document.write("<link rel='stylesheet' href='{{asset("css/customer_picture.css")}}?v=" + Date.now() + "'><\/link>");</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

@endsection

{{--<aside>--}}
<nav id="sidebar" class="border-right border-light mb-3">
    @csrf
    <div id="customer_portal_item_list_panel">
        <div>
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

        <div class="customer_portal_list_area">
            <div class="d-flex justify-content-around" style="padding-top: 1px; padding-bottom: 5px;">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_expand" title="Expand All"
                        onclick="$('#customer_portal_list').jstree('open_all');">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_collapse"
                        title="Collapse All"
                        onclick="$('#customer_portal_list').jstree('close_all');">
                </button>
            </div>
            <div id="customer_portal_list">
                Customer portal Item List
            </div>
        </div>
    </div>

</nav>
{{--</aside>--}}


