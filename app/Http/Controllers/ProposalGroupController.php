<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserProposalDetail;
use App\Models\UserProposalGroup;
use App\Models\UserProposalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProposalGroupController extends Controller
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

        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->paginate(1);
        $page_info = ['name' => 'Proposal Groups'];

        return view('proposal_group.show-proposalgroup', compact('proposal_group', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->paginate(1);
        return view('proposal_group.pagination', compact('proposal_group'))->render();
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->paginate(1);
        return view('proposal_group.pagination', compact('proposal_group'))->render();
    }

    // get proposalgroup by id
    public function get_proposalgroup_by_id(Request $request)
    {
        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->paginate(1);
        return view('proposal_group.pagination', compact('proposal_group'))->render();
    }

    // get tree data
    public function get_proposalgroup_tree(Request $request)
    {
        $data = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
        $tree_data = get_proposal_group_tree($data);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_proposalgroup = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $request->aproposalgroup)->count();
        if ($check_exist_proposalgroup) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cost group number already exist'
            ]);
        } else {
            UserProposalGroup::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'proposal_standard_item_group_number' => $request->aproposalgroup,
                    'proposal_standard_item_group_description' => $request->adesc,
                    'is_folder' => $request->afolder ? 1 : 0
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully'
            ]);
        }
    }

    // update desc and folder
    public function update_desc_folder(Request $request)
    {
        $proposal_group = UserProposalGroup::find($request->id);
        $proposal_group->proposal_standard_item_group_description = $request->adesc;
        $proposal_group->is_folder = $request->afolder;
        $proposal_group->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    // renumbering
    public function renumbering(Request $request)
    {
        $updated_proposalgroup_number = $request->updated_proposalgroup_number;
        $check_duplicated = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $updated_proposalgroup_number)->count();
        if ($check_duplicated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cost group number already exist'
            ]);
        } else {
            $proposal_group = UserProposalGroup::find($request->id);

            UserProposalDetail::where('user_id', $this->user_id)
                ->where('proposal_item_group_number', $proposal_group->proposal_standard_item_group_number)
                ->update(array('proposal_item_group_number' => $updated_proposalgroup_number));

            UserProposalItem::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $proposal_group->proposal_standard_item_group_number)
                ->update(array('proposal_standard_item_group_number' => $updated_proposalgroup_number));

            $proposal_group->proposal_standard_item_group_number = $updated_proposalgroup_number;
            $proposal_group->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cost group number updated successfully'
            ]);
        }
    }

    public function destroy($id)
    {
        $proposal_group = UserProposalGroup::find($id);
        UserProposalItem::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $proposal_group->proposal_standard_item_group_number)->delete();
        $proposal_group->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!'
        ]);
    }
}
