@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />

    {{-- youtube overlay plugin --}}
    <link href="{{ asset('css/youtube-overlay.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/youtube-overlay.js') }}"></script>

    {{-- jexcel v7 --}}
    <script src="{{ asset('js/jexcel.js') }}"></script>
    <script src="{{ asset('js/jsuites.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/jsuites.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('css/jexcel.css') }}" type="text/css" />

    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Material+Icons" />

    {{-- jspreadsheet plugins --}}
    {{-- <script src="{{asset('js/jexcel.conditionalstyle.js')}}"></script> --}}

    {{-- <script src="https://jexcel.net/plugins/jexcel.download.js"></script> --}}
    {{-- <script src="{{asset('js/jexcel.download.js')}}"></script> --}}
    <script src="{{ asset('js/custom-jspreadsheet-download.js') }}"></script>
    <script src="{{ asset('js/jexcel.print.js') }}"></script>

    <script>
        document.write("<link rel='stylesheet' href='{{ asset('css/spreadsheet.css') }}?v=" + Date.now() + "'><\/link>");
    </script>
@endsection

{{-- <aside> --}}
<nav id="sidebar" class="border-right border-light mb-3 {{ $is_sidebar_open ? '' : 'active' }}">
    @csrf
    <div class="d-flex justify-content-between ask_interview_div">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse"
                class="btn btn-outline-secondary {{ $is_sidebar_open ? 'open' : '' }}"
                data-sidebar_status="{{ $is_sidebar_open }}">
            </button>
        </div>

        <div class="p-1">
            <button type="button" class="btn btn-outline-secondary p-0 item-takeoff-btn ss_item_icon" title="Item">
            </button>
        </div>

        <div class="p-1">
            <button type="button" class="btn btn-active p-0 assembly-takeoff-btn ss_interview_icon" title="Interview">
            </button>
        </div>

        <div class="p-1">
            <button type="button" class="btn btn-outline-secondary p-0 add-on-takeoff-btn ss_add_on_icon"
                title="Add ons">
            </button>
        </div>

        <div class="my-auto px-1 turn_on_interview_div">
            <button type="button" class="btn p-0 turn-on-interview ss_toggle" data-checked="true"
                title="Turn on interview">
            </button>
        </div>
    </div>

    <div id="costitem_list_panel" style="display: none;">
        <div class="py-1">
            <div class="tree_search_area">
                <div class="my-auto mr-1">
                    <input class="form-control form-control-sm" name="search_tree_key" id="search_tree_key"
                        style="height: 36px;" placeholder="SEARCH E.G BRICKS">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 p-0 ss_search_tree_icon"
                    id="search_tree" title="Search">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm p-0 ss_clear_search_icon"
                    id="clear_search" title="Clear">
                </button>
            </div>
        </div>

        <div class="costitem_list_area">
            <div class="d-flex justify-content-around" style="padding-top: 1px; padding-bottom: 5px;">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_expand" title="Expand All"
                    onclick="$('#costitem_list').jstree('open_all');">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_collapse"
                    title="Collapse All" onclick="$('#costitem_list').jstree('close_all');">
                </button>
            </div>

            <div id="costitem_list">
                CostItem List
            </div>
        </div>
    </div>

    <div id="assembly_list_panel">
        <div class="py-1">
            <div class="tree_search_area">
                <div class="my-auto mr-1">
                    <input class="form-control form-control-sm" name="a_search_tree_key" id="a_search_tree_key"
                        style="height: 36px;" placeholder="SEARCH E.G BRICKS">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 p-0 ss_search_tree_icon"
                    id="a_search_tree" title="Search">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm p-0 ss_clear_search_icon"
                    id="a_clear_search" title="Clear">
                </button>
            </div>
        </div>

        <div class="assembly_list_area">
            <div class="d-flex justify-content-around" style="padding-top: 1px; padding-bottom: 5px;">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_expand" title="Expand All"
                    onclick="$('#assembly_list').jstree('open_all');">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_collapse"
                    title="Collapse All" onclick="$('#assembly_list').jstree('close_all');">
                </button>
            </div>
            <div id="assembly_list">
                Interviews List
            </div>
        </div>
    </div>

    <div id="add_on_list_panel" style="display: none;">
        <div class="py-1">
            <div class="tree_search_area">
                <div class="my-auto mr-1">
                    <input class="form-control form-control-sm" name="add_on_search_tree_key"
                        id="add_on_search_tree_key" style="height: 36px;" placeholder="SEARCH E.G BRICKS">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 p-0 ss_search_tree_icon"
                    id="add_on_search_tree" title="Search">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm p-0 ss_clear_search_icon"
                    id="add_on_clear_search" title="Clear">
                </button>
            </div>
        </div>

        <div class="add_on_list_area">
            <div class="d-flex justify-content-around" style="padding-top: 1px; padding-bottom: 5px;">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_expand" title="Expand All"
                    onclick="$('#add_on_list').jstree('open_all');">
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 ss_tree_collapse"
                    title="Collapse All" onclick="$('#add_on_list').jstree('close_all');">
                </button>
            </div>
            <div id="add_on_list">
                Interviews List
            </div>
        </div>
    </div>

</nav>
{{-- </aside> --}}
