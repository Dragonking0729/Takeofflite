<?php

namespace App\Http\Controllers;

use App\Models\DailyLogsModel;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\User;
use App\Models\UserProposalDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DailyLogsController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 'Daily Logs';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Daily Logs';
    }

    public function index()
    {
        return redirect('dashboard');
    }

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

        $logs = DailyLogsModel::where('user_id', $this->user_id)->where('project_id', $id)->orderBy('log_entry_date', 'DESC')->get();
        $daily_logs = [];
        foreach ($logs as $log) {
            $attached_files = json_decode($log->attached_files);
            $temp = [];
            foreach ($attached_files as $file) {
                $temp[] = $file;
            }
            $log['files'] = $temp;
            $log['log_entry_date'] = Carbon::parse($log->log_entry_date)->format('Y-m-d\TH:i:s');
            $daily_logs[] = $log;
        }

        return view('daily_logs.show-daily-logs', compact('projects', 'page_info', 'daily_logs'));
    }


    public function store(Request $request)
    {
        $log_entry_date = $request->new_log_entry_date;
        $project_id = $request->project_id;
        $is_existing = DailyLogsModel::where('user_id', $this->user_id)->where('project_id', $project_id)->where('log_entry_date', $log_entry_date)->get()->count();
        if ($is_existing) {
            return back()->with('error', 'Already existing');
        } else {
            $is_customer_view = isset($request->new_customer_view) ? 1 : 0;
            $note = $request->new_note;

            // get zip code
            $project = Project::find($project_id);
            $customer_postal_code = $project->customer_postal_code;
            if (!$customer_postal_code) {
                $customer_postal_code = $project->postal_code;
                if (!$customer_postal_code) {
                    $user = User::find($this->user_id);
                    $customer_postal_code = $user->postal_code;
                    if (!$customer_postal_code) {
                        $customer_postal_code = 30032;
                    }
                }
            }

            $weather_info_image_path = "http://www.weatherusa.net/forecasts/?forecast=hourly&alt=hwicc&config=png&pands=" . $customer_postal_code;
            $weather_image = 'weather_log/' . $this->user_id . '/' . time() . '.png';
            $weather_image_path = 'public/' . $weather_image;
            Storage::disk('local')->put($weather_image_path, file_get_contents($weather_info_image_path));

            // upload attached files
            $attached_files = [];
            if ($request->hasfile('filenames')) {
                foreach ($request->file('filenames') as $file) {
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                    $name = $filename . '__' . time() . '.' . $extension;

                    $public_path = '/logs/' . $this->user_id . '/';
                    $path = public_path($public_path);
                    $file->move($path, $name);

                    $file_path = $public_path . $name;
                    $attached_files[] = [
                        'path' => $file_path,
                        'name' => $name,
                        'type' => $extension
                    ];
                }
            }
            $filenames = json_encode($attached_files);

            DailyLogsModel::create([
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'log_entry_date' => $log_entry_date,
                'customer_view' => $is_customer_view,
                'note' => $note,
                'attached_files' => $filenames,
                'weather' => $weather_image
            ]);
            return back()->with('success', 'Created successfully');
        }
    }


    // remove selected lines
    public function remove_logs(Request $request)
    {
        $selectedLines = $request->selectedLines;
        foreach ($selectedLines as $id) {
            $log = DailyLogsModel::find($id);

            // delete weather image
            if ($log->weather) {
                $weather_image_path = storage_path('app/public/') . $log->weather;
                if (file_exists($weather_image_path)) {
                    unlink($weather_image_path);
                }
            }

            // delete attached files
            $attached_files = json_decode($log->attached_files);
            foreach ($attached_files as $file) {
                $file_path = public_path() . $file->path;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            $log->delete();
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Selected logs are removed successfully',
        ]);
    }


    public function update_log_line(Request $request)
    {
        $field = $request->field;
        $id = $request->id;
        $val = $request->val;

        $log = DailyLogsModel::find($id);
        $log[$field] = $val;
        $log->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }


    public function attach_more_files(Request $request)
    {

        $log_id = $request->log_id;
        $log = DailyLogsModel::find($log_id);
        $existing_files = json_decode($log->attached_files);
        if ($request->hasfile('attach_more_files')) {
            foreach ($request->file('attach_more_files') as $file) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = $filename . '__' . time() . '.' . $extension;

                $public_path = '/logs/' . $this->user_id . '/';
                $path = public_path($public_path);
                $file->move($path, $name);

                $file_path = $public_path . $name;
                $existing_files[] = [
                    'path' => $file_path,
                    'name' => $name,
                    'type' => $extension
                ];
            }
            $log->attached_files = json_encode($existing_files);
            $log->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Uploaded files successfully',
            'data' => $existing_files
        ]);

    }


    public function create()
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
