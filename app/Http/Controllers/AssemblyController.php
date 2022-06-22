<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\UserCostGroup;
use App\Models\UserCostItem;
use App\Models\Project;
use App\Models\UserFormula;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AssemblyController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $page_info = ['name' => 'Interview'];
        $projects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get();

        // get assemblies
        $items = [];
        $assemblies = Assembly::where('user_id', $this->user_id)
            ->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')
            ->paginate(1);
        if (!$assemblies[0]['is_folder']) { // if folder
            $id = $assemblies[0]['assembly_number'];
            $assembly_items = AssemblyItem::where('user_id', $this->user_id)->where('assembly_number', $id)->orderBy('item_order')->orderBy('id')->get();

            foreach ($assembly_items as $e) {
                $id = $e['id'];
                $group_number = $e['item_cost_group_number'];
                $item_number = $e['item_number'];
                $item_order = $e['item_order'];

                $cost_item = UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $group_number)
                    ->where('item_number', $item_number)->first();

                $item_desc = $cost_item === null ? '' : $cost_item->item_desc;

                $is_formula_exist = false;
                if (isset($e['formula_params'])) {
                    $temp_result = json_decode($e['formula_params'], true) ? json_decode($e['formula_params'], true) : [];
                    $is_formula_exist = count($temp_result) ? true : false;
                }
                $items[] = [
                    'id' => $id,
                    'item_cost_group_number' => $group_number,
                    'item_number' => $item_number,
                    'item_desc' => $item_desc,
                    'item_order' => $item_order,
                    'is_formula_exist' => $is_formula_exist,
                ];
            }
        }

        // get assembly tree
        $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = get_assembly_tree_data($assembly_data);

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();

        return view('assembly.show-assembly', compact(
            'projects',
            'assemblies',
            'items',
            'assembly_tree_data',
            'costitem_tree_data',
            'page_info',
            'question',
            'pre_defined_calculations'
        ));
    }


    // get data by next/prev
    public function fetch(Request $request)
    {
        $assemblies = Assembly::where('user_id', $this->user_id)
            ->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')
            ->paginate(1);
        $items = [];
        if (!$assemblies[0]['is_folder']) { // if folder
            $id = $assemblies[0]['assembly_number'];
            $assembly_items = AssemblyItem::where('user_id', $this->user_id)->where('assembly_number', $id)->orderBy('item_order')->orderBy('id')->get();

            foreach ($assembly_items as $e) {
                $id = $e['id'];
                $group_number = $e['item_cost_group_number'];
                $item_number = $e['item_number'];
                $item_order = $e['item_order'];

                $cost_item = UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $group_number)
                    ->where('item_number', $item_number)->first();

                $item_desc = $cost_item === null ? '' : $cost_item->item_desc;

                $is_formula_exist = false;
                if (isset($e['formula_params'])) {
                    $temp_result = json_decode($e['formula_params'], true) ? json_decode($e['formula_params'], true) : [];
                    $is_formula_exist = count($temp_result) ? true : false;
                }
                $items[] = [
                    'id' => $id,
                    'item_cost_group_number' => $group_number,
                    'item_number' => $item_number,
                    'item_desc' => $item_desc,
                    'item_order' => $item_order,
                    'is_formula_exist' => $is_formula_exist,
                ];
            }
        }

        return view('assembly.pagination-assembly', compact('assemblies', 'items'))->render();
    }


    // get assembly data by id
    public function get_assembly_by_id(Request $request)
    {
        $assemblies = Assembly::where('user_id', $this->user_id)
            ->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')
            ->paginate(1);
        $items = [];
        if (!$assemblies[0]['is_folder']) { // if folder
            $id = $assemblies[0]['assembly_number'];
            $assembly_items = AssemblyItem::where('user_id', $this->user_id)->where('assembly_number', $id)->get();

            foreach ($assembly_items as $e) {
                $id = $e['id'];
                $group_number = $e['item_cost_group_number'];
                $item_number = $e['item_number'];
                $item_order = $e['item_order'];

                $cost_item = UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $group_number)
                    ->where('item_number', $item_number)->first();
                $item_desc = isset($cost_item->item_desc) ? $cost_item->item_desc : '';

                $is_formula_exist = false;
                if (isset($e['formula_params'])) {
                    $temp_result = json_decode($e['formula_params'], true) ? json_decode($e['formula_params'], true) : [];
                    $is_formula_exist = count($temp_result) ? true : false;
                }
                $items[] = [
                    'id' => $id,
                    'item_cost_group_number' => $group_number,
                    'item_number' => $item_number,
                    'item_desc' => $item_desc,
                    'item_order' => $item_order,
                    'is_formula_exist' => $is_formula_exist,
                ];
            }
        }

        return view('assembly.pagination-assembly', compact('assemblies', 'items'))->render();
    }

    // get assembly item formula
    public function get_assembly_item_formula(Request $request)
    {
        $assemblyItem = AssemblyItem::find($request->assemblyId);

        $cost_item = UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $assemblyItem->item_cost_group_number)
            ->where('item_number', $assemblyItem->item_number)->first();

        $item_desc = $cost_item === null ? '' : $cost_item->item_desc;

        $metaData = $assemblyItem->item_cost_group_number . " - " . $assemblyItem->item_number . " - " . $item_desc;

        $formula = [];
        if (isset($assemblyItem->formula_params)) {
            $temp_result = json_decode($assemblyItem->formula_params, true) ? json_decode($assemblyItem->formula_params, true) : [];
            if ($temp_result) {
                foreach ($temp_result as $item) {
                    if ($item['type'] === 'variable') {
                        $temp_question = UserQuestion::where('user_id', $this->user_id)->where('question', $item['val'])->first();
                        $question_note = isset($temp_question->notes) ? $temp_question->notes : '';
                        $item['help'] = $question_note;
                    }
                    $formula[] = $item;
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Assembly item formula',
            'result' => [
                'formula' => $formula,
                'meta_data' => $metaData
            ]
        ]);
    }

    // save assembly item formula
    public function save_assembly_item_formula(Request $request)
    {
        $assemblyItem = AssemblyItem::find($request->assemblyId);
        $assemblyItem->formula_params = $request->formula;
        $assemblyItem->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Assembly item formula is updated successfully'
        ]);
    }


    // update assembly item order
    public function update_assembly_item_order(Request $request)
    {
        $order = $request->order;
        for ($position = 1; $position <= count($order); $position++) {
            $item_id = $order[$position - 1];
            $assembly_item = AssemblyItem::find($item_id);
            $assembly_item->item_order = $position;
            $assembly_item->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Assembly item order is updated successfully'
        ]);
    }

    // add assembly
    public function add_assembly(Request $request)
    {
        $is_qv_exist = Assembly::where('is_qv', 1)->where('user_id', $this->user_id)->get()->count();
        $check_exist_assembly_number = Assembly::where('user_id', $this->user_id)
            ->where('assembly_number', $request->assembly_number)->count();
        if ($check_exist_assembly_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assembly number already exist'
            ]);
        } else {
            if (!empty($request->items)) {
                $items = $request->items;
                $assembly_items = [];
                foreach ($items as $item) {
                    $formula_params = isset($item['formula_body']) ? $item['formula_body'] : '';
                    $assembly_items[] = [
                        'user_id' => $this->user_id,
                        'assembly_number' => $request->assembly_number,
                        'item_cost_group_number' => $item['group_number'],
                        'item_number' => $item['item_number'],
                        'formula_params' => $formula_params,
                        'item_order' => $item['item_order'],
                    ];
                }
                AssemblyItem::insert($assembly_items);
            }

            $assembly = new Assembly;
            $assembly->user_id = $this->user_id;
            $assembly->assembly_number = $request->assembly_number;
            $assembly->assembly_desc = $request->assembly_desc;
            $assembly->is_folder = $request->is_folder;
            if (!$is_qv_exist) {
                $assembly->is_qv = $request->is_qv;
            }

            $assembly->save();

            // get assembly tree
            $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
            $assembly_tree_data = get_assembly_tree_data($assembly_data);

            // get cost item tree
            $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
            $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
            $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

            return response()->json([
                'status' => 'success',
                'message' => 'Interview added successfully',
                'tree_data' => [
                    'assembly_tree_data' => $assembly_tree_data,
                    'costitem_tree_data' => $costitem_tree_data
                ]
            ]);
        }
    }


    // delete assembly
    public function delete_assembly(Request $request)
    {
        $assembly = Assembly::where('user_id', $this->user_id)->where('id', $request->id);
        $rows = $assembly->get();
        $assembly_number = $rows[0]['assembly_number'];
        $assembly->delete();
        AssemblyItem::where('user_id', $this->user_id)
            ->where('assembly_number', $assembly_number)->delete();

        // get assembly tree
        $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = get_assembly_tree_data($assembly_data);

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

        return response()->json([
            'status' => 'success',
            'message' => 'Interview deleted successfully',
            'tree_data' => [
                'assembly_tree_data' => $assembly_tree_data,
                'costitem_tree_data' => $costitem_tree_data
            ]
        ]);
    }


    // delete assembly item
    public function delete_assembly_item(Request $request)
    {
        if (AssemblyItem::find($request->id)->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item deleted successfully'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Delete item failed'
        ]);
    }


    // update assembly desc and items
    public function update_assembly(Request $request)
    {
        $assembly = Assembly::find($request->id);
        $assembly_number = $assembly->assembly_number;
        if (!empty($request->items)) {
            $order = AssemblyItem::where('user_id', $this->user_id)->where('assembly_number', $assembly_number)->get()->count();
            $items = $request->items;
            $assembly_items = [];
            foreach ($items as $item) {
                $order++;
                $formula_params = isset($item['formula_body']) ? $item['formula_body'] : '';
                $assembly_items[] = [
                    'user_id' => $this->user_id,
                    'assembly_number' => $item['assembly_number'],
                    'item_cost_group_number' => $item['group_number'],
                    'item_number' => $item['item_number'],
                    'formula_params' => $formula_params,
                    'item_order' => $order
                ];
            }
            AssemblyItem::insert($assembly_items);
        }

        $assembly->assembly_desc = $request->assembly_desc;
        $assembly->save();

        // get assembly tree
        $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = get_assembly_tree_data($assembly_data);

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

        return response()->json([
            'status' => 'success',
            'message' => 'Interview updated successfully',
            'tree_data' => [
                'assembly_tree_data' => $assembly_tree_data,
                'costitem_tree_data' => $costitem_tree_data
            ]
        ]);
    }


    // group ---> ungrouping....
    public function ungrouping_assembly(Request $request)
    {
        $id = $request->id;
        $assembly = Assembly::find($id);
        $assembly->is_folder = 0;
        $assembly->save();

        // get assembly tree
        $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = get_assembly_tree_data($assembly_data);

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

        return response()->json([
            'status' => 'success',
            'message' => 'Interview updated successfully',
            'tree_data' => [
                'assembly_tree_data' => $assembly_tree_data,
                'costitem_tree_data' => $costitem_tree_data
            ]
        ]);
    }


    // ungroup ---> grouping...
    public function grouping_assembly(Request $request)
    {
        $id = $request->id;
        $assembly = Assembly::find($id);
        $assembly->is_folder = 1;
        $assembly->save();

        // get assembly tree
        $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
        $assembly_tree_data = get_assembly_tree_data($assembly_data);

        // get cost item tree
        $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
        $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

        return response()->json([
            'status' => 'success',
            'message' => 'Interview updated successfully',
            'tree_data' => [
                'assembly_tree_data' => $assembly_tree_data,
                'costitem_tree_data' => $costitem_tree_data
            ]
        ]);
    }


    // update QV
    public function update_qv_field(Request $request)
    {
        $id = $request->assembly_id;
        $qv_assembly = Assembly::where('is_qv', 1)->where('user_id', $this->user_id)->first();
        if ($qv_assembly) {
            if ($qv_assembly->id == $id) {
                $qv_assembly->is_qv = !$qv_assembly->is_qv;
                $qv_assembly->save();
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fix and Flip interview already existing'
                ]);
            }
        } else {
            $assembly = Assembly::find($id);
            $assembly->is_qv = $assembly->is_qv ? 0 : 1;
            $assembly->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }


    // update assembly number  -- renumbering
    public function update_assembly_number(Request $request)
    {
        $check_exist_assembly_number = Assembly::where('user_id', $this->user_id)
            ->where('assembly_number', $request->assembly_number)->count();
        if ($check_exist_assembly_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assembly number already exist'
            ]);
        } else {
            $assembly = Assembly::find($request->id);
            AssemblyItem::where('user_id', $this->user_id)
                ->where('assembly_number', $assembly->assembly_number)
                ->update(array('assembly_number' => $request->assembly_number));

            $assembly->assembly_number = $request->assembly_number;
            $assembly->save();

            // get assembly tree
            $assembly_data = Assembly::where('user_id', $this->user_id)->orderBy(DB::raw('assembly_number+0'))->orderBy('assembly_desc')->get()->toArray();
            $assembly_tree_data = get_assembly_tree_data($assembly_data);

            // get cost item tree
            $all_costgroup = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
            $all_costitems = UserCostItem::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->orderBy(DB::raw('item_number+0'))->get()->toArray();
            $costitem_tree_data = get_assembly_cost_item_tree($all_costgroup, $all_costitems);

            return response()->json([
                'status' => 'success',
                'message' => 'Renumbering interview successfully',
                'tree_data' => [
                    'assembly_tree_data' => $assembly_tree_data,
                    'costitem_tree_data' => $costitem_tree_data
                ]
            ]);
        }
    }


    // remove bulk assembly items
    public function bulk_delete_assembly_items(Request $request)
    {
        if (AssemblyItem::whereIn('id', $request->checked_item_ids)->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Selected items are removed successfully'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Failed'
        ]);
    }
}
