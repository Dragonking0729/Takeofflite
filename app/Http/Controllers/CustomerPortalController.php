<?php

namespace App\Http\Controllers;

use App\Models\DailyLogsModel;
use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\User;
use App\Models\UserInvoices;
use App\Models\UserProposals;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CustomerPortalController extends Controller
{
    protected $share_id = 0;
    protected $project_id = 0;

    public function __construct()
    {
        $this->share_id = Session::get('share_id');
        $this->project_id = Session::get('project_id');
    }

    // get customer_portal group > customer_portal item tree
    private function buildCustomerPortalList()
    {
        $tree_data = [];

        // build proposals list
        $data = UserProposals::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('is_locked', 1)->get();
        $children = [];
        $is_existing_not_viewed = false;
        foreach ($data as $item) {
            $text = $item['proposal_name'] . ' #' . $item['id'];
            $id = 'proposal__' . $item['id'];
            $is_viewed = $item->is_viewed;
            if ($is_viewed) {
                $children[] = [
                    "id" => $id,
                    "text" => $text,
                    "type" => "child",
                    "icon" => "jstree-file",
                ];
            } else {
                $is_existing_not_viewed = true;
                $children[] = [
                    "id" => $id,
                    "text" => $text,
                    "type" => "child",
                    "icon" => "jstree-file",
                    "a_attr" => [
                        "class" => "not_viewed"
                    ]
                ];
            }
        }
        if ($is_existing_not_viewed) {
            $tree_data[] = [
                "id" => "proposal_folder",
                "text" => "PROPOSAL",
                "children" => $children,
                "a_attr" => [
                    "class" => "not_viewed"
                ]
            ];
        } else {
            $tree_data[] = [
                "id" => "proposal_folder",
                "text" => "PROPOSAL",
                "children" => $children
            ];
        }


        // build invoices list
        $data = UserInvoices::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('is_locked', 1)->get();
        $children = [];
        $is_existing_not_viewed = false;
        foreach ($data as $item) {
            $text = $item['invoice_name'] . ' #' . $item['id'];
            $id = 'invoice__' . $item['id'];
            $is_viewed = $item->is_viewed;
            if ($is_viewed) {
                $children[] = [
                    "id" => $id,
                    "text" => $text,
                    "type" => "child",
                    "icon" => "jstree-file",
                ];
            } else {
                $is_existing_not_viewed = true;
                $children[] = [
                    "id" => $id,
                    "text" => $text,
                    "type" => "child",
                    "icon" => "jstree-file",
                    "a_attr" => [
                        "class" => "not_viewed"
                    ]
                ];
            }
        }
        if ($is_existing_not_viewed) {
            $tree_data[] = [
                "id" => "invoice_folder",
                "text" => "INVOICES",
                "children" => $children,
                "a_attr" => [
                    "class" => "not_viewed"
                ]
            ];
        } else {
            $tree_data[] = [
                "id" => "invoice_folder",
                "text" => "INVOICES",
                "children" => $children,
            ];
        }

        // build daily log list
        $tree_data[] = [
            "id" => "dailylog_folder",
            "text" => "DAILY LOGS",
            "children" => []
        ];

        // build pictures list
        $tree_data[] = [
            "id" => "pictures_folder",
            "text" => "PICTURES",
            "children" => []
        ];

        // build videos list
        $tree_data[] = [
            "id" => "videos_folder",
            "text" => "VIDEOS",
            "children" => []
        ];

        // build files list
        $tree_data[] = [
            "id" => "files_folder",
            "text" => "FILES",
            "children" => []
        ];


        return $tree_data;
    }


    // generate Daily Log page HTML
    private function generateDLPage($data)
    {
        $html = '<div class="daily_log_area">';

        foreach ($data as $index => $log) {
            $html .= '<div class="daily_log_item">
                    <div class="daily_log_item_left">
                        <span class="daily_log_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="daily_log_item_right">
                        <div class="row py-2 daily_log_item_right_top">
                            <div class="col-md-3">
                                <label for="log_entry_date__' . $log->id . '">Log entry date/time</label>
                                <input type="datetime-local" class="form-control log_entry_date_picker"
                                       id="log_entry_date__' . $log->id . '"
                                       value="' . $log->log_entry_date . '" data-field="log_entry_date" disabled>
                            </div>
                            <div class="col-md-3">
                                <img src="' . asset('storage/' . $log->weather) . '"
                                     width="170" height="100" alt="Weather"
                                     title="Forecast from weatherUSA"/>
                            </div>
                        </div>
                        <div class="row py-2 daily_log_item_right_bottom">
                            <div class="col-md-12">
                                <textarea class="form-control note" rows="2"
                                          data-field="note" disabled>' . $log->note . '</textarea>
                            </div>';

            $files = '<div class="row" id="attached_files_list_' . $log->id . '">';
            foreach ($log->files as $file) {
                $files .= '<div class="col-md-1 pb-2 pt-2"><div>';
                if ($file->type === 'svg' || $file->type === 'jpg' || $file->type === 'jpeg' || $file->type === 'png') {
                    $files .= '<a href="' . asset($file->path) . '" data-fancybox="attached_files_list_' . $log->id . '" data-caption="' . $file->name . '">
                                    <img src="' . asset($file->path) . '" alt="' . $file->name . '"
                                         class="img-thumbnail rounded"
                                         style="width:100px;" title="' . $file->name . '">
                                </a>';
                } else {
                    $files .= '<a href="' . asset("/icons/noun_other_3482826.png") . '" data-fancybox="attached_files_list_' . $log->id . '" data-caption="' . $file->name . '">
                                    <img src="' . asset("/icons/noun_other_3482826.png") . '"
                                         class="img-thumbnail rounded"
                                         alt="' . $file->name . '" style="width:100px;"
                                         title="' . $file->name . '">
                                </a>';
                }

                $files .= '</div>
                    <div class="d-flex">
                        <div class="attach_file_name">' . $file->name . '</div>
                        <div>
                            <a href="' . asset($file->path) . '" download>
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>';
            }

            $html .= $files;
            $html .= '</div>

                        </div>
                    </div>
                </div>';
        }
        $html .= '</div>';

        return $html;
    }


    // generate picture page HTML
    private function genPictureHTML($files)
    {
        $html = '';
        if (count($files)) {
            foreach ($files as $file) {
                $html .= '<div class="col-md-2 m-1 px-0 sheet_image" data-order="' . $file['id'] . '">
                    <a href="' . asset($file->file) . '" data-fancybox="attached_files_list"
                       class="btn btn-link select_sheet" data-caption="' . $file->sheet_name . '">
                        <img src="' . asset($file->file) . '" alt="picture" class="image" title="' . $file->sheet_name . '">
                    </a>

                    <div class="d-flex justify-content-between sheet_title">
                        <div data-name="sheet_label" class="text-center m-auto sheet_name_edit"
                             data-id="' . $file->id . '">' . $file->sheet_name . '
                        </div >
                        <div class="d-flex">
                            <div class="download">
                                <a href="' . asset($file->file) . '" class="btn btn-link download_sheet" download
                                   data-id="' . $file->id . '" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div >';
            }
        } else {
            $html = '<div class="text-center">No existing data</div >';
        }
        return $html;
    }


    // generate video page HTML
    private function genVideoHTML($files)
    {
        $html = '';
        if (count($files)) {
            foreach ($files as $file) {
                $html .= '<div class="col-md-2 m-1 px-0 sheet_image" data-order="' . $file['id'] . '">
                    <a href="' . asset('/icons/video.png') . '" data-fancybox="attached_files_list"
                       class="btn btn-link select_sheet" data-caption="' . $file->sheet_name . '">
                        <img src="' . asset('/icons/video.png') . '" alt="picture" class="image" title="' . $file->sheet_name . '">
                    </a>

                    <div class="d-flex justify-content-between sheet_title">
                        <div data-name="sheet_label" class="text-center m-auto sheet_name_edit"
                             data-id="' . $file->id . '">' . $file->sheet_name . '
                        </div >
                        <div class="d-flex">
                            <div class="download">
                                <a href="' . asset($file->file) . '" class="btn btn-link download_sheet" download
                                   data-id="' . $file->id . '" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div >';
            }
        } else {
            $html = '<div class="text-center">No existing data</div >';
        }
        return $html;
    }


    // generate video page HTML
    private function genOtherFileHTML($files)
    {
        $html = '';
        if (count($files)) {
            foreach ($files as $file) {
                $temp = '';
                if (substr($file->pdf_path, -4) === '.pdf') {
                    $temp .= '<a href = "' . asset($file->pdf_path) . '" target = "_blank" class="btn btn-link select_sheet" >
                            <img src = "' . asset('/icons/document.png') . '" alt = "file" class="image"
                                 title = "' . $file->sheet_name . '" >
                        </a >';
                } else {
                    $temp .= '<a href = "' . asset('/icons/document.png') . '"  data-fancybox="attached_files_list"
                       class="btn btn-link select_sheet" data-caption="' . $file->sheet_name . '">
                            <img src = "' . asset('/icons/document.png') . '" alt = "file" class="image"
                                 title = "' . $file->sheet_name . '" >
                        </a >';
                }
                $html .= '<div class="col-md-2 m-1 px-0 sheet_image" data-order="' . $file['id'] . '">
                ' . $temp . '
                    <div class="d-flex justify-content-between sheet_title">
                        <div data-name="sheet_label" class="text-center m-auto sheet_name_edit"
                             data-id="' . $file->id . '">' . $file->sheet_name . '
                        </div >
                        <div class="d-flex">
                            <div class="download">
                                <a href="' . asset($file->file) . '" class="btn btn-link download_sheet" download
                                   data-id="' . $file->id . '" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div >';
            }
        } else {
            $html = '<div class="text-center">No existing data</div >';
        }
        return $html;
    }


    public function check_customer()
    {
        return view('customer.passcode');
    }

    // check customer passcode
    public function check_passcode(Request $request)
    {
        $passcode = $request->passcode;

        if (trim($passcode) == $this->share_id) {
            Session::put([
                'passcode' => $passcode
            ]);
            return redirect()->route('customer.show')->with('success', "Welcome to Takeoff lite!");
        } else {
            return back()->with('error', "Pass code doesn't match!");
        }
    }


    // show customer portal page
    public function show()
    {
        $project_id = $this->project_id;
        $share_id = $this->share_id;

        $project_info = Project::find($project_id);
        $company_info = User::find($share_id);
        $page_info = array(
            'name' => 'CONTRACTOR CUSTOMER PORTAL',
            'project_info' => $project_info,
            'company_info' => $company_info
        );

        $customer_portal_item_tree_data = $this->buildCustomerPortalList();

        return view('customer.index',
            compact(
                'page_info',
                'customer_portal_item_tree_data'
            )
        );
    }


    // get preview content
    public function get_preview_content(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $content = '';
        if ($type === 'invoice') {
            $data = UserInvoices::find($id);
            $content = $data->preview_content;
            $data->is_viewed = 1;
            $data->save();
        } else if ($type === 'proposal') {
            $data = UserProposals::find($id);
            $content = $data->preview_content;
            $data->is_viewed = 1;
            $data->save();
        } else if ($type === 'daily_log') {
            $logs = DailyLogsModel::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('customer_view', 1)->orderBy('log_entry_date', 'DESC')->get();
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
            $content = $this->generateDLPage($daily_logs);
        } else if ($type === 'picture') {
            $files = ProjectPdfSheet::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('category', $type)->orderBy('sheet_order')->orderBy('id')->get();
            $content = $this->genPictureHTML($files);
        } else if ($type === 'video') {
            $files = ProjectPdfSheet::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('category', $type)->orderBy('sheet_order')->orderBy('id')->get();
            $content = $this->genVideoHTML($files);
        } else if ($type === 'other') {
            $files = ProjectPdfSheet::where('user_id', $this->share_id)->where('project_id', $this->project_id)->where('category', $type)->orderBy('sheet_order')->orderBy('id')->get();
            $content = $this->genOtherFileHTML($files);
        }

        $tree_data = $this->buildCustomerPortalList();
        return response()->json([
            'status' => 'success',
            'message' => 'Get preview content successfully',
            'data' => ['content' => $content, 'tree_data' => $tree_data]
        ]);
    }

    // approve proposal
    public function approve_proposal(Request $request)
    {
        $proposal_id = $request->proposal_id;
        $approve_date = $request->approve_date;
        $approve_name = $request->approve_name;

        $proposal = UserProposals::find($proposal_id);
        $proposal->approve_name = $approve_name;
        $proposal->approve_date = $approve_date;
        $proposal->approve_status = 'Approved';

        $proposal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal is approved successfully'
        ]);
    }

}
