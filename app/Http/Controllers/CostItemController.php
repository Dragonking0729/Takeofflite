<?php

namespace App\Http\Controllers;

use App\Models\AssemblyItem;
use App\Models\ProjectShare;
use App\Models\Uom;
use App\Models\UserCostGroup;
use App\Models\UserCostItem;
use App\Models\Project;
use App\Models\UserFormula;
use App\Models\UserInvoiceItem;
use App\Models\UserProposalItem;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CostItemController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $page_info = ['name' => 'Cost Items'];
        // get cost item data
        $cost_item = UserCostItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('cost_group_number+0'))
            ->orderBy(DB::raw('item_number+0'))
            ->paginate(1);
        // get group desc
        $group_desc = '';
        if (count($cost_item)) {
            $group_number = $cost_item[0]->cost_group_number;
            $cost_group = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $group_number)->first();
            $group_desc = $cost_group->cost_group_desc;
        }
        // get formula params
        $formula_params = [];
        if (isset($cost_item[0]->formula_params)) {
            $temp_result = json_decode($cost_item[0]->formula_params, true) ? json_decode($cost_item[0]->formula_params, true) : [];
            if ($temp_result) {
                foreach ($temp_result as $item) {
                    if ($item['type'] === 'variable') {
                        $temp_question = UserQuestion::where('user_id', $this->user_id)->where('question', $item['val'])->first();
                        $question_note = $temp_question->notes;
                        $item['help'] = $question_note;
                    }
                    $formula_params[] = $item;
                }
            }
        }

        // get cost group, cost item tree data
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costgroup_tree = get_cost_group_tree($all_costgroup);
        $costgroup_tree = json_encode($costgroup_tree);
        $costitem_tree = get_cost_item_tree($all_costgroup, $all_costitems);
        $costitem_tree = json_encode($costitem_tree);

        $uom = Uom::orderBy('uom_name')->get();
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();

        $invoice_items_list = [""];
        $invoice_items = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_number'))->get();
        foreach ($invoice_items as $item) {
            $invoice_item = $item->invoice_standard_item_number . ' ' . $item->invoice_standard_item_description;
            $invoice_items_list[] = $invoice_item;
        }

        $proposal_items_list = [""];
        $proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_number'))->get();
        foreach ($proposal_items as $item) {
            $proposal_item = $item->proposal_standard_item_number . ' ' . $item->proposal_standard_item_description;
            $proposal_items_list[] = $proposal_item;
        }

        return view('costitem.show-costitem', compact(
            'projects',
            'cost_item',
            'group_desc',
            'costitem_tree',
            'uom',
            'costgroup_tree',
            'question',
            'formula_params',
            'page_info',
            'pre_defined_calculations',
            'invoice_items_list',
            'proposal_items_list'
        ));
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $cost_item = UserCostItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('cost_group_number+0'))
            ->orderBy(DB::raw('item_number+0'))
            ->paginate(1);

        $group_number = $cost_item[0]->cost_group_number;
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $group_number)->first();
        $group_desc = $cost_group->cost_group_desc;

        $uom = Uom::orderBy('uom_name')->get();
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();

        $formula_params = [];
        if (isset($cost_item[0]->formula_params)) {
            $temp_result = json_decode($cost_item[0]->formula_params, true) ? json_decode($cost_item[0]->formula_params, true) : [];
            if ($temp_result) {
                foreach ($temp_result as $item) {
                    if ($item['type'] === 'variable') {
                        $temp_question = UserQuestion::where('user_id', $this->user_id)->where('question', $item['val'])->first();
                        $question_note = $temp_question->notes;
                        $item['help'] = $question_note;
                    }
                    $formula_params[] = $item;
                }
            }
        }

        $invoice_items_list = [""];
        $invoice_items = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_number'))->get();
        foreach ($invoice_items as $item) {
            $invoice_item = $item->invoice_standard_item_number . ' ' . $item->invoice_standard_item_description;
            $invoice_items_list[] = $invoice_item;
        }

        $proposal_items_list = [""];
        $proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_number'))->get();
        foreach ($proposal_items as $item) {
            $proposal_item = $item->proposal_standard_item_number . ' ' . $item->proposal_standard_item_description;
            $proposal_items_list[] = $proposal_item;
        }

        $view_data = view('costitem.default-costitem', compact(
            'cost_item',
            'group_desc',
            'uom',
            'question',
            'formula_params',
            'pre_defined_calculations',
            'invoice_items_list',
            'proposal_items_list'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data,
                'formula_params' => $formula_params
            ]
        ]);
    }

    // create new question
    public function new_question(Request $request)
    {
        $check = UserQuestion::where('user_id', $this->user_id)->where('question', $request->newQuestion)->count();
        if ($check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Question already exists'
            ]);
        } else {
            $question = new UserQuestion;
            $question->user_id = $this->user_id;
            $question->question = $request->newQuestion;
            $question->notes = $request->helpNotes;
            $question->type = $request->questionType;
            $question->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Question added successfully',
                'id' => $question->id
            ]);
        }
    }


    // store formula
    public function store_formula(Request $request)
    {
        $check = UserFormula::where('user_id', $this->user_id)->where('calculation_name', $request->calculation_name)->count();
        if ($check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Calculation name is duplicated. Please choose other one.'
            ]);
        } else {
            $formula = new UserFormula;
            $formula->user_id = $this->user_id;
            $formula->calculation_name = $request->calculation_name;
            $formula->formula_body = $request->formula_body;
            $formula->save();
            $result = UserFormula::find($formula->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Saved calculations successfully',
                'result' => $result
            ]);
        }
    }

    public function store(Request $request)
    {
        $check_exist_costitem_number = UserCostItem::where('user_id', $this->user_id)
            ->where('cost_group_number', $request->acostgroup)->where('item_number', $request->aitem_number)->count();
        if ($check_exist_costitem_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cost item number already exist'
            ]);
        } else {
            UserCostItem::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'cost_group_number' => $request->acostgroup,
                    'item_number' => $request->aitem_number,
                    'item_desc' => $request->aitem_desc,
                    'notes' => $request->aitem_notes,
                    'takeoff_uom' => $request->atakeoff_uom,
                    'use_labor' => $request->use_labor,
                    'use_material' => $request->use_material,
                    'use_sub' => $request->use_sub,
                    'labor_uom' => $request->alabor_uom,
                    'material_uom' => $request->amaterial_uom,
                    'subcontract_uom' => $request->asubcontract_uom,
                    'labor_conversion_factor' => $request->alabor_conversion_factor,
                    'material_conversion_factor' => $request->amaterial_conversion_factor,
                    'subcontract_conversion_factor' => $request->asubcontract_conversion_factor,
                    'labor_price' => $request->alabor_price,
                    'material_price' => $request->amaterial_price,
                    'material_waste_factor' => $request->amaterial_waste_factor,
                    'labor_conversion_toggle_status' => $request->alabor_conversion_toggle_status,
                    'material_conversion_toggle_status' => $request->amaterial_conversion_toggle_status,
                    'subcontract_conversion_toggle_status' => $request->asubcontract_conversion_toggle_status,
                    'subcontract_price' => $request->asubcontract_price,
                    'lowes_sku' => $request->lowes_sku,
                    'lowes_price' => $request->lowes_price,
                    'home_depot_price' => $request->home_depot_price,
                    'home_depot_sku' => $request->home_depot_sku,
                    'whitecap_price' => $request->whitecap_price,
                    'whitecap_sku' => $request->whitecap_sku,
                    'bls_number' => $request->bls_number,
                    'bls_price' => $request->bls_price,
                    'grainger_number' => $request->grainger_number,
                    'grainger_price' => $request->grainger_price,
                    'wcyw_number' => $request->wcyw_number,
                    'wcyw_price' => $request->wcyw_price,
                    'invoice_item_default' => $request->aitem_invoice,
                    'quote_or_invoice_item' => $request->aitem_proposal,
                    'formula_params' => $request->formula_params
                ]
            );
            $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
            $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
            $costitem_tree = get_cost_item_tree($all_costgroup, $all_costitems);
            $page = get_cost_item_page($all_costgroup, $all_costitems, $request->acostgroup, $request->aitem_number);
            $costitem_tree = json_encode($costitem_tree);

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully',
                'tree_data' => $costitem_tree,
                'page' => $page
            ]);
        }
    }


    // renumbering
    public function renumbering(Request $request)
    {
        $is_exist_costgroup = UserCostGroup::where('user_id', $this->user_id)
            ->where('cost_group_number', $request->updated_costgroup_number)->count();
        if ($is_exist_costgroup) {
            $is_exist_item_number = UserCostItem::where('user_id', $this->user_id)
                ->where('cost_group_number', $request->updated_costgroup_number)
                ->where('item_number', $request->updated_costitem_number)->count();

            if ($is_exist_item_number) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item number already exist'
                ]);
            } else {
                //  update cost item number
                $updated_costgroup_number = $request->updated_costgroup_number;
                $updated_costitem_number = $request->updated_costitem_number;

                $cost_item = UserCostItem::find($request->id);
                AssemblyItem::where('user_id', $this->user_id)
                    ->where('item_cost_group_number', $cost_item->cost_group_number)
                    ->where('item_number', $cost_item->item_number)
                    ->update(array('item_cost_group_number' => $updated_costgroup_number, 'item_number' => $updated_costitem_number));

                $cost_item->cost_group_number = $updated_costgroup_number;
                $cost_item->item_number = $updated_costitem_number;
                $cost_item->save();

                $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
                $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
                $costitem_tree = get_cost_item_tree($all_costgroup, $all_costitems);
                $costitem_tree = json_encode($costitem_tree);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Item number updated successfully',
                    'tree_data' => $costitem_tree
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid cost group number'
            ]);
        }
    }


    public function destroy($id)
    {
        UserCostItem::find($id)->delete();
        $cost_item = UserCostItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('cost_group_number+0'))
            ->orderBy(DB::raw('item_number+0'))
            ->paginate(1);
        $group_number = $cost_item[0]->cost_group_number;
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $group_number)->first();
        $group_desc = $cost_group->cost_group_desc;
        $uom = Uom::orderBy('uom_name')->get();
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $formula_params = json_decode($cost_item[0]->formula_params, true);
        $formula_params = $formula_params ? $formula_params : [];
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();

        $invoice_items_list = [""];
        $invoice_items = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_number'))->get();
        foreach ($invoice_items as $item) {
            $invoice_item = $item->invoice_standard_item_number . ' ' . $item->invoice_standard_item_description;
            $invoice_items_list[] = $invoice_item;
        }

        $proposal_items_list = [""];
        $proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_number'))->get();
        foreach ($proposal_items as $item) {
            $proposal_item = $item->proposal_standard_item_number . ' ' . $item->proposal_standard_item_description;
            $proposal_items_list[] = $proposal_item;
        }

        $view_data = view('costitem.default-costitem', compact(
            'cost_item',
            'group_desc',
            'uom',
            'question',
            'formula_params',
            'pre_defined_calculations',
            'invoice_items_list',
            'proposal_items_list'
        ))->render();

        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree = get_cost_item_tree($all_costgroup, $all_costitems);
        $costitem_tree = json_encode($costitem_tree);

        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!',
            'data' => [
                'view_data' => $view_data,
                'tree_data' => $costitem_tree
            ]
        ]);
    }


    // get cost item by id
    public function get_costitem_by_id(Request $request)
    {
        $cost_item = UserCostItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('cost_group_number+0'))
            ->orderBy(DB::raw('item_number+0'))
            ->paginate(1);

        $group_number = $cost_item[0]->cost_group_number;
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $group_number)->first();
        $group_desc = $cost_group->cost_group_desc;

        $uom = Uom::orderBy('uom_name')->get();
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();

        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();

        $formula_params = [];
        if (isset($cost_item[0]->formula_params)) {
            $temp_result = json_decode($cost_item[0]->formula_params, true) ? json_decode($cost_item[0]->formula_params, true) : [];
            if ($temp_result) {
                foreach ($temp_result as $item) {
                    if ($item['type'] === 'variable') {
                        $temp_question = UserQuestion::where('user_id', $this->user_id)->where('question', $item['val'])->first();
                        $question_note = $temp_question->notes;
                        $item['help'] = $question_note;
                    }
                    $formula_params[] = $item;
                }
            }
        }

        $invoice_items_list = [""];
        $invoice_items = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_number'))->get();
        foreach ($invoice_items as $item) {
            $invoice_item = $item->invoice_standard_item_number . ' ' . $item->invoice_standard_item_description;
            $invoice_items_list[] = $invoice_item;
        }

        $proposal_items_list = [""];
        $proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_number'))->get();
        foreach ($proposal_items as $item) {
            $proposal_item = $item->proposal_standard_item_number . ' ' . $item->proposal_standard_item_description;
            $proposal_items_list[] = $proposal_item;
        }

        $view_data = view('costitem.default-costitem', compact(
            'cost_item',
            'group_desc',
            'uom',
            'question',
            'formula_params',
            'pre_defined_calculations',
            'invoice_items_list',
            'proposal_items_list'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data,
                'formula_params' => $formula_params
            ]
        ]);
    }


    // update cost item
    public function update_costitem(Request $request)
    {
        $cost_item = UserCostItem::find($request->data['id']);
        $cost_item->item_desc = $request->data['item_desc'];
        $cost_item->notes = $request->data['item_notes'];
        $cost_item->takeoff_uom = $request->data['takeoff_uom'];
        $cost_item->use_labor = $request->data['use_labor'];
        $cost_item->use_material = $request->data['use_material'];
        $cost_item->use_sub = $request->data['use_sub'];
        $cost_item->labor_uom = $request->data['labor_uom'];
        $cost_item->material_uom = $request->data['material_uom'];
        $cost_item->subcontract_uom = $request->data['subcontract_uom'];
        $cost_item->labor_price = $request->data['labor_price'];
        $cost_item->material_price = $request->data['material_price'];
        $cost_item->subcontract_price = $request->data['subcontract_price'];
        $cost_item->labor_conversion_factor = $request->data['labor_conversion_factor'];
        $cost_item->material_conversion_factor = $request->data['material_conversion_factor'];
        $cost_item->subcontract_conversion_factor = $request->data['subcontract_conversion_factor'];
        $cost_item->material_waste_factor = $request->data['material_waste_factor'];
        $cost_item->labor_conversion_toggle_status = $request->data['labor_conversion_toggle_status'];
        $cost_item->material_conversion_toggle_status = $request->data['material_conversion_toggle_status'];
        $cost_item->subcontract_conversion_toggle_status = $request->data['subcontract_conversion_toggle_status'];
        $cost_item->lowes_sku = $request->data['lowes_sku'];
        $cost_item->lowes_price = $request->data['lowes_price'];
        $cost_item->home_depot_price = $request->data['home_depot_price'];
        $cost_item->home_depot_sku = $request->data['home_depot_sku'];
        $cost_item->whitecap_price = $request->data['whitecap_price'];
        $cost_item->whitecap_sku = $request->data['whitecap_sku'];
        $cost_item->bls_number = $request->data['bls_number'];
        $cost_item->bls_price = $request->data['bls_price'];
        $cost_item->grainger_number = $request->data['grainger_number'];
        $cost_item->grainger_price = $request->data['grainger_price'];
        $cost_item->wcyw_number = $request->data['wcyw_number'];
        $cost_item->wcyw_price = $request->data['wcyw_price'];
        $cost_item->invoice_item_default = $request->data['item_invoice'];
        $cost_item->quote_or_invoice_item = $request->data['item_proposal'];
        $cost_item->formula_params = $request->data['formula_params'];
        $cost_item->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }
}
