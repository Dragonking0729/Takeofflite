<?php

namespace App\Http\Controllers;

use App\Models\AssemblyItem;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Uom;
use App\Models\UserProposalDetail;
use App\Models\UserProposalGroup;
use App\Models\UserProposalItem;
use App\Models\UserFormula;
use App\Models\UserInvoiceItem;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProposalItemController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $page_info = ['name' => 'Proposal Items'];

        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);
        // get proposal item data
        $proposal_item = UserProposalItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('proposal_standard_item_group_number+0'))
            ->orderBy(DB::raw('proposal_standard_item_number+0'))
            ->paginate(1);
        // get group desc
        $group_desc = '';
        if (count($proposal_item)) {
            $group_number = $proposal_item[0]->proposal_standard_item_group_number;
            $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $group_number)->first();
            $group_desc = $proposal_group->proposal_standard_item_group_description;
        }

        // get proposal group, proposal item tree data
        $all_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
        $all_proposalitems = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->orderBy(DB::raw('proposal_standard_item_number+0'))->get()->toArray();
        $proposalgroup_tree = get_proposal_group_tree($all_proposalgroup);

        $proposalgroup_tree = json_encode($proposalgroup_tree);
        $proposalitem_tree = get_proposal_item_tree($all_proposalgroup, $all_proposalitems);
        $proposalitem_tree = json_encode($proposalitem_tree);

        $uom = Uom::orderBy('uom_name')->get();

        return view('proposalitem.show-proposalitem', compact(
            'page_info',
            'projects',
            'uom',
            'group_desc',
            'proposal_item',
            'proposalitem_tree',
            'proposalgroup_tree'
        ));
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $proposal_item = UserProposalItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('proposal_standard_item_group_number+0'))
            ->orderBy(DB::raw('proposal_standard_item_number+0'))
            ->paginate(1);

        $group_number = $proposal_item[0]->proposal_standard_item_group_number;
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $group_number)->first();
        $group_desc = $proposal_group->proposal_standard_item_group_description;

        $uom = Uom::orderBy('uom_name')->get();

        $view_data = view('proposalitem.default-proposalitem', compact(
            'group_desc',
            'proposal_item',
            'uom'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data
            ]
        ]);
    }


    public function store(Request $request)
    {
        $check_exist_proposal_standard_item_number = UserProposalItem::where('user_id', $this->user_id)
            ->where('proposal_standard_item_group_number', $request->aproposalgroup)->where('proposal_standard_item_number', $request->aitem_number)->count();
        if ($check_exist_proposal_standard_item_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proposal item number already exist'
            ]);
        } else {
            UserProposalItem::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'proposal_standard_item_group_number' => $request->aproposalgroup,
                    'proposal_standard_item_number' => $request->aitem_number,
                    'proposal_standard_item_description' => $request->aitem_desc,
                    'proposal_standard_item_uom' => $request->atakeoff_uom,
                    'proposal_standard_item_default_markup_percent' => $request->amarkup_percent,
                    'proposal_standard_item_explanatory_text' => $request->aexplanatory_text,
                    'proposal_standard_item_internal_notes' => $request->ainternal_notes
                ]
            );

            // get proposal group, proposal item tree data
            $all_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
            $all_proposalitems = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->orderBy(DB::raw('proposal_standard_item_number+0'))->get()->toArray();
            $proposalitem_tree = get_proposal_item_tree($all_proposalgroup, $all_proposalitems);
            $proposalitem_tree = json_encode($proposalitem_tree);

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully',
                'tree_data' => $proposalitem_tree
            ]);
        }
    }


    // renumbering
    public function renumbering(Request $request)
    {
        $is_exist_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)
            ->where('proposal_standard_item_group_number', $request->updated_proposalgroup_number)->count();
        if ($is_exist_proposalgroup) {
            $is_exist_proposal_standard_item_number = UserProposalItem::where('user_id', $this->user_id)
                ->where('proposal_standard_item_group_number', $request->updated_proposalgroup_number)
                ->where('proposal_standard_item_number', $request->updated_proposalitem_number)->count();

            if ($is_exist_proposal_standard_item_number) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item number already exist'
                ]);
            } else {
                //  update proposal item number
                $updated_proposalgroup_number = $request->updated_proposalgroup_number;
                $updated_proposal_standard_item_number = $request->updated_proposalitem_number;

                $proposal_item = UserProposalItem::find($request->id);
                UserProposalDetail::where('user_id', $this->user_id)
                    ->where('proposal_item_group_number', $proposal_item->proposal_standard_item_group_number)
                    ->where('proposal_item_number', $proposal_item->proposal_standard_item_number)
                    ->update(array('proposal_item_group_number' => $updated_proposalgroup_number, 'proposal_item_number' => $updated_proposal_standard_item_number));

                $proposal_item->proposal_standard_item_group_number = $updated_proposalgroup_number;
                $proposal_item->proposal_standard_item_number = $updated_proposal_standard_item_number;
                $proposal_item->save();

                // get proposal group, proposal item tree data
                $all_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
                $all_proposalitems = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->orderBy(DB::raw('proposal_standard_item_number+0'))->get()->toArray();
                $proposalitem_tree = get_proposal_item_tree($all_proposalgroup, $all_proposalitems);
                $proposalitem_tree = json_encode($proposalitem_tree);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Item number updated successfully',
                    'tree_data' => $proposalitem_tree
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid proposal group number'
            ]);
        }
    }


    public function destroy($id)
    {
        UserProposalItem::find($id)->delete();
        $proposal_item = UserProposalItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('proposal_standard_item_group_number+0'))
            ->orderBy(DB::raw('proposal_standard_item_number+0'))
            ->paginate(1);
        $group_number = $proposal_item[0]->proposal_standard_item_group_number;
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $group_number)->first();
        $group_desc = $proposal_group->proposal_standard_item_group_description;
        $uom = Uom::orderBy('uom_name')->get();


        $view_data = view('proposalitem.default-proposalitem', compact(
            'proposal_item',
            'group_desc',
            'uom'
        ))->render();

        $all_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
        $all_proposalitems = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->orderBy(DB::raw('proposal_standard_item_number+0'))->get()->toArray();
        $proposalitem_tree = get_proposal_item_tree($all_proposalgroup, $all_proposalitems);
        $proposalitem_tree = json_encode($proposalitem_tree);

        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!',
            'data' => [
                'view_data' => $view_data,
                'tree_data' => $proposalitem_tree
            ]
        ]);
    }


    // get proposal item by id
    public function get_proposalitem_by_id(Request $request)
    {
        $proposal_item = UserProposalItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('proposal_standard_item_group_number+0'))
            ->orderBy(DB::raw('proposal_standard_item_number+0'))
            ->paginate(1);

        $group_number = $proposal_item[0]->proposal_standard_item_group_number;
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $group_number)->first();
        $group_desc = $proposal_group->proposal_standard_item_group_description;

        $uom = Uom::orderBy('uom_name')->get();

        $view_data = view('proposalitem.default-proposalitem', compact(
            'group_desc',
            'proposal_item',
            'uom'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data
            ]
        ]);
    }


    // update proposal item
    public function update_proposalitem(Request $request)
    {
        $proposal_item = UserProposalItem::find($request->data['id']);
        $proposal_item->proposal_standard_item_description = $request->data['item_desc'];
        $proposal_item->proposal_standard_item_uom = $request->data['takeoff_uom'];
        $proposal_item->proposal_standard_item_default_markup_percent = $request->data['markup_percent'];
        $proposal_item->proposal_standard_item_explanatory_text = $request->data['explanatory_text'];
        $proposal_item->proposal_standard_item_internal_notes = $request->data['internal_notes'];
        $proposal_item->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }
}
