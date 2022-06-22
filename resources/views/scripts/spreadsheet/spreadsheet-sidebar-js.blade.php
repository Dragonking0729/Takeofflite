<script>
    @if (!empty($page_info) && $page_info['project_name'])
        var projectName = "{{ $page_info['project_name'] }}";
        $('#dropdownMenuLink').html(projectName);
    @endif

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    $(document).ready(function() {
        var fullHeight = function() {

            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function() {
                $('.js-fullheight').css('height', $(window).height());
            });

        };
        fullHeight();

        // cost item sidebar data
        var costItemList = @json($costitem_tree_data);
        console.log('costItemList tree...', costItemList);
        $('#costitem_list').jstree({
            'core': {
                'data': costItemList
            },
            'search': {
                'show_only_matches': true,
            },
            types: {
                "child": {
                    "icon": "fa fa-plus"
                }
            },
            plugins: ["theme", "types", 'search']
        });

        // assembly item sidebar data
        var assemblyItemList = @json($assembly_tree_data);
        $('#assembly_list').jstree({
            'core': {
                'data': assemblyItemList
            },
            'search': {
                'show_only_matches': true,
            },
            types: {
                "child": {
                    "icon": "fa fa-plus"
                }
            },
            plugins: ["theme", "types", 'search']
        });

        // add on item sidebar data
        var addOnList = @json($add_ons_tree_data);
        $('#add_on_list').jstree({
            'core': {
                'data': addOnList
            },
            'search': {
                'show_only_matches': true,
            },
            types: {
                "child": {
                    "icon": "fa fa-plus"
                }
            },
            plugins: ["theme", "types", 'search']
        });


        // search & clear tree
        $(document).on('click', '#search_tree', function() {
            let key = $('#search_tree_key').val();
            $("#costitem_list").jstree(true).search(key);
        });
        $(document).on('click', '#clear_search', function() {
            $("#costitem_list").jstree(true).clear_search();
        });
        $(document).on('click', '#a_search_tree', function() {
            let key = $('#a_search_tree_key').val();
            $("#assembly_list").jstree(true).search(key);
        });
        $(document).on('click', '#a_clear_search', function() {
            $("#assembly_list").jstree(true).clear_search();
        });
        $(document).on('click', '#add_on_search_tree', function() {
            let key = $('#add_on_search_tree_key').val();
            $("#add_on_list").jstree(true).search(key);
        });
        $(document).on('click', '#add_on_clear_search', function() {
            $("#add_on_list").jstree(true).clear_search();
        });


        // switch left panel
        $('.assembly-takeoff-btn').click(function() {
            console.log('assembly take off btn clicked');
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-active');
            $('.item-takeoff-btn').removeClass('btn-active');
            $('.item-takeoff-btn').addClass('btn-outline-secondary');
            $('.add-on-takeoff-btn').removeClass('btn-active');
            $('.add-on-takeoff-btn').addClass('btn-outline-secondary');

            $('#costitem_list_panel').hide();
            $('#add_on_list_panel').hide();
            $('#assembly_list_panel').show();
        });

        $('.item-takeoff-btn').click(function() {
            console.log('item take off btn clicked');
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-active');
            $('.assembly-takeoff-btn').removeClass('btn-active');
            $('.assembly-takeoff-btn').addClass('btn-outline-secondary');
            $('.add-on-takeoff-btn').removeClass('btn-active');
            $('.add-on-takeoff-btn').addClass('btn-outline-secondary');

            $('#assembly_list_panel').hide();
            $('#add_on_list_panel').hide();
            $('#costitem_list_panel').show();
        });

        $('.add-on-takeoff-btn').click(function() {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-active');
            $('.assembly-takeoff-btn').removeClass('btn-active');
            $('.assembly-takeoff-btn').addClass('btn-outline-secondary');
            $('.item-takeoff-btn').removeClass('btn-active');
            $('.item-takeoff-btn').addClass('btn-outline-secondary');

            $('#assembly_list_panel').hide();
            $('#costitem_list_panel').hide();
            $('#add_on_list_panel').show();
        });


        // switch ask question toggle button
        $('.turn-on-interview').click(function() {
            let toggleStatus = $(this).data('checked');
            let toggleBtnOnPath = "{{ asset('icons/ss_toggle_on.svg') }}";
            let toggleBtnOffPath = "{{ asset('icons/ss_toggle_off.svg') }}";
            if (toggleStatus) {
                $(this).data('checked', false);
                $('.ss_toggle').css({
                    'background': 'url("/icons/ss_toggle_off.svg") no-repeat center center / 40px 40px',
                    'width': '38px',
                    'height': '38px'
                });
            } else {
                $(this).data('checked', true);
                $('.ss_toggle').css({
                    'background': 'url("/icons/ss_toggle_on.svg") no-repeat center center / 60px 60px',
                    'width': '38px',
                    'height': '38px'
                });
            }
        });

        // help ss video
        let obj = $("#help_ss_youtube");
        let configObject = {
            sourceUrl: obj.attr("data-videourl"),
            triggerElement: "#" + obj.attr("id"),
            progressCallback: function() {
                console.log("Callback Invoked.");
            }
        };

        let videoBuild = new YoutubeOverlayModule(configObject);
        videoBuild.activateDeployment();
    });
</script>
