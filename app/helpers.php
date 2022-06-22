<?php

/**
 * @param $data
 * @return array cost_group_tree data
 */
function get_cost_group_tree($data)
{
    $tree_data = [];
    $updated_costgroup = [];
    $opened = false;
    $folder_id = '';
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $page = 1;
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $temp_group_folder_desc = $item['cost_group_desc'] ? '-' . $item['cost_group_desc'] : '';
            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
            $group_folder_text = "{$item['cost_group_number']}{$group_folder_desc}";
            $group_page = $page;

            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $page++;
                    $temp_group_desc = $group['cost_group_desc'] ? '-' . $group['cost_group_desc'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['cost_group_number']}{$group_desc}";
                    $folder_children[] = [
//                            "id" => "group-{$group['id']}",
                        "id" => $page,
                        "text" => $group_text,
                        "icon" => "jstree-file"
                    ];
                }
            }

            $tree_data[] = [
//                    "id" => "folder-{$item['id']}",
                "id" => $group_page,
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
            $page++;
        }
    }

    return $tree_data;
}


/**
 * @param $all_costgroup , $all_costitems
 * @return array cost item tree data
 */
function get_cost_item_tree($all_costgroup, $all_costitems)
{
    $tree_data = [];
    $opened = false;
    $updated_costgroup = [];

    $folder_id = '';
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $updated_costitems = [];
    foreach ($all_costitems as $item) {
        $updated_costitems[$item['cost_group_number']][] = $item;
    }

    $page = 1;
    foreach ($all_costgroup as $item) {
        $temp_group_folder_desc = $item['cost_group_desc'] ? '-' . $item['cost_group_desc'] : '';
        $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
        $group_folder_text = "{$item['cost_group_number']}{$group_folder_desc}";
        if ($item['is_folder']) {
            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $temp_group_desc = $group['cost_group_desc'] ? '-' . $group['cost_group_desc'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['cost_group_number']}{$group_desc}";
                    if (array_key_exists($group['cost_group_number'], $updated_costitems)) { // item node
                        // get group children
                        $costitems = $updated_costitems[$group['cost_group_number']];
                        $group_children = [];
                        foreach ($costitems as $costitem) {
                            $temp_item_desc = $costitem['item_desc'] ? '-' . $costitem['item_desc'] : '';
                            $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                            $item_text = "{$costitem['item_number']}{$item_desc}";

                            $group_children[] = [
                                //                                    "id" => "costitem-{$costitem['id']}",
                                "id" => "costitem-{$page}",
                                "text" => $item_text,
                                "icon" => "jstree-file"
                            ];
                            $page++;
                        }
                        $folder_children[] = [
                            "id" => "costgroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                            "children" => $group_children
                        ];
                    } else {
                        $folder_children[] = [
                            "id" => "costgroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                        ];
                    }
                }
            }
            $tree_data[] = [
                "id" => "folder-{$item['id']}",
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
        }
    }

    return $tree_data;
}

/**
 * @param $all_costgroup
 * @param $all_costitems
 * @param $group_number
 * @param $item_number
 * @return int
 */
function get_cost_item_page($all_costgroup, $all_costitems, $group_number, $item_number)
{
    $updated_costgroup = [];
    $folder_id = '';
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $updated_costitems = [];
    foreach ($all_costitems as $item) {
        $updated_costitems[$item['cost_group_number']][] = $item;
    }

    $page = 1;
    $result = 1;
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    if (array_key_exists($group['cost_group_number'], $updated_costitems)) { // item node
                        $costitems = $updated_costitems[$group['cost_group_number']];
                        foreach ($costitems as $costitem) {
                            if ($costitem['item_number'] === $item_number && $group['cost_group_number'] === $group_number) {
                                $result = $page;
                            }
                            $page++;
                        }
                    }
                }
            }
        }
    }

    return $result;
}


/**
 * @param $data
 * @return array
 */
function get_proposal_group_tree($data)
{
    $tree_data = [];
    $updated_costgroup = [];
    $opened = false;
    $folder_id = '';
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $page = 1;
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $temp_group_folder_desc = $item['proposal_standard_item_group_description'] ? '-' . $item['proposal_standard_item_group_description'] : '';
            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
            $group_folder_text = "{$item['proposal_standard_item_group_number']}{$group_folder_desc}";
            $group_page = $page;

            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $page++;
                    $temp_group_desc = $group['proposal_standard_item_group_description'] ? '-' . $group['proposal_standard_item_group_description'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['proposal_standard_item_group_number']}{$group_desc}";
                    $folder_children[] = [
//                            "id" => "group-{$group['id']}",
                        "id" => $page,
                        "text" => $group_text,
                        "icon" => "jstree-file"
                    ];
                }
            }

            $tree_data[] = [
//                    "id" => "folder-{$item['id']}",
                "id" => $group_page,
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
            $page++;
        }
    }

    return $tree_data;
}


/**
 * @param $all_costgroup
 * @param $all_costitems
 * @return array
 */
function get_proposal_item_tree($all_costgroup, $all_costitems)
{
    $tree_data = [];
    $opened = false;
    $updated_costgroup = [];

    $folder_id = '';
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $updated_costitems = [];
    foreach ($all_costitems as $item) {
        $updated_costitems[$item['proposal_standard_item_group_number']][] = $item;
    }

    $page = 1;
    foreach ($all_costgroup as $item) {
        $temp_group_folder_desc = $item['proposal_standard_item_group_description'] ? '-' . $item['proposal_standard_item_group_description'] : '';
        $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
        $group_folder_text = "{$item['proposal_standard_item_group_number']}{$group_folder_desc}";
        if ($item['is_folder']) {
            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $temp_group_desc = $group['proposal_standard_item_group_description'] ? '-' . $group['proposal_standard_item_group_description'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['proposal_standard_item_group_number']}{$group_desc}";
                    if (array_key_exists($group['proposal_standard_item_group_number'], $updated_costitems)) { // item node
                        // get group children
                        $costitems = $updated_costitems[$group['proposal_standard_item_group_number']];
                        $group_children = [];
                        foreach ($costitems as $costitem) {
                            $temp_item_desc = $costitem['proposal_standard_item_description'] ? '-' . $costitem['proposal_standard_item_description'] : '';
                            $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                            $item_text = "{$costitem['proposal_standard_item_number']}{$item_desc}";

                            $group_children[] = [
                                //                                    "id" => "costitem-{$costitem['id']}",
                                "id" => "proposalitem-{$page}",
                                "text" => $item_text,
                                "icon" => "jstree-file"
                            ];
                            $page++;
                        }
                        $folder_children[] = [
                            "id" => "proposalgroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                            "children" => $group_children
                        ];
                    } else {
                        $folder_children[] = [
                            "id" => "proposalgroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                        ];
                    }
                }
            }
            $tree_data[] = [
                "id" => "folder-{$item['id']}",
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
        }
    }

    return $tree_data;
}


/**
 * @param $data
 * @return array
 */
function get_invoice_group_tree($data)
{
    $tree_data = [];
    $updated_costgroup = [];
    $opened = false;
    $folder_id = '';
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $page = 1;
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $temp_group_folder_desc = $item['invoice_standard_item_group_description'] ? '-' . $item['invoice_standard_item_group_description'] : '';
            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
            $group_folder_text = "{$item['invoice_standard_item_group_number']}{$group_folder_desc}";
            $group_page = $page;

            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $page++;
                    $temp_group_desc = $group['invoice_standard_item_group_description'] ? '-' . $group['invoice_standard_item_group_description'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['invoice_standard_item_group_number']}{$group_desc}";
                    $folder_children[] = [
//                            "id" => "group-{$group['id']}",
                        "id" => $page,
                        "text" => $group_text,
                        "icon" => "jstree-file"
                    ];
                }
            }

            $tree_data[] = [
//                    "id" => "folder-{$item['id']}",
                "id" => $group_page,
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
            $page++;
        }
    }

    return $tree_data;
}


/**
 * @param $all_costgroup
 * @param $all_costitems
 * @return array
 */
function get_invoice_item_tree($all_costgroup, $all_costitems)
{
    $tree_data = [];
    $opened = false;
    $updated_costgroup = [];

    $folder_id = '';
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $updated_costitems = [];
    foreach ($all_costitems as $item) {
        $updated_costitems[$item['invoice_standard_item_group_number']][] = $item;
    }

    $page = 1;
    foreach ($all_costgroup as $item) {
        $temp_group_folder_desc = $item['invoice_standard_item_group_description'] ? '-' . $item['invoice_standard_item_group_description'] : '';
        $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
        $group_folder_text = "{$item['invoice_standard_item_group_number']}{$group_folder_desc}";
        if ($item['is_folder']) {
            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $temp_group_desc = $group['invoice_standard_item_group_description'] ? '-' . $group['invoice_standard_item_group_description'] : '';
                    $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['invoice_standard_item_group_number']}{$group_desc}";
                    if (array_key_exists($group['invoice_standard_item_group_number'], $updated_costitems)) { // item node
                        // get group children
                        $costitems = $updated_costitems[$group['invoice_standard_item_group_number']];
                        $group_children = [];
                        foreach ($costitems as $costitem) {
                            $temp_item_desc = $costitem['invoice_standard_item_description'] ? '-' . $costitem['invoice_standard_item_description'] : '';
                            $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                            $item_text = "{$costitem['invoice_standard_item_number']}{$item_desc}";

                            $group_children[] = [
                                //                                    "id" => "costitem-{$costitem['id']}",
                                "id" => "invoiceitem-{$page}",
                                "text" => $item_text,
                                "icon" => "jstree-file"
                            ];
                            $page++;
                        }
                        $folder_children[] = [
                            "id" => "invoicegroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                            "children" => $group_children
                        ];
                    } else {
                        $folder_children[] = [
                            "id" => "invoicegroup-{$group['id']}",
                            "text" => $group_text,
                            "state" => array("opened" => $opened),
                        ];
                    }
                }
            }
            $tree_data[] = [
                "id" => "folder-{$item['id']}",
                "text" => $group_folder_text,
                "state" => array("opened" => $opened),
                "children" => $folder_children
            ];
        }
    }

    return $tree_data;
}


/**
 * @param $data
 * @return array assembly tree data
 */
function get_assembly_tree_data($data)
{
    $tree_data = [];
    $updated_data = [];
    $opened = false;
    $folder_id = '';
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_data[$folder_id][] = $item;
        } else {
            $updated_data[$folder_id][] = $item;
        }
    }

    $page = 1;
    foreach ($data as $item) {
        if ($item['is_folder']) {
            $desc = $item['assembly_desc'] ? '-' . $item['assembly_desc'] : '';
//                $desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_desc);
            $text = "{$item['assembly_number']}{$desc}";
            $group_page = $page;

            // get children
            $children = [];
            $groups = $updated_data[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $page++;
                    $child_desc = $group['assembly_desc'] ? '-' . $group['assembly_desc'] : '';
//                        $child_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_child_desc);
                    $child_text = "{$group['assembly_number']}{$child_desc}";
                    $children[] = [
//                            "id" => $group['id'],
                        "id" => $page,
                        "text" => $child_text,
                        "icon" => "jstree-file"
                    ];
                }
            }

            $tree_data[] = [
//                    "id" => $item['id'],
                "id" => $group_page,
                "text" => $text,
                "state" => array("opened" => $opened),
                "children" => $children
            ];
            $page++;
        }
    }

    return $tree_data;
}


/**
 * @param $all_costgroup , $all_costitems
 * @return array assembly cost item tree data
 */
function get_assembly_cost_item_tree($all_costgroup, $all_costitems)
{
    $tree_data = [];
    $folder_id = '';
    $opened = false;
    $initial_tree_state = array("opend" => $opened);
    $updated_costgroup = [];
    foreach ($all_costgroup as $item) {
        if ($item['is_folder']) {
            $folder_id = $item['id'];
            $updated_costgroup[$folder_id][] = $item;
        } else {
            $updated_costgroup[$folder_id][] = $item;
        }
    }

    $updated_costitems = [];
    foreach ($all_costitems as $item) {
        $updated_costitems[$item['cost_group_number']][] = $item;
    }
    foreach ($all_costgroup as $item) {
        $group_folder_desc = $item['cost_group_desc'] ? '-' . $item['cost_group_desc'] : '';
//            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
        $group_folder_text = "{$item['cost_group_number']}{$group_folder_desc}";
        if ($item['is_folder']) {
            // get folder children
            $folder_children = [];
            $groups = $updated_costgroup[$item['id']];
            foreach ($groups as $group) {
                if (empty($group['is_folder'])) {
                    $group_desc = $group['cost_group_desc'] ? '-' . $group['cost_group_desc'] : '';
//                        $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                    $group_text = "{$group['cost_group_number']}{$group_desc}";
                    if (array_key_exists($group['cost_group_number'], $updated_costitems)) { // item node
                        // get group children
                        $costitems = $updated_costitems[$group['cost_group_number']];
                        $group_children = [];
                        foreach ($costitems as $costitem) {
                            $item_desc = $costitem['item_desc'] ? '-' . $costitem['item_desc'] : '';
//                                $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                            $item_text = "{$costitem['item_number']}{$item_desc}";

                            $group_children[] = [
                                "id" => "costitem{$costitem['id']}-{$costitem['item_number']}",
                                "text" => $item_text,
                                "icon" => "jstree-file"
                            ];
                        }
                        $folder_children[] = [
                            "id" => "costgroup{$group['id']}-{$group['cost_group_number']}",
                            "text" => $group_text,
                            "state" => $initial_tree_state,
                            "children" => $group_children
                        ];
                    } else {
                        $folder_children[] = [
                            "id" => "costgroup{$group['id']}-{$group['cost_group_number']}",
                            "text" => $group_text,
                            "state" => $initial_tree_state,
                        ];
                    }
                }
            }
            $tree_data[] = [
                "id" => "folder-{$item['id']}",
                "text" => $group_folder_text,
                "state" => $initial_tree_state,
                "children" => $folder_children
            ];
        }
    }

    return $tree_data;
}

/**
 * @param $data
 * @return array questions_tree data
 */
function get_question_tree($data)
{
    $tree_data = [];
    $page = 1;
    foreach ($data as $item) {
        $tree_data[] = [
//                "id" => $item['id'],
            "id" => $page,
            "text" => $item['question'],
            "icon" => "jstree-file"
        ];
        $page++;
    }

    return $tree_data;
}

/**
 * @param $data
 * @return array questions_tree data
 */
function get_addon_tree($data)
{
    $tree_data = [];
    $page = 1;
    foreach ($data as $item) {
        $tree_data[] = [
//                "id" => $item['id'],
            "id" => $page,
            "text" => $item['addon_name'],
            "icon" => "jstree-file"
        ];
        $page++;
    }

    return $tree_data;
}


/**
 * @param $data
 * @return array
 */
function get_invoice_text_tree($data)
{
    $tree_data = [];
    $page = 1;
    foreach ($data as $item) {
        $tree_data[] = [
            "id" => $page,
            "text" => $item['title'],
            "icon" => "jstree-file"
        ];
        $page++;
    }

    return $tree_data;
}


/**
 * @param $data
 * @return array
 */
function get_proposal_text_tree($data)
{
    $tree_data = [];
    $page = 1;
    foreach ($data as $item) {
        $tree_data[] = [
            "id" => $page,
            "text" => $item['title'],
            "icon" => "jstree-file"
        ];
        $page++;
    }

    return $tree_data;
}


/**
 * @param $data
 * @return array stored formula tree data
 */
function get_stored_formula_tree($data)
{
    $tree_data = [];
    $page = 1;
    foreach ($data as $item) {
        $tree_data[] = [
            "id" => $page,
            "text" => $item['calculation_name'],
            "icon" => "jstree-file"
        ];
        $page++;
    }

    return $tree_data;
}

function genCustomerPortalLink($user_id, $project_id)
{
    $secret = env('CUSTOMER_SECRET_KEY');
    // Create the token header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);

    // Create the token payload
    $payload = json_encode([
        'type' => 'customer_portal',
        'user_id' => $user_id,
        'project_id' => $project_id,
    ]);

    // Encode Header
    $base64UrlHeader = base64url_encode($header);
    //dd($base64UrlHeader);
    // Encode Payload
    $base64UrlPayload = base64url_encode($payload);
    //dd($base64UrlPayload);
    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    //dd($signature);
    // Encode Signature to Base64Url String
    $base64UrlSignature = base64url_encode($signature);
    //dd($base64UrlSignature);
    // Create token
    $token = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    //dd($token);
    $url = env('APP_URL') . '/customer/token/' . $token;
    return ['url' => $url, 'token' => $token];
}

function base64url_encode($s)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($s));
}

function base64url_decode($s)
{
    return base64_decode(str_replace(['-', '_', ''], ['+', '/', '='], $s));
}

// get updated projects list
function get_project_list($privateProjects, $sharedProjects)
{
    $projects = [];
    foreach ($privateProjects as $privateProject) {
        $projects[] = [
            'id' => $privateProject->id,
            'projectName' => $privateProject->project_name,
            'isShared' => 0,
        ];
    }
    foreach ($sharedProjects as $sharedProject) {
        $projects[] = [
            'id' => $sharedProject->id,
            'projectName' => $sharedProject->project_name,
            'isShared' => 1,
        ];
    }
    return $projects;
}