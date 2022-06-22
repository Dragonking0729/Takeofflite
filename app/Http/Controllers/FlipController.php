<?php

namespace App\Http\Controllers;

use App\Models\FlipModel;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Spreadsht;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FlipController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 'Flip Analyzer';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Flip Analyzer';
    }

    private function get_ss_total($projectId)
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
        $total_estimate = $total_labor + $total_material + $total_subcontract + $total_other;

        return $total_estimate;
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

    // update repair cost from ss total
    private function update_repair_cost($id, $val)
    {
        FlipModel::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'project_id' => $id
            ],
            [
                'repair_cost' => $val
            ]
        );
        return true;
    }


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
        $repair_cost = $this->get_ss_total($id);
        $this->update_repair_cost($id, $repair_cost);

        $flip_info = FlipModel::where('user_id', $this->user_id)->where('project_id', $id)->first();

        // get six value
        $six_value = $this->get_six_value($flip_info);

        // profit = arv - 6 other fields
        $profit = $this->get_profit($flip_info, $six_value);

        // max purchase = purchase price + profit
        $max_purchase = $this->get_max_purchase($flip_info, $profit);

        return view('flip.show-flip', compact('projects', 'page_info', 'repair_cost', 'flip_info',
            'max_purchase', 'profit'));
    }


    // update entry
    public function update_entry(Request $request)
    {
        $project_id = $request->project_id;
        $field = $request->field;
        $val = $request->val;

        $flip = FlipModel::where('user_id', $this->user_id)->where('project_id', $project_id)->first();
        $flip[$field] = $val;
        $flip->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully',
        ]);
    }


    // update entry
    public function update_flip_detail(Request $request)
    {
        $project_id = $request->project_id;
        $field = $request->field;
        $data = $request->data;

        $flip = FlipModel::where('user_id', $this->user_id)->where('project_id', $project_id)->first();
        $flip[$field] = $data;
        $flip->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Updated detail info successfully',
        ]);
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
