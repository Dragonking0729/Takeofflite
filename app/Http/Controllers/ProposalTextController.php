<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserProposalText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProposalTextController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }


    public function index()
    {
        $page_info = ['name' => 'Standard Proposal Text'];
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);
        $proposal_text = UserProposalText::where('user_id', $this->user_id)->orderBy('title')->paginate(1);

        return view('proposal_text.show', compact('proposal_text', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $proposal_text = UserProposalText::where('user_id', $this->user_id)->orderBy('title')->paginate(1);
        return view('proposal_text.pagination', compact('proposal_text'))->render();
    }


    // get data by next/prev
    public function fetch(Request $request)
    {
        $proposal_text = UserProposalText::where('user_id', $this->user_id)->orderBy('title')->paginate(1);
        return view('proposal_text.pagination', compact('proposal_text'))->render();
    }

    // get question by id
    public function get_proposal_text_by_id(Request $request)
    {
        $proposal_text = UserProposalText::where('user_id', $this->user_id)->orderBy('title')->paginate(1);
        return view('proposal_text.pagination', compact('proposal_text'))->render();
    }

    // get tree data
    public function get_proposal_text_tree(Request $request)
    {
        $proposal_texts = UserProposalText::where('user_id', $this->user_id)->orderBy('title')->get()->toArray();
        $tree_data = get_proposal_text_tree($proposal_texts);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_title = UserProposalText::where('user_id', $this->user_id)->where('title', $request->atitle)->count();
        if ($check_exist_title) {
            return response()->json([
                'status' => 'error',
                'message' => 'Title already exist'
            ]);
        } else {
            UserProposalText::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'title' => $request->atitle,
                    'text' => $request->atext,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal text is added successfully'
        ]);
    }

    // update title or text
    public function update(Request $request)
    {
        $proposal_text = UserProposalText::find($request->id);
        if ($proposal_text->title === $request->title) {
            $proposal_text->text = $request->text;
            $proposal_text->save();
        } else {
            $check_exist_title = UserProposalText::where('user_id', $this->user_id)->where('title', $request->title)->count();
            if ($check_exist_title) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Title already exist'
                ]);
            } else {
                $proposal_text->title = $request->title;
                $proposal_text->text = $request->text;
                $proposal_text->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    public function destroy($id)
    {
        UserProposalText::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully!'
        ]);
    }
}
