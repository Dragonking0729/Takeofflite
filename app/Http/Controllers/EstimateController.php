<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Spreadsht;
use App\Models\ProjectSetting;
use App\Models\Uom;
use App\Models\User;
use App\Models\UserAddons;
use App\Models\UserProposalItem;
use App\Models\UserSSAddonList;
use App\Models\UserCostGroup;
use App\Models\UserCostItem;
use App\Models\UserInvoiceItem;
use App\Models\UserQuestion;
use App\Services\BlsApi;
use App\Services\GraingerApi;
use App\Services\WcywApi;
use App\Services\HomeDepotApi;
use App\Services\LowesApi;
use App\Services\WhitecapApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Exception;

class EstimateController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 'Estimate';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Estimate';
    }

    protected $FIELD_NAME = [
        'C' => 'ss_item_cost_group_number',
        'D' => 'ss_item_cost_group_desc',
        'E' => 'ss_item_number',
        'F' => 'ss_item_description',
        'G' => 'ss_item_takeoff_quantity',
        'H' => 'ss_item_takeoff_uom',
        'I' => 'ss_labor_conversion_factor',
        'J' => 'ss_labor_order_quantity',
        'K' => 'ss_labor_uom',
        'L' => 'ss_labor_price',
        'M' => 'ss_labor_total',
        'N' => 'ss_labor_markup_percent',
        'O' => 'ss_labor_markup_dollar_amount',
        'P' => 'ss_labor_total_markedup_total',
        'Q' => 'ss_material_conversion_factor',
        'R' => 'ss_material_order_quantity',
        'S' => 'ss_material_uom',
        'T' => 'ss_material_price',
        'U' => 'ss_material_total',
        'V' => 'ss_material_markup_percent',
        'W' => 'ss_material_markup_dollar_amount',
        'X' => 'ss_material_total_markedup_total',
        'Y' => 'ss_subcontract_conversion_factor',
        'Z' => 'ss_subcontract_order_quantity',
        'AA' => 'ss_subcontract_uom',
        'AB' => 'ss_subcontract_price',
        'AC' => 'ss_subcontract_total',
        'AD' => 'ss_subcontract_markup_percent',
        'AE' => 'ss_subcontract_markup_dollar_amount',
        'AF' => 'ss_subcontract_total_markedup_total',
        'AG' => 'ss_other_conversion_factor',
        'AH' => 'ss_other_order_quantity',
        'AI' => 'ss_other_uom',
        'AJ' => 'ss_other_price',
        'AK' => 'ss_other_total',
        'AL' => 'ss_other_markup_percent',
        'AM' => 'ss_other_markup_dollar_amount',
        'AN' => 'ss_other_total_markedup_total',
        'AO' => 'ss_home_depot_sku',
        'AP' => 'ss_home_depot_price',
        'AQ' => 'ss_lowes_sku',
        'AR' => 'ss_lowes_price',
        'AS' => 'ss_whitecap_sku',
        'AT' => 'ss_whitecap_price',
        'AU' => 'ss_bls_number',
        'AV' => 'ss_bls_price',
        'AW' => 'ss_grainger_number',
        'AX' => 'ss_grainger_price',
        'AY' => 'ss_wcyw_number',
        'AZ' => 'ss_wcyw_price',
        'BA' => 'ss_selected_vendor',
        'BB' => 'ss_quote_or_invoice_item',
        'BC' => 'ss_line_total',
        'BD' => 'ss_location',
        'BE' => 'ss_notes'
    ];

    private function get_ss_data($projectId, $sort = 'default')
    {
        if ($sort === 'default') {
            $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)
                ->orderBy('ss_item_cost_group_number')->orderBy('ss_item_number')->get()->toArray();
        } else {
            $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)
                ->orderBy($sort)->get()->toArray();
        }
        $updated = [];
        foreach ($data as $row) {
            if ($sort !== 'default') {
                $updated[$row[$sort]][] = $row;
            }
        }
        $ss_data = [];
        $over_line_array = [];
        $index = 1;
        foreach ($data as $row) {
            if ($sort !== 'default') {
                $sort_val = $row[$sort];
                if (!in_array($sort_val, $over_line_array)) {
                    array_push($over_line_array, $sort_val);
                    $index++;
                    $labor_total = 0;
                    $material_total = 0;
                    $subcontract_total = 0;
                    $other_total = 0;
                    for ($i = 0; $i < count($updated[$sort_val]); $i++) {
                        if ($updated[$sort_val][$i]['ss_labor_conversion_factor'] == '0') {
                            $updated[$sort_val][$i]['ss_labor_conversion_factor'] = 1;
                        }
                        if ($updated[$sort_val][$i]['ss_material_conversion_factor'] == '0') {
                            $updated[$sort_val][$i]['ss_material_conversion_factor'] = 1;
                        }
                        if ($updated[$sort_val][$i]['ss_subcontract_conversion_factor'] == '0') {
                            $updated[$sort_val][$i]['ss_subcontract_conversion_factor'] = 1;
                        }
                        if ($updated[$sort_val][$i]['ss_other_conversion_factor'] == '0') {
                            $updated[$sort_val][$i]['ss_other_conversion_factor'] = 1;
                        }
                        $labor_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_labor_conversion_factor'] * $updated[$sort_val][$i]['ss_labor_price'], 2);
                        $material_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_material_conversion_factor'] * $updated[$sort_val][$i]['ss_material_price'], 2);
                        $subcontract_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_subcontract_conversion_factor'] * $updated[$sort_val][$i]['ss_subcontract_price'], 2);
                        $other_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_other_conversion_factor'] * $updated[$sort_val][$i]['ss_other_price'], 2);
                    }
                    $total_line = $labor_total + $material_total + $subcontract_total + $other_total;
                    if ($sort === 'ss_item_cost_group_number') {
                        $ss_data[] = [
                            'over_line ' . $sort, '', $sort_val, $row['ss_item_cost_group_desc'], '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', $total_line, '', ''
                        ];
                    } else if ($sort === 'ss_location') {
                        if (!$sort_val)
                            $sort_val = 'Location Not Assigned';
                        $ss_data[] = [
                            'over_line ' . $sort, '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',  $total_line, $sort_val, ''
                        ];
                    } else if ($sort === 'ss_selected_vendor') {
                        if (!$sort_val)
                            $sort_val = 'No Selected Vendor';
                        $ss_data[] = [
                            'over_line ' . $sort, '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $sort_val, '', $total_line, '', ''
                        ];
                    } else if ($sort === 'ss_quote_or_invoice_item') {
                        if (!$sort_val)
                            $sort_val = 'No Quote/Invoice';
                        $ss_data[] = [
                            'over_line ' . $sort, '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $sort_val, $total_line, '', ''
                        ];
                    }
                }
            }

            $use_labor = $row['ss_use_labor'];
            $use_material = $row['ss_use_material'];
            $use_sub = $row['ss_use_sub'];
            if ($sort === 'ss_item_cost_group_number') {
                $ss_data[] = [
                    $row['id'], '', '', $row['ss_item_cost_group_desc'],
                    $row['ss_item_number'], $row['ss_item_description'],
                    $row['ss_item_takeoff_quantity'], // G : TOQ
                    $row['ss_item_takeoff_uom'],

                    $use_labor ? $row['ss_labor_conversion_factor'] : '', // I
                    $use_labor ? "=ROUND(G" . $index . "/I" . $index . ", 2)" : '', // J : LOQ
                    $use_labor ? $row['ss_labor_uom'] : '', $use_labor ? $row['ss_labor_price'] : '',
                    $use_labor ? "=ROUND(J" . $index . "*L" . $index . ", 2)" : '', // M : Labor total
                    $use_labor ? $row['ss_labor_markup_percent'] : '',
                    $use_labor ? $row['ss_labor_markup_dollar_amount'] : '', $use_labor ? $row['ss_labor_total_markedup_total'] : '',

                    $use_material ? $row['ss_material_conversion_factor'] : '', // Q
                    $use_material ? "=ROUND(G" . $index . "/Q" . $index . ", 2)" : '', // R : MOQ
                    $use_material ? $row['ss_material_uom'] : '', $use_material ? $row['ss_material_price'] : '',
                    $use_material ? "=ROUND(R" . $index . "*T" . $index . ", 2)" : '', // U : Material total
                    $use_material ? $row['ss_material_markup_percent'] : '',
                    $use_material ? $row['ss_material_markup_dollar_amount'] : '', $use_material ? $row['ss_material_total_markedup_total'] : '',

                    $use_sub ? $row['ss_subcontract_conversion_factor'] : '', // Y
                    $use_sub ? "=ROUND(G" . $index . "/Y" . $index . ", 2)" : '', // Z : SOQ
                    $use_sub ? $row['ss_subcontract_uom'] : '', $use_sub ? $row['ss_subcontract_price'] : '',
                    $use_sub ? "=ROUND(Z" . $index . "*AB" . $index . ", 2)" : '', // AC : Subcontract total
                    $use_sub ? $row['ss_subcontract_markup_percent'] : '',
                    $use_sub ? $row['ss_subcontract_markup_dollar_amount'] : '', $use_sub ? $row['ss_subcontract_total_markedup_total'] : '',

                    $row['ss_other_conversion_factor'], // AG
                    "=ROUND(G" . $index . "/AG" . $index . ", 2)", // AH : OOQ
                    $row['ss_other_uom'], $row['ss_other_price'],
                    "=ROUND(AH" . $index . "*AJ" . $index . ", 2)", // AK : Other total
                    $row['ss_other_markup_percent'],
                    $row['ss_other_markup_dollar_amount'], $row['ss_other_total_markedup_total'],

                    $row['ss_home_depot_sku'], $row['ss_home_depot_price'],
                    $row['ss_lowes_sku'], $row['ss_lowes_price'],
                    $row['ss_whitecap_sku'], $row['ss_whitecap_price'],
                    $row['ss_bls_number'], $row['ss_bls_price'],
                    $row['ss_grainger_number'], $row['ss_grainger_price'],
                    $row['ss_wcyw_number'], $row['ss_wcyw_price'],
                    $row['ss_selected_vendor'], $row['ss_quote_or_invoice_item'],
                    "=ROUND(M" . $index . "+U" . $index . "+AC" . $index . ", 2)", // line total: AU = M + U + AC + AK
                    $row['ss_location'], $row['ss_notes']
                ];
            } else if ($sort === 'ss_location') {
                $ss_data[] = [
                    $row['id'], '', $row['ss_item_cost_group_number'], $row['ss_item_cost_group_desc'],
                    $row['ss_item_number'], $row['ss_item_description'],
                    $row['ss_item_takeoff_quantity'], // G : TOQ
                    $row['ss_item_takeoff_uom'],

                    $use_labor ? $row['ss_labor_conversion_factor'] : '', // I
                    $use_labor ? "=ROUND(G" . $index . "/I" . $index . ", 2)" : '', // J : LOQ
                    $use_labor ? $row['ss_labor_uom'] : '', $use_labor ? $row['ss_labor_price'] : '',
                    $use_labor ? "=ROUND(J" . $index . "*L" . $index . ", 2)" : '', // M : Labor total
                    $use_labor ? $row['ss_labor_markup_percent'] : '',
                    $use_labor ? $row['ss_labor_markup_dollar_amount'] : '', $use_labor ? $row['ss_labor_total_markedup_total'] : '',

                    $use_material ? $row['ss_material_conversion_factor'] : '', // Q
                    $use_material ? "=ROUND(G" . $index . "/Q" . $index . ", 2)" : '', // R : MOQ
                    $use_material ? $row['ss_material_uom'] : '', $use_material ? $row['ss_material_price'] : '',
                    $use_material ? "=ROUND(R" . $index . "*T" . $index . ", 2)" : '', // U : Material total
                    $use_material ? $row['ss_material_markup_percent'] : '',
                    $use_material ? $row['ss_material_markup_dollar_amount'] : '', $use_material ? $row['ss_material_total_markedup_total'] : '',

                    $use_sub ? $row['ss_subcontract_conversion_factor'] : '', // Y
                    $use_sub ? "=ROUND(G" . $index . "/Y" . $index . ", 2)" : '', // Z : SOQ
                    $use_sub ? $row['ss_subcontract_uom'] : '', $use_sub ? $row['ss_subcontract_price'] : '',
                    $use_sub ? "=ROUND(Z" . $index . "*AB" . $index . ", 2)" : '', // AC : Subcontract total
                    $use_sub ? $row['ss_subcontract_markup_percent'] : '',
                    $use_sub ? $row['ss_subcontract_markup_dollar_amount'] : '', $use_sub ? $row['ss_subcontract_total_markedup_total'] : '',

                    $row['ss_other_conversion_factor'], // AG
                    "=ROUND(G" . $index . "/AG" . $index . ", 2)", // AH : OOQ
                    $row['ss_other_uom'], $row['ss_other_price'],
                    "=ROUND(AH" . $index . "*AJ" . $index . ", 2)", // AK : Other total
                    $row['ss_other_markup_percent'],
                    $row['ss_other_markup_dollar_amount'], $row['ss_other_total_markedup_total'],

                    $row['ss_home_depot_sku'], $row['ss_home_depot_price'],
                    $row['ss_lowes_sku'], $row['ss_lowes_price'],
                    $row['ss_whitecap_sku'], $row['ss_whitecap_price'],
                    $row['ss_bls_number'], $row['ss_bls_price'],
                    $row['ss_grainger_number'], $row['ss_grainger_price'],
                    $row['ss_wcyw_number'], $row['ss_wcyw_price'],
                    $row['ss_selected_vendor'], $row['ss_quote_or_invoice_item'],
                    "=ROUND(M" . $index . "+U" . $index . "+AC" . $index . ", 2)", // line total: AU = M + U + AC + AK
                    '', $row['ss_notes']
                ];
            } else if ($sort === 'ss_selected_vendor') {
                $ss_data[] = [
                    $row['id'], '', $row['ss_item_cost_group_number'], $row['ss_item_cost_group_desc'],
                    $row['ss_item_number'], $row['ss_item_description'],
                    $row['ss_item_takeoff_quantity'], // G : TOQ
                    $row['ss_item_takeoff_uom'],

                    $use_labor ? $row['ss_labor_conversion_factor'] : '', // I
                    $use_labor ? "=ROUND(G" . $index . "/I" . $index . ", 2)" : '', // J : LOQ
                    $use_labor ? $row['ss_labor_uom'] : '', $use_labor ? $row['ss_labor_price'] : '',
                    $use_labor ? "=ROUND(J" . $index . "*L" . $index . ", 2)" : '', // M : Labor total
                    $use_labor ? $row['ss_labor_markup_percent'] : '',
                    $use_labor ? $row['ss_labor_markup_dollar_amount'] : '', $use_labor ? $row['ss_labor_total_markedup_total'] : '',

                    $use_material ? $row['ss_material_conversion_factor'] : '', // Q
                    $use_material ? "=ROUND(G" . $index . "/Q" . $index . ", 2)" : '', // R : MOQ
                    $use_material ? $row['ss_material_uom'] : '', $use_material ? $row['ss_material_price'] : '',
                    $use_material ? "=ROUND(R" . $index . "*T" . $index . ", 2)" : '', // U : Material total
                    $use_material ? $row['ss_material_markup_percent'] : '',
                    $use_material ? $row['ss_material_markup_dollar_amount'] : '', $use_material ? $row['ss_material_total_markedup_total'] : '',

                    $use_sub ? $row['ss_subcontract_conversion_factor'] : '', // Y
                    $use_sub ? "=ROUND(G" . $index . "/Y" . $index . ", 2)" : '', // Z : SOQ
                    $use_sub ? $row['ss_subcontract_uom'] : '', $use_sub ? $row['ss_subcontract_price'] : '',
                    $use_sub ? "=ROUND(Z" . $index . "*AB" . $index . ", 2)" : '', // AC : Subcontract total
                    $use_sub ? $row['ss_subcontract_markup_percent'] : '',
                    $use_sub ? $row['ss_subcontract_markup_dollar_amount'] : '', $use_sub ? $row['ss_subcontract_total_markedup_total'] : '',

                    $row['ss_other_conversion_factor'], // AG
                    "=ROUND(G" . $index . "/AG" . $index . ", 2)", // AH : OOQ
                    $row['ss_other_uom'], $row['ss_other_price'],
                    "=ROUND(AH" . $index . "*AJ" . $index . ", 2)", // AK : Other total
                    $row['ss_other_markup_percent'],
                    $row['ss_other_markup_dollar_amount'], $row['ss_other_total_markedup_total'],

                    $row['ss_home_depot_sku'], $row['ss_home_depot_price'],
                    $row['ss_lowes_sku'], $row['ss_lowes_price'],
                    $row['ss_whitecap_sku'], $row['ss_whitecap_price'],
                    $row['ss_bls_number'], $row['ss_bls_price'],
                    $row['ss_grainger_number'], $row['ss_grainger_price'],
                    $row['ss_wcyw_number'], $row['ss_wcyw_price'],
                    '', $row['ss_quote_or_invoice_item'],
                    "=ROUND(M" . $index . "+U" . $index . "+AC" . $index . ", 2)", // line total: AU = M + U + AC + AK
                    $row['ss_location'], $row['ss_notes']
                ];
            } else if ($sort === 'ss_quote_or_invoice_item') {
                $ss_data[] = [
                    $row['id'], '', $row['ss_item_cost_group_number'], $row['ss_item_cost_group_desc'],
                    $row['ss_item_number'], $row['ss_item_description'],
                    $row['ss_item_takeoff_quantity'], // G : TOQ
                    $row['ss_item_takeoff_uom'],

                    $use_labor ? $row['ss_labor_conversion_factor'] : '', // I
                    $use_labor ? "=ROUND(G" . $index . "/I" . $index . ", 2)" : '', // J : LOQ
                    $use_labor ? $row['ss_labor_uom'] : '', $use_labor ? $row['ss_labor_price'] : '',
                    $use_labor ? "=ROUND(J" . $index . "*L" . $index . ", 2)" : '', // M : Labor total
                    $use_labor ? $row['ss_labor_markup_percent'] : '',
                    $use_labor ? $row['ss_labor_markup_dollar_amount'] : '', $use_labor ? $row['ss_labor_total_markedup_total'] : '',

                    $use_material ? $row['ss_material_conversion_factor'] : '', // Q
                    $use_material ? "=ROUND(G" . $index . "/Q" . $index . ", 2)" : '', // R : MOQ
                    $use_material ? $row['ss_material_uom'] : '', $use_material ? $row['ss_material_price'] : '',
                    $use_material ? "=ROUND(R" . $index . "*T" . $index . ", 2)" : '', // U : Material total
                    $use_material ? $row['ss_material_markup_percent'] : '',
                    $use_material ? $row['ss_material_markup_dollar_amount'] : '', $use_material ? $row['ss_material_total_markedup_total'] : '',

                    $use_sub ? $row['ss_subcontract_conversion_factor'] : '', // Y
                    $use_sub ? "=ROUND(G" . $index . "/Y" . $index . ", 2)" : '', // Z : SOQ
                    $use_sub ? $row['ss_subcontract_uom'] : '', $use_sub ? $row['ss_subcontract_price'] : '',
                    $use_sub ? "=ROUND(Z" . $index . "*AB" . $index . ", 2)" : '', // AC : Subcontract total
                    $use_sub ? $row['ss_subcontract_markup_percent'] : '',
                    $use_sub ? $row['ss_subcontract_markup_dollar_amount'] : '', $use_sub ? $row['ss_subcontract_total_markedup_total'] : '',

                    $row['ss_other_conversion_factor'], // AG
                    "=ROUND(G" . $index . "/AG" . $index . ", 2)", // AH : OOQ
                    $row['ss_other_uom'], $row['ss_other_price'],
                    "=ROUND(AH" . $index . "*AJ" . $index . ", 2)", // AK : Other total
                    $row['ss_other_markup_percent'],
                    $row['ss_other_markup_dollar_amount'], $row['ss_other_total_markedup_total'],

                    $row['ss_home_depot_sku'], $row['ss_home_depot_price'],
                    $row['ss_lowes_sku'], $row['ss_lowes_price'],
                    $row['ss_whitecap_sku'], $row['ss_whitecap_price'],
                    $row['ss_bls_number'], $row['ss_bls_price'],
                    $row['ss_grainger_number'], $row['ss_grainger_price'],
                    $row['ss_wcyw_number'], $row['ss_wcyw_price'],
                    $row['ss_selected_vendor'], '',
                    "=ROUND(M" . $index . "+U" . $index . "+AC" . $index . ", 2)", // line total: AU = M + U + AC + AK
                    $row['ss_location'], $row['ss_notes']
                ];
            } else {
                $ss_data[] = [
                    $row['id'], '', $row['ss_item_cost_group_number'], $row['ss_item_cost_group_desc'],
                    $row['ss_item_number'], $row['ss_item_description'],
                    $row['ss_item_takeoff_quantity'], // G : TOQ
                    $row['ss_item_takeoff_uom'],

                    $use_labor ? $row['ss_labor_conversion_factor'] : '', // I
                    $use_labor ? "=ROUND(G" . $index . "/I" . $index . ", 2)" : '', // J : LOQ
                    $use_labor ? $row['ss_labor_uom'] : '', $use_labor ? $row['ss_labor_price'] : '',
                    $use_labor ? "=ROUND(J" . $index . "*L" . $index . ", 2)" : '', // M : Labor total
                    $use_labor ? $row['ss_labor_markup_percent'] : '',
                    $use_labor ? $row['ss_labor_markup_dollar_amount'] : '', $use_labor ? $row['ss_labor_total_markedup_total'] : '',

                    $use_material ? $row['ss_material_conversion_factor'] : '', // Q
                    $use_material ? "=ROUND(G" . $index . "/Q" . $index . ", 2)" : '', // R : MOQ
                    $use_material ? $row['ss_material_uom'] : '', $use_material ? $row['ss_material_price'] : '',
                    $use_material ? "=ROUND(R" . $index . "*T" . $index . ", 2)" : '', // U : Material total
                    $use_material ? $row['ss_material_markup_percent'] : '',
                    $use_material ? $row['ss_material_markup_dollar_amount'] : '', $use_material ? $row['ss_material_total_markedup_total'] : '',

                    $use_sub ? $row['ss_subcontract_conversion_factor'] : '', // Y
                    $use_sub ? "=ROUND(G" . $index . "/Y" . $index . ", 2)" : '', // Z : SOQ
                    $use_sub ? $row['ss_subcontract_uom'] : '', $use_sub ? $row['ss_subcontract_price'] : '',
                    $use_sub ? "=ROUND(Z" . $index . "*AB" . $index . ", 2)" : '', // AC : Subcontract total
                    $use_sub ? $row['ss_subcontract_markup_percent'] : '',
                    $use_sub ? $row['ss_subcontract_markup_dollar_amount'] : '', $use_sub ? $row['ss_subcontract_total_markedup_total'] : '',

                    $row['ss_other_conversion_factor'], // AG
                    "=ROUND(G" . $index . "/AG" . $index . ", 2)", // AH : OOQ
                    $row['ss_other_uom'], $row['ss_other_price'],
                    "=ROUND(AH" . $index . "*AJ" . $index . ", 2)", // AK : Other total
                    $row['ss_other_markup_percent'],
                    $row['ss_other_markup_dollar_amount'], $row['ss_other_total_markedup_total'],

                    $row['ss_home_depot_sku'], $row['ss_home_depot_price'],
                    $row['ss_lowes_sku'], $row['ss_lowes_price'],
                    $row['ss_whitecap_sku'], $row['ss_whitecap_price'],
                    $row['ss_bls_number'], $row['ss_bls_price'],
                    $row['ss_grainger_number'], $row['ss_grainger_price'],
                    $row['ss_wcyw_number'], $row['ss_wcyw_price'],
                    $row['ss_selected_vendor'], $row['ss_quote_or_invoice_item'],
                    "=ROUND(M" . $index . "+U" . $index . "+AC" . $index . ", 2)", // line total: AU = M + U + AC + AK
                    $row['ss_location'], $row['ss_notes']
                ];
            }
            $index++;
        }

        return $ss_data;
    }

    private function get_total($projectId)
    {
        $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)->get()->toArray();
        $total_labor = 0;
        $total_material = 0;
        $total_subcontract = 0;
        $total_other = 0;
        foreach ($data as $row) {
            $use_labor = $row['ss_use_labor'];
            $use_material = $row['ss_use_material'];
            $use_sub = $row['ss_use_sub'];

            if ($use_labor)
                $total_labor += Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
            if ($use_material)
                $total_material += Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
            if ($use_sub)
                $total_subcontract += Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
            // $total_other += Round($row['ss_item_takeoff_quantity'] / $row['ss_other_conversion_factor'] * $row['ss_other_price'], 2);
        }

        // calculate the total addons
        $existAddonLists = UserSSAddonList::where('project_id', $projectId)->where('user_id', $this->user_id)->get();
        $total_addon = 0;
        foreach ($existAddonLists as $addon) {
            $total_addon += $addon->ss_add_on_value;
        }

        $total_estimate = $total_labor + $total_material + $total_subcontract + $total_addon;
        $totals = 'TOTAL ESTIMATE - $' . $total_estimate . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;LABOR - $' . $total_labor . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;MATERIAL - $' . $total_material .
            '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;SUBCONTRACTOR - $' . $total_subcontract . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;ADDONS - $' . $total_addon;

        return $totals;
    }

    // get sum of category (Labor, Material, Subcontract, Estimate Total)
    private function get_category_total($projectId, $category)
    {
        $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)->get()->toArray();
        $total_labor = 0;
        $total_material = 0;
        $total_subcontract = 0;
        $total_other = 0;
        foreach ($data as $row) {
            $use_labor = $row['ss_use_labor'];
            $use_material = $row['ss_use_material'];
            $use_sub = $row['ss_use_sub'];

            if ($use_labor)
                $total_labor += Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
            if ($use_material)
                $total_material += Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
            if ($use_sub)
                $total_subcontract += Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
        }
        $total_estimate = $total_labor + $total_material + $total_subcontract + $total_other;

        switch ($category) {
            case 'Labor':
                return $total_labor;
            case 'Material':
                return $total_material;
            case 'Subcontract':
                return $total_subcontract;
            case 'Estimate Total':
                return $total_estimate;
            default:
                return 0;
        }
    }

    // get price comment
    private function get_price_comments($projectId, $sort = 'default')
    {
        $comments = [];
        $group_array = [];
        $index = 1;
        $updated = [];
        if ($sort === 'default') {
            $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)
                ->orderBy('ss_item_cost_group_number')->orderBy('ss_item_number')->get()->toArray();
        } else {
            $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $projectId)
                ->orderBy($sort)->get()->toArray();
        }
        foreach ($data as $row) {
            if ($sort !== 'default') {
                $group = $row[$sort];
                $updated[$group][] = $row;
            }
        }
        foreach ($data as $row) {
            if ($sort !== 'default') {
                $group = $row[$sort];
                if (!in_array($group, $group_array)) {
                    $index++;
                    array_push($group_array, $group);
                }
            }
            if (!empty($row['ss_price_info'])) {
                if (!empty($row['ss_bls_number']) && !empty($row['ss_bls_price'])) {
                    $comments['L' . $index] = $row['ss_price_info']; // L - Labor Price
                } else {
                    $comments['T' . $index] = $row['ss_price_info']; // T - Material price
                }
            }
            $index++;
        }
        return $comments;
    }

    // get costgroup > costitem tree
    private function buildCostitemTree($all_costgroup, $all_costitems)
    {
        $tree_data = [];
        $opened = false;
        $updated_costgroup = [];
        $initial_tree_state = array("opened" => $opened);
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
                                    "id" => "costitem-{$costitem['id']}",
                                    "text" => $item_text,
                                    "type" => "child"
                                ];
                            }
                            $folder_children[] = [
                                "id" => "costgroup-{$group['id']}",
                                "text" => $group_text,
                                "state" => $initial_tree_state,
                                "children" => $group_children
                            ];
                        } else {
                            $folder_children[] = [
                                "id" => "costgroup-{$group['id']}",
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

    // get assembly tree
    private function buildAssemblyTree($data)
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

        foreach ($data as $item) {
            if ($item['is_folder']) {
                $desc = $item['assembly_desc'] ? '-' . $item['assembly_desc'] : '';
                //                $desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_desc);
                $text = "{$item['assembly_number']}{$desc}";

                // get children
                $children = [];
                $groups = $updated_data[$item['id']];
                foreach ($groups as $group) {
                    if (empty($group['is_folder'])) {
                        $child_desc = $group['assembly_desc'] ? '-' . $group['assembly_desc'] : '';
                        //                        $child_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_child_desc);
                        $child_text = "{$group['assembly_number']}{$child_desc}";
                        $children[] = [
                            "id" => $group['id'],
                            "text" => $child_text,
                            "type" => "child"
                        ];
                    }
                }

                $tree_data[] = [
                    "id" => $item['id'],
                    "text" => $text,
                    "state" => array("opened" => $opened),
                    "children" => $children
                ];
            }
        }


        return $tree_data;
    }

    public function buildAddonTree($data)
    {
        $tree_data = [];
        $page = 1;
        foreach ($data as $item) {
            $tree_data[] = [
                //                "id" => $item['id'],
                "id" => "addon-" . $item['id'],
                "text" => $item['addon_name'],
                "icon" => "jstree-file"
            ];
            $page++;
        }

        return $tree_data;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('dashboard');
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $project_name = Project::find($id)->project_name;
        $page_info = array(
            'project_id' => $id,
            'project_name' => $project_name,
            'name' => $this->page_name
        );

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = $this->buildCostitemTree($all_costgroup, $all_costitems);

        // get assembly item tree
        $all_assembly_array = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = $this->buildAssemblyTree($all_assembly_array);

        // get add-ons item tree
        $all_add_ons_array = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->get()->toArray();
        $add_ons_tree_data = $this->buildAddonTree($all_add_ons_array);

        // get spread sheet data
        $ss_data = $this->get_ss_data($id, 'default');
        $price_comments = $this->get_price_comments($id, 'default');
        $total = $this->get_total($id);

        // get uom
        $uom = [];
        $uom_array = Uom::orderBy('uom_name')->get();
        foreach ($uom_array as $item) {
            array_push($uom, $item->uom_name);
        }

        // get quote/invoice item dropdown list
        $proposal_items_list = ["", "No Quote/Invoice"];
        $proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_number'))->get();
        foreach ($proposal_items as $item) {
            $proposal_item = $item->proposal_standard_item_number . ' ' . $item->proposal_standard_item_description;
            $proposal_items_list[] = $proposal_item;
        }

        // get spreadsheet setting
        $ss_setting = '';
        $is_sidebar_open = 1;
        $ss_setting_count = ProjectSetting::where('user_id', $this->user_id)->where('project_id', $id)->where('page_name', $this->page_name)->get();
        if (count($ss_setting_count)) {
            $ss_setting = $ss_setting_count[0]->setting;
            $is_sidebar_open = $ss_setting_count[0]->is_sidebar_open;
        }

        // show initially the addon lists
        $existAddonLists = UserSSAddonList::where('user_id', $this->user_id)->where('project_id', $id)->get();


        return view(
            'estimate.index',
            compact(
                'projects',
                'page_info',
                'costitem_tree_data',
                'assembly_tree_data',
                'add_ons_tree_data',
                'proposal_items_list',
                'ss_data',
                'total',
                'uom',
                'ss_setting',
                'price_comments',
                'is_sidebar_open',
                'existAddonLists'
            )
        );
    }


    // add add-ons to SS
    public function add_addon_to_ss(Request $request)
    {
        $id = $request->item_id;
        $project_id = $request->project_id;
        $addon = UserAddons::find($id);
        if (!$addon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        } else {
            $category = $addon->addon_category;
            $method = $addon->addon_method;
            $value = $addon->addon_value;
            $name = $addon->addon_name;
            $category_total = $this->get_category_total($project_id, $category);
            if ($method == '%') {
                $temp_param = (float)$value / 100;
                $temp_value = round((float)$category_total * $temp_param);
            } else {
                $temp_param = (float)$value / 100;
                $temp_value = round((float)$category_total * $temp_param);
            }
            // save add-on row to db ss_add_on_list table
            $existAddonList = UserSSAddonList::where('user_id', $addon->user_id)
                ->where('project_id', $project_id)
                ->where('ss_add_on_name', $name)
                ->where('ss_add_on_value', $temp_value)
                ->first();

            $totalAddonValues = 0;
            if ($existAddonList) // already existed case
            {

                return response()->json([
                    'status' => 'success',
                    'message' => 'addon added successfully',
                    'data' => null
                ]);
            } else {

                $newAddonlist = new UserSSAddonList();
                $newAddonlist->user_id = $addon->user_id;
                $newAddonlist->project_id = $project_id;
                $newAddonlist->ss_add_on_name = $name;
                $newAddonlist->ss_add_on_value = $temp_value;
                $newAddonlist->addon_category = $category;
                $newAddonlist->addon_method = $method;
                $newAddonlist->addon_value = $value;
                $newAddonlist->save();

                $getAllUserAddonValues = UserSSAddonList::where('user_id', $addon->user_id)
                    ->where('project_id', $project_id)->get();


                foreach ($getAllUserAddonValues as $each) {
                    $totalAddonValues += $each->ss_add_on_value;
                }

                $getTotalEstimate = $this->get_total($project_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'addon added successfully',
                    'data' => [
                        'id' => $newAddonlist->id,
                        'name' => $name,
                        'total' => $temp_value,
                        'action' => 'Delete',
                        'compoundvalue' => $totalAddonValues,
                        'category' => $category,
                        'method' => $method,
                        'addonvalue' => $value,
                        'totalestimate' => $getTotalEstimate
                    ]
                ]);
            }
        }
    }

    public function update_addon_to_ss(Request $request)
    {

        $project_id = $request->project_id;
        $existAddonLists = UserSSAddonList::where('user_id', $this->user_id)
            ->where('project_id', $project_id)->get();
        $category_total1 = $this->get_category_total($project_id, 'Labor');
        $category_total2 = $this->get_category_total($project_id, 'Material');
        $category_total3 = $this->get_category_total($project_id, 'Subcontract');
        $category_total4 = $this->get_category_total($project_id, 'Estimate Total');

        foreach ($existAddonLists as $ea) {
            $method = $ea->addon_method;
            $category = $ea->addon_category;
            $value = $ea->addon_value;
            switch ($category) {
                case 'Labor':
                    if ($method == '%') {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total1 * $temp_param);
                    } else {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total1 * $temp_param);
                    }

                    $ea->ss_add_on_value = $temp_value;
                    $ea->save();

                    break;
                case 'Material':
                    if ($method == '%') {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total2 * $temp_param);
                    } else {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total2 * $temp_param);
                    }
                    $ea->ss_add_on_value = $temp_value;
                    $ea->save();

                    break;
                case 'Subcontract':
                    if ($method == '%') {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total3 * $temp_param);
                    } else {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total3 * $temp_param);
                    }
                    $ea->ss_add_on_value = $temp_value;
                    $ea->save();

                    break;
                case 'Estimate Total':
                    if ($method == '%') {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total4 * $temp_param);
                    } else {
                        $temp_param = (float)$value / 100;
                        $temp_value = round((float)$category_total4 * $temp_param);
                    }
                    $ea->ss_add_on_value = $temp_value;
                    $ea->save();

                    break;
                default:
                    break;
            }
        }

        $totalAddonValue = 0;
        $allAddonListsData = UserSSAddonList::where('user_id', $this->user_id)
            ->where('project_id', $project_id)->get();

        foreach ($allAddonListsData as $each) {
            $totalAddonValue += $each->ss_add_on_value;
        }

        $getTotal = $this->get_total($project_id);

        return response()->json([
            'status' => 'success',
            'message' => 'addon added successfully',
            'data' => [
                'addons' => $allAddonListsData,
                'compoundvalue' => $totalAddonValue,
                'totalestimate' => $getTotal
            ]
        ]);
    }

    // delete add-ons to ss
    public function delete_addon_to_ss(Request $request)
    {

        $addonlistid = $request->addonid;
        if (!$addonlistid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        } else {

            // find add-on row to db ss_add_on_list table
            $existAddonList = UserSSAddonList::find($addonlistid);
            $existKey = 0;

            $totalAddonValues = 0;
            if ($existAddonList) // already existed case
            {
                $user_id = $existAddonList->user_id;
                $project_id = $existAddonList->project_id;
                $existAddonList->delete();

                $getAllUserAddonValues = UserSSAddonList::where('user_id', $user_id)
                    ->where('project_id', $project_id)->get();


                foreach ($getAllUserAddonValues as $each) {
                    $totalAddonValues += $each->ss_add_on_value;
                    $existKey = 1;
                }

                $getTotal = $this->get_total($project_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'addon added successfully',
                    'data' => [
                        'deletedid' => $addonlistid,
                        'compoundvalue' => $totalAddonValues,
                        'existkey' => $existKey,
                        'totalestimate' => $getTotal
                    ]
                ]);
            } else {

                return response()->json([
                    'status' => 'success',
                    'message' => 'addon added successfully',
                    'data' => null
                ]);
            }
        }
    }


    // add cost item to spread sheet
    public function add_item_to_ss(Request $request)
    {
        $item_id = $request->item_id;
        $project_id = $request->project_id;
        $sort_status = $request->sort_status;
        $item = UserCostItem::where('user_id', $this->user_id)->where('id', $item_id)->first();
        $cost_group = UserCostGroup::where('user_id', $this->user_id)
            ->where('cost_group_number', $item->cost_group_number)->first();
        $group_desc = $cost_group->cost_group_desc;

        $ss = new Spreadsht;
        $ss->user_id = $this->user_id;
        $ss->project_id = $project_id;
        $ss->ss_item_cost_group_number = $item->cost_group_number;
        $ss->ss_item_cost_group_desc = $group_desc;
        $ss->ss_item_number = $item->item_number;
        $ss->ss_item_description = $item->item_desc;
        $ss->ss_notes = $item->notes;
        $ss->ss_item_takeoff_uom = $item->takeoff_uom;
        $ss->ss_labor_conversion_factor = $item->labor_conversion_factor;
        $ss->ss_labor_uom = $item->labor_uom;
        $ss->ss_labor_price = $item->labor_price;
        $ss->ss_material_conversion_factor = $item->material_conversion_factor;
        $ss->ss_material_uom = $item->material_uom;
        $ss->ss_material_price = $item->material_price;
        $ss->ss_subcontract_conversion_factor = $item->subcontract_conversion_factor;
        $ss->ss_subcontract_uom = $item->subcontract_uom;
        $ss->ss_subcontract_price = $item->subcontract_price;
        $ss->ss_other_conversion_factor = $item->other_conversion_factor;
        $ss->ss_other_uom = $item->other_uom;
        $ss->ss_other_price = $item->other_price;
        $ss->ss_home_depot_sku = $item->home_depot_sku;
        $ss->ss_home_depot_price = $item->home_depot_price;
        $ss->ss_lowes_sku = $item->lowes_sku;
        $ss->ss_lowes_price = $item->lowes_price;
        $ss->ss_whitecap_sku = $item->whitecap_sku;
        $ss->ss_whitecap_price = $item->whitecap_price;
        $ss->ss_bls_number = $item->bls_number;
        $ss->ss_bls_price = $item->bls_price;
        $ss->ss_grainger_number = $item->grainger_number;
        $ss->ss_grainger_price = $item->grainger_price;
        $ss->ss_wcyw_number = $item->wcyw_number;
        $ss->ss_wcyw_price = $item->wcyw_price;
        $ss->ss_quote_or_invoice_item = $item->invoice_item_default;
        $ss->ss_selected_vendor = $item->selected_vendor;
        $ss->ss_use_labor = $item->use_labor;
        $ss->ss_use_material = $item->use_material;
        $ss->ss_quote_or_invoice_item = $item->quote_or_invoice_item;
        $ss->ss_use_sub = $item->use_sub;

        if ($ss->save()) {
            // get spread sheet data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Item added successfully',
                'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Adding item error'
            ]);
        }
    }


    // add assembly items to spread sheet
    public function add_assemblyItems_to_ss(Request $request)
    {
        $item_id = $request->item_id;
        $project_id = $request->project_id;
        $sort_status = $request->sort_status;
        $assemblies = Assembly::find($item_id);
        $assembly_number = $assemblies->assembly_number;
        $items = AssemblyItem::where('user_id', $this->user_id)
            ->where('assembly_number', $assembly_number)->get()->toArray();
        foreach ($items as $item) {
            $costitem = UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $item['item_cost_group_number'])
                ->where('item_number', $item['item_number'])->first();
            $cost_group = UserCostGroup::where('user_id', $this->user_id)
                ->where('cost_group_number', $costitem->cost_group_number)->first();
            $group_desc = $cost_group->cost_group_desc;

            $ss = new Spreadsht;
            $ss->user_id = $this->user_id;
            $ss->project_id = $project_id;
            $ss->ss_item_cost_group_number = $costitem->cost_group_number;
            $ss->ss_item_cost_group_desc = $group_desc;
            $ss->ss_item_number = $costitem->item_number;
            $ss->ss_item_description = $costitem->item_desc;
            $ss->ss_notes = $costitem->notes;
            $ss->ss_item_takeoff_uom = $costitem->takeoff_uom;
            $ss->ss_labor_conversion_factor = $costitem->labor_conversion_factor;
            $ss->ss_labor_uom = $costitem->labor_uom;
            $ss->ss_labor_price = $costitem->labor_price;
            $ss->ss_material_conversion_factor = $costitem->material_conversion_factor;
            $ss->ss_material_uom = $costitem->material_uom;
            $ss->ss_material_price = $costitem->material_price;
            $ss->ss_subcontract_conversion_factor = $costitem->subcontract_conversion_factor;
            $ss->ss_subcontract_uom = $costitem->subcontract_uom;
            $ss->ss_subcontract_price = $costitem->subcontract_price;
            $ss->ss_other_conversion_factor = $costitem->other_conversion_factor;
            $ss->ss_other_uom = $costitem->other_uom;
            $ss->ss_other_price = $costitem->other_price;
            $ss->ss_home_depot_sku = $costitem->home_depot_sku;
            $ss->ss_home_depot_price = $costitem->home_depot_price;
            $ss->ss_lowes_sku = $costitem->lowes_sku;
            $ss->ss_lowes_price = $costitem->lowes_price;
            $ss->ss_whitecap_sku = $costitem->whitecap_sku;
            $ss->ss_whitecap_price = $costitem->whitecap_price;
            $ss->ss_bls_number = $costitem->bls_number;
            $ss->ss_bls_price = $costitem->bls_price;
            $ss->ss_grainger_number = $costitem->grainger_number;
            $ss->ss_grainger_price = $costitem->grainger_price;
            $ss->ss_wcyw_number = $costitem->wcyw_number;
            $ss->ss_wcyw_price = $costitem->wcywr_price;
            $ss->ss_quote_or_invoice_item = $costitem->invoice_item_default;
            $ss->ss_selected_vendor = $costitem->selected_vendor;
            $ss->ss_use_labor = $costitem->use_labor;
            $ss->ss_use_material = $costitem->use_material;
            $ss->ss_quote_or_invoice_item = $costitem->quote_or_invoice_item;
            $ss->ss_use_sub = $costitem->use_sub;
            $ss->save();
        }

        $ss_data = $this->get_ss_data($project_id, $sort_status);
        $price_comments = $this->get_price_comments($project_id, $sort_status);
        $total = $this->get_total($project_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Item added successfully',
            'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
        ]);
    }


    // update all TOQ occurrence
    public function update_all_toq_ss(Request $request)
    {
        try {
            $data = $request->data;
            $project_id = $request->project_id;
            $sort_status = $request->sort_status;
            $field_name = $this->FIELD_NAME[$data['columnName']];
            $ss = Spreadsht::find($data['id']);
            $ss[$field_name] = $data['value'];

            $cost_group_number = $ss->ss_item_cost_group_number;
            $item_number = $ss->ss_item_number;
            $matchThese = [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'ss_item_cost_group_number' => $cost_group_number,
                'ss_item_number' => $item_number,
            ];
            Spreadsht::where($matchThese)->update(['ss_item_takeoff_quantity' => $data['value']]);

            // get spread sheet data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Updated successfully',
                'data' => [
                    'ss_data' => $ss_data,
                    'total' => $total,
                    'price_comments' => $price_comments,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // update ss items
    public function update_ss_items(Request $request)
    {
        try {
            $data = $request->data;
            $project_id = $request->project_id;
            $sort_status = $request->sort_status;
            $field_name = $this->FIELD_NAME[$data['columnName']];
            $ss = Spreadsht::find($data['id']);


            // Set data that are required in order to refresh the spreadsht with previos data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $total = $this->get_total($project_id);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $isExistOccur = 1;

            // Columns for checking if they're allowed to update or not
            $laborColumns = ['ss_labor_conversion_factor', 'ss_labor_order_quantity', 'ss_labor_uom', 'ss_labor_price', 'ss_labor_total', 'ss_labor_markup_percent', 'ss_labor_markup_dollar_amount', 'ss_labor_total_markedup_total'];
            $materialColumns = ['ss_material_conversion_factor', 'ss_material_order_quantity', 'ss_material_uom', 'ss_material_price', 'ss_material_total', 'ss_material_markup_percent', 'ss_material_markup_dollar_amount', 'ss_material_total_markedup_total'];
            $subContractColumns = ['ss_subcontract_conversion_factor', 'ss_subcontract_order_quantity', 'ss_subcontract_uom', 'ss_subcontract_price', 'ss_subcontract_total', 'ss_subcontract_markup_percent', 'ss_subcontract_markup_dollar_amount', 'ss_subcontract_total_markedup_total'];


            if ((in_array($field_name, $laborColumns) && !$ss->ss_use_labor) || (in_array($field_name, $materialColumns) && !$ss->ss_use_material) || (in_array($field_name, $subContractColumns) && !$ss->ss_use_sub)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category is not allowed to update , please check and allow category from Cost Items',
                    'data' => [
                        'ss_data' => $ss_data,
                        'total' => $total,
                        'price_comments' => $price_comments,
                        'isExistOccur' => $isExistOccur,
                    ]
                ]);
            }

            $ss[$field_name] = $data['value'];
            $isExistOccur = 1;


            $cost_group_number = $ss->ss_item_cost_group_number;
            $item_number = $ss->ss_item_number;
            $matchThese = [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'ss_item_cost_group_number' => $cost_group_number,
                'ss_item_number' => $item_number,
            ];
            $existMatchCase = Spreadsht::where($matchThese)->get();
            $isExistOccur = count($existMatchCase);


            $ss->save();

            // get spread sheet data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Updated successfully',
                'data' => [
                    'ss_data' => $ss_data,
                    'total' => $total,
                    'price_comments' => $price_comments,
                    'isExistOccur' => $isExistOccur,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_all_price_ss(Request $request)
    {
        try {
            $data = $request->data;
            $project_id = $request->project_id;
            $sort_status = $request->sort_status;
            $field_name = $this->FIELD_NAME[$data['columnName']];
            $ss = Spreadsht::find($data['id']);
            $ss[$field_name] = $data['value'];

            $cost_group_number = $ss->ss_item_cost_group_number;
            $item_number = $ss->ss_item_number;
            $matchThese = [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'ss_item_cost_group_number' => $cost_group_number,
                'ss_item_number' => $item_number,
            ];
            Spreadsht::where($matchThese)->update([$field_name => $data['value']]);

            // get spread sheet data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Updated successfully',
                'data' => [
                    'ss_data' => $ss_data,
                    'total' => $total,
                    'price_comments' => $price_comments,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // sort SS
    public function sort_ss(Request $request)
    {
        $project_id = $request->project_id;
        $sort_status = $request->sort_status;

        $ss_data = $this->get_ss_data($project_id, $sort_status);
        $price_comments = $this->get_price_comments($project_id, $sort_status);
        $total = $this->get_total($project_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Sorted result',
            'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
        ]);
    }


    // remove selected items from spread sheet
    public function remove_bulk_ss_items(Request $request)
    {
        $selected_rows = $request->selectedRows;
        $project_id = $request->project_id;
        $sort_status = $request->sort_status;
        Spreadsht::whereIn('id', $selected_rows)->delete();
        // get spread sheet data
        $total = $this->get_total($project_id);
        $ss_data = $this->get_ss_data($project_id, $sort_status);
        $price_comments = $this->get_price_comments($project_id, $sort_status);

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed successfully',
            'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
        ]);
    }


    public function update_ss_setting(Request $request)
    {
        $data = $request->data;
        $project_id = $request->project_id;

        ProjectSetting::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'page_name' => $this->page_name
            ],
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'setting' => $data
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'SS setting updated successfully'
        ]);
    }


    public function update_ss_sidebar_status(Request $request)
    {
        $sidebar_status = $request->sidebar_status;
        $project_id = $request->project_id;

        ProjectSetting::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'page_name' => $this->page_name
            ],
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'is_sidebar_open' => $sidebar_status
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'SS sidebar status updated successfully'
        ]);
    }


    // get price
    public function get_price(Request $request)
    {
        try {
            $API = null;
            $source = $request->price_hd_lowes;
            $sort_status = $request->sort_status;
            switch ($source) {
                case 'hd':
                    $API = new HomeDepotApi();
                    break;
                case 'lowes':
                    $API = new LowesApi();
                    break;
                case 'whitecap':
                    $API = new WhitecapApi();
                    break;
                case 'BLS':
                    $API = new BlsApi();
                    break;
                case 'GRAINGER':
                    $API = new GraingerApi();
                    break;
                case 'WCYW':
                    $API = new WcywApi();
                    break;
                default:
                    return back()->with('info', 'Comming soon!');
                    break;
            }
            $store_to_lib = $request->price_store_to_lib ? true : false;
            $project_id = $request->price_project_id;
            $selected_rows = $request->price_selected_rows;
            $selected_rows = explode(',', $selected_rows);
            $zip_code = User::find($this->user_id)->postal_code;
            if (!empty($zip_code) && $source !== 'whitecap' && $source !== 'BLS' && $source !== 'GRAINGER' && $source !== 'WCYW')
                $API->setLocationFromZipCode($zip_code);

            //        $zip_code = '30301';
            //        $test_hd_sku = '206265821';
            //        $test_lowes_sku = '2000000';
            //        $price = $API->fetchProductPrice($test_hd_sku);
            //        $price = $API->fetchProductPrice($test_lowes_sku);
            //        dd($price);

            // get all sku
            $ss_items = Spreadsht::where('user_id', $this->user_id)->where('project_id', $project_id)->whereIn('id', $selected_rows)->get();
            $sku_array = [];
            foreach ($ss_items as $item) {
                $cost_item = UserCostItem::where('user_id', $this->user_id)
                    ->where('cost_group_number', $item->ss_item_cost_group_number)
                    ->where('item_number', $item->ss_item_number)->first();

                $sku_array[] = [
                    'cost_item_id' => $cost_item->id,
                    'ss_item_id' => $item->id,
                    'hd_sku' => $cost_item->home_depot_sku,
                    'lowes_sku' => $cost_item->lowes_sku,
                    'whitecap_sku' => $cost_item->whitecap_sku,
                    'bls_number' => $cost_item->bls_number,
                    'grainger_number' => $cost_item->grainger_number,
                    'wcyw_number' => $cost_item->wcyw_number
                ];
            }
            foreach ($sku_array as $item) {
                if ($source === 'hd') {
                    // get hd price
                    if ($item['hd_sku']) {
                        $price = $API->fetchProductPrice($item['hd_sku']);
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from Home Depot on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_material_price = $price;
                            $ss_item->ss_home_depot_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item hd price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->home_depot_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                } else if ($source === 'lowes') {
                    // get lowes price
                    if ($item['lowes_sku']) {
                        $price = $API->fetchProductPrice($item['lowes_sku']);
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from Lowes on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_material_price = $price;
                            $ss_item->ss_lowes_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item lowes price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->lowes_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                } else if ($source === 'whitecap') {
                    // get whitecap price
                    if ($item['whitecap_sku']) {
                        $price = $API->fetchProductPriceFromSku($item['whitecap_sku']);
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from Whitecap on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_material_price = $price;
                            $ss_item->ss_whitecap_sku = $item['whitecap_sku'];
                            $ss_item->ss_whitecap_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item whitecap price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->whitecap_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                } else if ($source === 'BLS') {
                    // get bls price
                    if ($item['bls_number']) {
                        $price = $API->fetchWageEstimateHourly90('https://www.bls.gov/oes/current/oes472111.htm');
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from BLS on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_labor_price = $price;
                            $ss_item->ss_bls_number = $item['bls_number'];
                            $ss_item->ss_bls_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item bls price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->bls_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                } else if ($source === 'GRAINGER') {
                    // get grainger price
                    if ($item['grainger_number']) {
                        $price = $API->fetchProductPriceFromSku($item['grainger_number']);
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from Grainger on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_grainger_number = $item['grainger_number'];
                            $ss_item->ss_grainger_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->ss_material_price = $price;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item grainger price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->grainger_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                } else if ($source === 'WCYW') {
                    // get wire and cable your way price
                    if ($item['wcyw_number']) {
                        $price = $API->fetchProductPriceFromSku($item['wcyw_number']);
                        if ($price) {
                            // update ss price
                            $time = date("m/d/y h:i A") . ' ' . date_default_timezone_get();
                            $price_info = "Retrieved from Wire & Cable YW on " . $time;
                            $ss_item = Spreadsht::find($item['ss_item_id']);
                            $ss_item->ss_wcyw_number = $item['wcyw_number'];
                            $ss_item->ss_wcyw_price = $price;
                            $ss_item->ss_price_info = $price_info;
                            $ss_item->ss_material_price = $price;
                            $ss_item->save();
                            if ($store_to_lib) {
                                // update cost item wcyw_price price
                                $cost_item = UserCostItem::find($item['cost_item_id']);
                                $cost_item->wcyw_price = $price;
                                $cost_item->save();
                            }
                        }
                    }
                }
            }

            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'The price are updated successfully',
                'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
            ]);
        } catch (\Throwable $th) {

            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch the price',
                'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
            ]);
        }
    }


    // get cost item formula for item
    public function get_formula(Request $request)
    {
        $item_id = $request->item_id;
        $formula_params = UserCostItem::find($item_id)->formula_params;
        $temp_result = json_decode($formula_params, true);
        $result = [];
        if ($temp_result) {
            foreach ($temp_result as $item) {
                if ($item['type'] === 'variable') {
                    $temp_question = UserQuestion::where('user_id', $this->user_id)->where('question', $item['val'])->first();
                    $question_note = $temp_question->notes;
                    $item['help'] = $question_note;
                }
                $result[] = $item;
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Fetch formula',
            'data' => $result
        ]);
    }


    // get assembly formula for item
    /* public function get_formula_assembly(Request $request)
    {
        $result = [];
        $item_id = $request->item_id;
        $assemblies = Assembly::find($item_id);
        $assembly_number = $assemblies->assembly_number;
        $items = AssemblyItem::select('item_cost_group_number','item_number', 'formula_params')->where('user_id', $this->user_id)->where('assembly_number', $assembly_number)->orderBy('item_order')->get()->toArray();

        foreach ($items as $item) {
            $formula_params = null;
            $costitem = UserCostItem::select('id', 'formula_params')->where('user_id', $this->user_id)->where('cost_group_number', $item['item_cost_group_number'])->where('item_number', $item['item_number'])->first();
            if ($item['formula_params']) {
                $formula_params = $item['formula_params'];
            } else {
                if ($costitem) {
                    $formula_params = $costitem->formula_params;
                }
            }

            if ($formula_params) {
                $temp_result = json_decode($formula_params, true);
                if ($temp_result) {
                    $updated_temp_result = [];
                    foreach ($temp_result as $el) {
                        if ($el['type'] === 'variable') {
                            $temp_question = UserQuestion::select('notes')->where('user_id', $this->user_id)->where('question', $el['val'])->first();
                            $question_note = $temp_question['notes'];
                            $el['help'] = $question_note;
                        }
                        $updated_temp_result[] = $el;
                    }

                    $temp = [];
                    $temp[] = $costitem->id;
                    $temp[] = $updated_temp_result;
                    $result[] = $temp;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch formula',
            'data' => $result
        ]);
    } */

    // get assembly formula for item
    public function get_formula_assembly(Request $request)
    {
        $result = [];
        $item_id = $request->item_id;
        $assemblies = Assembly::select('assembly_number')->find($item_id);
        $assembly_number = $assemblies->assembly_number;
        $items = AssemblyItem::select('item_cost_group_number', 'item_number', 'formula_params')->where('user_id', $this->user_id)->where('assembly_number', $assembly_number)->orderBy('item_order')->get()->toArray();

        foreach ($items as $item) {
            $formula_params = null;
            $costitem = UserCostItem::select('id', 'formula_params')->where('user_id', $this->user_id)->where('cost_group_number', $item['item_cost_group_number'])->where('item_number', $item['item_number'])->first();
            if ($item['formula_params']) {
                $formula_params = $item['formula_params'];
            } else {
                if ($costitem) {
                    $formula_params = $costitem->formula_params;
                }
            }

            if ($formula_params) {
                $temp_result = json_decode($formula_params, true);
                if ($temp_result) {
                    $updated_temp_result = [];
                    $array_column = array_column($temp_result, 'val');
                    $temp_question = UserQuestion::select('notes', 'question')->where('user_id', $this->user_id)->whereIn('question', $array_column)->pluck('notes', 'question');

                    foreach ($temp_result as $el) {
                        if ($el['type'] === 'variable') {
                            $question_note = $temp_question[$el['val']];
                            $el['help'] = $question_note;
                        }
                        $updated_temp_result[] = $el;
                    }

                    $temp = [];
                    $temp[] = $costitem->id;
                    $temp[] = $updated_temp_result;
                    $result[] = $temp;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch formula',
            'data' => $result
        ]);
    }


    // add assembly item to ss by interview
    public function add_assembly_to_ss_interview(Request $request)
    {
        $project_id = $request->project_id;
        $sort_status = $request->sort_status;
        $interviewId = $request->interviewId;
        $items = $request->TOQ;

        $ss_location = '';
        if (isset($request->interviewLocation)) {
            $ss_location = $request->interviewLocation;
        }

        // check qv interview
        $assembly = Assembly::find($interviewId);
        $is_qv = $assembly->is_qv;
        foreach ($items as $item) {
            $costitem = UserCostItem::find($item['costItemId']);
            $cost_group = UserCostGroup::where('user_id', $this->user_id)
                ->where('cost_group_number', $costitem->cost_group_number)->first();
            $group_desc = $cost_group->cost_group_desc;

            $ss = new Spreadsht;
            $ss->user_id = $this->user_id;
            $ss->project_id = $project_id;
            $ss->ss_item_cost_group_number = $costitem->cost_group_number;
            $ss->ss_item_cost_group_desc = $group_desc;
            $ss->ss_item_number = $costitem->item_number;
            $ss->ss_item_description = $costitem->item_desc;
            $ss->ss_notes = $costitem->notes;
            $ss->ss_item_takeoff_uom = $costitem->takeoff_uom;
            $ss->ss_labor_conversion_factor = $costitem->labor_conversion_factor;
            $ss->ss_labor_uom = $costitem->labor_uom;
            $ss->ss_labor_price = $costitem->labor_price;
            $ss->ss_material_conversion_factor = $costitem->material_conversion_factor;
            $ss->ss_material_uom = $costitem->material_uom;
            $ss->ss_material_price = $costitem->material_price;
            $ss->ss_subcontract_conversion_factor = $costitem->subcontract_conversion_factor;
            $ss->ss_subcontract_uom = $costitem->subcontract_uom;
            $ss->ss_subcontract_price = $costitem->subcontract_price;
            $ss->ss_other_conversion_factor = $costitem->other_conversion_factor;
            $ss->ss_other_uom = $costitem->other_uom;
            $ss->ss_other_price = $costitem->other_price;
            $ss->ss_home_depot_sku = $costitem->home_depot_sku;
            $ss->ss_home_depot_price = $costitem->home_depot_price;
            $ss->ss_lowes_sku = $costitem->lowes_sku;
            $ss->ss_lowes_price = $costitem->lowes_price;
            $ss->ss_whitecap_sku = $costitem->whitecap_sku;
            $ss->ss_whitecap_price = $costitem->whitecap_price;
            $ss->ss_bls_number = $costitem->bls_number;
            $ss->ss_bls_price = $costitem->bls_price;
            $ss->ss_grainger_number = $costitem->grainger_number;
            $ss->ss_grainger_price = $costitem->grainger_price;
            $ss->ss_wcyw_number = $costitem->wcyw_number;
            $ss->ss_wcyw_price = $costitem->wcyw_price;
            $ss->ss_quote_or_invoice_item = $costitem->invoice_item_default;
            $ss->ss_selected_vendor = $costitem->selected_vendor;
            $ss->ss_use_labor = $costitem->use_labor;
            $ss->ss_use_material = $costitem->use_material;
            $ss->ss_use_sub = $costitem->use_sub;
            $ss->ss_location = $ss_location;
            $ss->ss_quote_or_invoice_item = $costitem->quote_or_invoice_item;
            $ss->ss_is_qv = $is_qv;

            if ($item['DSQType'] === 'total') {
                $ss->ss_subcontract_price = $item['val'];
                $ss->ss_subcontract_conversion_factor = '1.0000';
                $ss->ss_subcontract_uom = 'lump sum';
                $ss->ss_item_takeoff_uom = 'lump sum';
                $ss->ss_item_takeoff_quantity = '1';
                $ss->ss_subcontract_order_quantity = '1';
                $ss->save();
            } else if ($item['DSQType'] === 'category') {
                $ss->ss_subcontract_price = $item['val']['categoryLab'];
                $ss->ss_material_price = $item['val']['categoryMat'];
                $ss->ss_subcontract_conversion_factor = '1.0000';
                $ss->ss_material_conversion_factor = '1.0000';
                $ss->ss_item_takeoff_uom = 'lump sum';
                $ss->ss_subcontract_uom = 'lump sum';
                $ss->ss_material_uom = 'lump sum';
                $ss->ss_item_takeoff_quantity = '1';
                $ss->ss_subcontract_order_quantity = '1';
                $ss->ss_material_order_quantity = '1';
                $ss->save();
            } else if ($item['DSQType'] === 'tricky') {
                $ss->ss_item_takeoff_quantity = $item['val']['trickyOfUnites'];
                $ss->ss_subcontract_price = $item['val']['trickyPerUnit'];
                $ss->ss_subcontract_conversion_factor = '1.0000';
                $ss->ss_subcontract_uom = 'lump sum';
                $ss->ss_item_takeoff_uom = 'lump sum';
                $ss->save();
            } else {
                if ($item['val'] && $item['val'] !== '0') {
                    $ss->ss_item_takeoff_quantity = $item['val'];
                    $ss->save();
                }
            }
        }

        $ss_data = $this->get_ss_data($project_id, $sort_status);
        $price_comments = $this->get_price_comments($project_id, $sort_status);
        $total = $this->get_total($project_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Item added successfully',
            'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
        ]);
    }


    // add cost item to ss by interview
    public function add_item_to_ss_by_interview(Request $request)
    {
        $item_id = $request->item_id;
        $project_id = $request->project_id;
        $TOQ = $request->TOQ;
        $sort_status = $request->sort_status;
        $item = UserCostItem::where('user_id', $this->user_id)->where('id', $item_id)->first();
        $cost_group = UserCostGroup::where('user_id', $this->user_id)
            ->where('cost_group_number', $item->cost_group_number)->first();
        $group_desc = $cost_group->cost_group_desc;

        $ss = new Spreadsht;
        $ss->user_id = $this->user_id;
        $ss->project_id = $project_id;
        $ss->ss_item_cost_group_number = $item->cost_group_number;
        $ss->ss_item_cost_group_desc = $group_desc;
        $ss->ss_item_number = $item->item_number;
        $ss->ss_item_description = $item->item_desc;
        $ss->ss_notes = $item->notes;
        $ss->ss_item_takeoff_uom = $item->takeoff_uom;
        $ss->ss_labor_conversion_factor = $item->labor_conversion_factor;
        $ss->ss_labor_uom = $item->labor_uom;
        $ss->ss_labor_price = $item->labor_price;
        $ss->ss_material_conversion_factor = $item->material_conversion_factor;
        $ss->ss_material_uom = $item->material_uom;
        $ss->ss_material_price = $item->material_price;
        $ss->ss_subcontract_conversion_factor = $item->subcontract_conversion_factor;
        $ss->ss_subcontract_uom = $item->subcontract_uom;
        $ss->ss_subcontract_price = $item->subcontract_price;
        $ss->ss_other_conversion_factor = $item->other_conversion_factor;
        $ss->ss_other_uom = $item->other_uom;
        $ss->ss_other_price = $item->other_price;
        $ss->ss_home_depot_sku = $item->home_depot_sku;
        $ss->ss_home_depot_price = $item->home_depot_price;
        $ss->ss_lowes_sku = $item->lowes_sku;
        $ss->ss_lowes_price = $item->lowes_price;
        $ss->ss_whitecap_sku = $item->whitecap_sku;
        $ss->ss_whitecap_price = $item->whitecap_price;
        $ss->ss_bls_number = $item->bls_number;
        $ss->ss_bls_price = $item->bls_price;
        $ss->ss_grainger_number = $item->grainger_number;
        $ss->ss_grainger_price = $item->grainger_price;
        $ss->ss_wcyw_number = $item->wcyw_number;
        $ss->ss_wcyw_price = $item->wcyw_price;
        $ss->ss_quote_or_invoice_item = $item->invoice_item_default;
        $ss->ss_selected_vendor = $item->selected_vendor;
        $ss->ss_use_labor = $item->use_labor;
        $ss->ss_use_material = $item->use_material;
        $ss->ss_quote_or_invoice_item = $item->quote_or_invoice_item;
        $ss->ss_use_sub = $item->use_sub;

        if ($TOQ['DSQType'] === 'total') {
            $ss->ss_subcontract_price = $TOQ['val'];
            $ss->ss_subcontract_conversion_factor = '1.0000';
            $ss->ss_subcontract_uom = 'lump sum';
            $ss->ss_item_takeoff_uom = 'lump sum';
            $ss->ss_item_takeoff_quantity = '1';
            $ss->ss_subcontract_order_quantity = '1';
        } else if ($TOQ['DSQType'] === 'category') {
            $ss->ss_labor_price = $TOQ['val']['categoryLab'];
            $ss->ss_material_price = $TOQ['val']['categoryMat'];
            $ss->ss_labor_conversion_factor = '1.0000';
            $ss->ss_material_conversion_factor = '1.0000';
            $ss->ss_item_takeoff_uom = 'lump sum';
            $ss->ss_labor_uom = 'lump sum';
            $ss->ss_material_uom = 'lump sum';
            $ss->ss_item_takeoff_quantity = '1';
            $ss->ss_labor_order_quantity = '1';
            $ss->ss_material_order_quantity = '1';
        } else if ($TOQ['DSQType'] === 'tricky') {
            $ss->ss_item_takeoff_quantity = $TOQ['val']['trickyOfUnites'];
            $ss->ss_subcontract_price = $TOQ['val']['trickyPerUnit'];
            $ss->ss_subcontract_conversion_factor = '1.0000';
            $ss->ss_subcontract_uom = 'lump sum';
            $ss->ss_item_takeoff_uom = 'lump sum';
        } else {
            if ($TOQ['val'] && $TOQ['val'] !== '0') {
                $ss->ss_item_takeoff_quantity = $TOQ['val'];
            }
        }

        if ($ss->save()) {
            // get spread sheet data
            $ss_data = $this->get_ss_data($project_id, $sort_status);
            $price_comments = $this->get_price_comments($project_id, $sort_status);
            $total = $this->get_total($project_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Item added successfully',
                'data' => ['ss_data' => $ss_data, 'total' => $total, 'price_comments' => $price_comments]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Adding item error'
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
