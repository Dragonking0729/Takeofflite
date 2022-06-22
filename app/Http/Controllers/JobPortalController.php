<?php

namespace App\Http\Controllers;

use App\Models\FlipModel;
use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\Spreadsht;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class JobPortalController extends Controller
{
    private function get_ss_total($user_id, $projectId)
    {
        $data = Spreadsht::where('user_id', $user_id)->where('project_id', $projectId)->get()->toArray();
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
        $total_estimate = $total_labor + $total_material + $total_subcontract + $total_other;

        return $total_estimate;
    }

    private function get_budget_data($user_id, $projectId)
    {
        $data = Spreadsht::where('user_id', $user_id)->where('project_id', $projectId)->where('ss_is_qv', 1)
            ->orderBy('ss_item_cost_group_number')->orderBy('ss_item_number')->get()->toArray();

        $ss_data = [];
        foreach ($data as $row) {
            if ($row['ss_labor_conversion_factor'] == '0') {
                $row['ss_labor_conversion_factor'] = 1;
            }
            if ($row['ss_material_conversion_factor'] == '0') {
                $row['ss_material_conversion_factor'] = 1;
            }
            if ($row['ss_subcontract_conversion_factor'] == '0') {
                $row['ss_subcontract_conversion_factor'] = 1;
            }
            if ($row['ss_other_conversion_factor'] == '0') {
                $row['ss_other_conversion_factor'] = 1;
            }
            $labor_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
            $material_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
            $subcontract_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
            $other_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_other_conversion_factor'] * $row['ss_other_price'], 2);
            $total = $labor_total + $material_total + $subcontract_total + $other_total;

            $ss_data[] = [
                'desc' => $row['ss_item_description'],
                'qty' => $row['ss_item_takeoff_quantity'],
                'uom' => $row['ss_item_takeoff_uom'],
                'price' => $row['ss_subcontract_price'],
                'amount' => $total
            ];
        }

        return $ss_data;
    }

    // get sum of six value
    private function get_six_value($flip_info)
    {
        $purchase_price = $flip_info->purchase_price ? $flip_info->purchase_price : 0;
        $acquisition_costs = $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0;
        $holding_costs = $flip_info->holding_costs ? $flip_info->holding_costs : 0;
        $selling_costs = $flip_info->selling_costs ? $flip_info->selling_costs : 0;
        $financing_costs = $flip_info->financing_costs ? $flip_info->financing_costs : 0;
        $repair_cost = $flip_info->repair_cost ? $flip_info->repair_cost : 0;

        return $purchase_price + $acquisition_costs + $holding_costs + $selling_costs + $financing_costs + $repair_cost;
    }

    // calculate profit
    private function get_profit($flip_info, $six_value)
    {
        $arv = $flip_info->arv ? $flip_info->arv : 0;
        return $arv - $six_value;
    }

    // calculate max purchase
    private function get_max_purchase($flip_info, $profit)
    {
        $purchase_price = $flip_info->purchase_price ? $flip_info->purchase_price : 0;
        $max_purchase = $purchase_price + $profit;
        return $max_purchase;

    }

    // get full address
    private function get_full_address($project)
    {
        $address_1 = $project->street_address_1 ? $project->street_address_1 . ' ' : '';
        $address_2 = $project->street_address_2 ? $project->street_address_2 . ' ' : '';
        $city = $project->city ? $project->city . ' ' : '';
        $state = $project->state ? $project->state . ' ' : '';
        $address = trim($address_1 . $address_2 . $city . $state);

        return $address;
    }

    // get location
    private function get_geo_location($address)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=AIzaSyAvd8WHv_GoEG5eaOCwPLku8JeQzNno7dA';
        $response = Http::get($url);
        $response = $response->json();
        $location = '';
        if ($response['status'] === "OK") {
            $location = $response['results'][0]['geometry']['location'];
        }
        return json_encode($location);
    }

    public function index()
    {
        return redirect('https://takeofflite.com');
    }


    // /job/710/214
    public function show($user_id, $project_id)
    {
        if (!$user_id || !$project_id) {
            return redirect('https://takeofflite.com');
        } else {
            // job detail & analyzer page data
            $project = Project::find($project_id);

            // get geo location
            $origin_project = $project;
            $address = $this->get_full_address($origin_project);
            if (!isset($project->geo_location) || $project->geo_location == '""') {
                $job_address = $this->get_geo_location($address);
                $origin_project->geo_location = $job_address;
                $origin_project->save();
            } else {
                $job_address = $project->geo_location;
            }

            $profit = 0;
            $max_purchase = 0;
            $detailed_acquisition_costs = [];
            $detailed_holding_costs = [];
            $detailed_financing_costs = [];
            $detailed_selling_costs = [];
            $total_acquisition_costs = 0;
            $total_holding_costs = 0;
            $total_financing_costs = 0;
            $total_selling_costs = 0;

            $flip_info = FlipModel::where('user_id', $user_id)->where('project_id', $project_id)->first();
            $repair_cost = $this->get_ss_total($user_id, $project_id);

            if (isset($flip_info)) {
                // get six value
                $six_value = $this->get_six_value($flip_info);
                // profit = arv - 6 other fields
                $profit = $this->get_profit($flip_info, $six_value);
                // max purchase = purchase price + profit
                $max_purchase = $this->get_max_purchase($flip_info, $profit);

                // detailed acquisition costs
                $detailed_costs = json_decode($flip_info->detailed_acquisition_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_acquisition_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_acquisition_costs += (float)$item->amount;
                    }
                }

                // detailed holding costs
                $detailed_costs = json_decode($flip_info->detailed_holding_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_holding_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_holding_costs += (float)$item->amount;
                    }
                }

                // detailed selling costs
                $detailed_costs = json_decode($flip_info->detailed_selling_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_selling_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_selling_costs += (float)$item->amount;
                    }
                }

                // detailed financing costs
                $detailed_costs = json_decode($flip_info->detailed_financing_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_financing_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_financing_costs += (float)$item->amount;
                    }
                }
            }

            // get budget data (spreadsheet data)
            $budget = $this->get_budget_data($user_id, $project_id);

            // get pictures
            $pictures = ProjectPdfSheet::where('user_id', $user_id)->where('project_id', $project_id)->where('category', 'picture')->get();

            return view('job_portal.index', compact('project', 'flip_info', 'detailed_acquisition_costs',
                'total_acquisition_costs', 'detailed_holding_costs', 'total_holding_costs', 'detailed_selling_costs',
                'total_selling_costs', 'detailed_financing_costs', 'total_financing_costs', 'repair_cost', 'budget',
                'profit', 'max_purchase', 'pictures', 'job_address'));
        }
    }
}
