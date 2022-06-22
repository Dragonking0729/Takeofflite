<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TakeoffQuestion extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }


    public function index()
    {
        $page_info = ['name' => 'Takeoff Questions'];

        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->paginate(1);

        return view('question.show-question', compact('question', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->paginate(1);
        return view('question.pagination', compact('question'))->render();
    }


    // get data by next/prev
    public function fetch(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->paginate(1);
        return view('question.pagination', compact('question'))->render();
    }

    // get question by id
    public function get_question_by_id(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->paginate(1);
        return view('question.pagination', compact('question'))->render();
    }

    // get tree data
    public function get_question_tree(Request $request)
    {
        $questions = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get()->toArray();
        $tree_data = get_question_tree($questions);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_question = UserQuestion::where('user_id', $this->user_id)->where('question', $request->aquestion)
            ->where('type', $request->add_question_type)->count();
        if ($check_exist_question) {
            return response()->json([
                'status' => 'error',
                'message' => 'Question already exist'
            ]);
        } else {
            UserQuestion::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'question' => $request->aquestion,
                    'notes' => $request->adesc,
                    'type' => $request->add_question_type
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Question is added successfully'
        ]);
    }

    // update question or notes
    public function update(Request $request)
    {
        $question = UserQuestion::find($request->id);
        if ($question->question === $request->question) {
            $question->notes = $request->desc;
            $question->type = $request->question_type;
            $question->save();
        } else {
            $check_exist_question = UserQuestion::where('user_id', $this->user_id)->where('question', $request->question)
                ->where('type', $request->question_type)->count();
            if ($check_exist_question) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Question already exist'
                ]);
            } else {
                $question->question = $request->question;
                $question->notes = $request->desc;
                $question->type = $request->question_type;
                $question->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    public function destroy($id)
    {
        UserQuestion::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Question deleted successfully!'
        ]);
    }
}
