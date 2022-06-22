
<script>
    // add cost item to spreadsheet
    $('#add_on_list').on("select_node.jstree", function (e, data) {
        console.log('addon-clicked item')
        if (data.node.id.includes('addon')) {
            e.preventDefault();
            localStorage.removeItem('globalTOQ'); // remove globalTOQ
            let itemId = data.node.id.replace('addon-', '');
           
                addAddonToSS(itemId, projectId);
            
        }
    });



    function addAddonToSS(itemId, projectId) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/add_addon_to_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                item_id: itemId,
                project_id: projectId,
                sort_status: sortStatus
            },
            success: function (res) {
                someBlock.preloader('remove');
                $('#add_on_area').show();

                if ( res.data ) {
                    $('#add_on_area tbody').append("<tr class='addons-lightblue' id='eachaddon" + res.data.id + "'><td>"
                    +res.data.name + "</td><td>"
                    +res.data.category + "</td><td>"
                    +res.data.addonvalue + "</td><td>"
                    +res.data.method + "</td><td>"
                    +res.data.total + "</td><td class='text-center'>"
                    +"<img src='/icons/ss_delete.svg' class='width-20px cursor-pointer' onclick='handleDeleteAddon(" + res.data.id + ")' alt='delete'>" + "</td></tr>");

                    // let prev = $('#ss_total_bar').html();
                    // let afterIndex = prev.indexOf('ADDONS');
                    // let aftertext = prev.slice(0, afterIndex);
                    // let after = aftertext + "ADDONS - $" + res.data.compoundvalue;
                    // $('#ss_total_bar').html(after);

                    $('#ss_total_bar').html(res.data.totalestimate);

                }
                
                
                hideMarkupCols();
                updateHiddenCols();
                updateOverLine();
                updateSortStyle();
            }
        });
    }

    function handleDeleteAddon(addonid) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/delete_addon_to_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                addonid: addonid
            },
            success: function (res) {
                someBlock.preloader('remove');
                $('#add_on_area').show();

                if ( res.data ) {
                    $('#add_on_area tbody #eachaddon'+res.data.deletedid).remove();
                    if ( res.data.existkey == 0 ) {
                        $('#add_on_area').hide();
                    }

                    // let prev = $('#ss_total_bar').html();
                    // let afterIndex = prev.indexOf('ADDONS');
                    // let aftertext = prev.slice(0, afterIndex);
                    // let after = aftertext + "ADDONS - $" + res.data.compoundvalue;
                    // $('#ss_total_bar').html(after);

                    $('#ss_total_bar').html(res.data.totalestimate);

                }
                
                hideMarkupCols();
                updateHiddenCols();
                updateOverLine();
                updateSortStyle();
            }
        });
    }
</script>