<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\ProjectShare;
use App\Models\SheetObject;
use App\Models\Spreadsht;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Models\UserInvoiceDetail;
use App\Models\UserInvoices;
use App\Models\UserProposalDetail;
use App\Models\UserProposals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        // get projects (private && shared)
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $company_info = User::find($this->user_id);
        $page_info = array(
            'name' => 'DASHBOARD',
            'user_id' => $this->user_id,
        );

        return view('dashboard', compact('projects', 'company_info', 'page_info'));
    }

    // get full address
    private function get_full_address($address_1, $address_2, $city, $state)
    {
        $address_1 = $address_1 ? $address_1 . ' ' : '';
        $address_2 = $address_2 ? $address_2 . ' ' : '';
        $city = $city ? $city . ' ' : '';
        $state = $state ? $state . ' ' : '';
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

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required'
        ]);

        // get geo location
        $address_1 = $request->input('street_address1');
        $address_2 = $request->input('street_address2');
        $city = $request->input('city');
        $state = $request->input('state');
        $address = $this->get_full_address($address_1, $address_2, $city, $state);
        $geo_location = $this->get_geo_location($address);

        Project::create([
            'user_id' => $this->user_id,
            'project_name' => $request->input('project_name'),
            'city' => $city,
            'state' => $state,
            'postal_code' => $request->input('postal_code'),
            'street_address_1' => $address_1,
            'street_address_2' => $address_2,
            'customer_name' => $request->input('customer_name'),
            'customer_email' => $request->input('customer_email'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_address_1' => $request->input('customer_street_address1'),
            'customer_address_2' => $request->input('customer_street_address2'),
            'customer_city' => $request->input('customer_state'),
            'customer_state' => $request->input('street_address1'),
            'customer_postal_code' => $request->input('customer_postal_code'),
            'geo_location' => $geo_location
        ]);

        return redirect()->route('dashboard.index')
            ->with('success', 'Project created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'update_project_name' => 'required'
        ]);

        // get geo location
        $address_1 = $request->update_street_address1;
        $address_2 = $request->update_street_address2;
        $city = $request->update_city;
        $state = $request->update_state;
        $address = $this->get_full_address($address_1, $address_2, $city, $state);
        $geo_location = $this->get_geo_location($address);

        $project = Project::find($id);
        $project->project_name = $request->update_project_name;
        $project->street_address_1 = $address_1;
        $project->street_address_2 = $address_2;
        $project->city = $city;
        $project->state = $state;
        $project->postal_code = $request->update_postal_code;
        $project->customer_address_1 = $request->update_customer_street_address1;
        $project->customer_address_2 = $request->update_customer_street_address2;
        $project->customer_name = $request->update_customer_name;
        $project->customer_email = $request->update_customer_email;
        $project->customer_phone = $request->update_customer_phone;
        $project->customer_city = $request->update_customer_city;
        $project->customer_state = $request->update_customer_state;
        $project->customer_postal_code = $request->update_customer_postal_code;
        $project->geo_location = $geo_location;

        $project->save();

        return redirect()->route('dashboard.index')
            ->with('success', 'Project updated successfully.');
    }

    public function update_company(Request $request)
    {
        $user = User::find($this->user_id);
        if ($user) {
            $user->company_name = $request->ucompany_name;
            $user->company_url = $request->ucompany_url;
            $user->street_address1 = $request->ustreet_address1;
            $user->street_address2 = $request->ustreet_address2;
            $user->city = $request->ucity;
            $user->state = $request->ustate;
            $user->postal_code = $request->upostal_code;

            if (isset($request->ucompany_logo)) {
                $file_type = $request->ucompany_logo->extension();
                if ($file_type === 'jpeg' || $file_type === 'png' || $file_type === 'jpg') {
                    $company_logo_name = time() . '.' . $file_type;
                    $request->ucompany_logo->move(public_path('img'), $company_logo_name);
                    $user->company_logo = '/img/' . $company_logo_name;
                } else {
                    $user->save();
                    return redirect()->route('dashboard.index')
                        ->with('error', 'Company logo type does not support.');
                }
            }
            $user->save();


            return redirect()->route('dashboard.index')
                ->with('success', 'Company updated successfully.');
        } else {
            return redirect()->route('dashboard.index')
                ->with('error', 'User information does not correct.');
        }
    }

    public function get_project_detail(Request $request)
    {
        $project_id = $request->project_id;
        $project = Project::find($project_id);
        return response()->json([
            'status' => 'success',
            'message' => 'Project information',
            'data' => $project
        ]);
    }

    public function destroy($id)
    {
        Project::find($id)->delete();
        Spreadsht::where('project_id', $id)->delete();
        ProjectSetting::where('project_id', $id)->delete();
        $sheet_ids = ProjectPdfSheet::where('project_id', $id)->pluck('id');
        SheetObject::whereIn('sheet_id', $sheet_ids)->delete();
        ProjectPdfSheet::where('user_id', $this->user_id)->where('project_id', $id)->delete();
        UserInvoiceDetail::where('user_id', $this->user_id)->where('project_id', $id)->delete();
        UserInvoices::where('user_id', $this->user_id)->where('project_id', $id)->delete();
        UserProposalDetail::where('user_id', $this->user_id)->where('project_id', $id)->delete();
        UserProposals::where('user_id', $this->user_id)->where('project_id', $id)->delete();

        return redirect()->route('dashboard.index')
            ->with('success', 'Project deleted successfully.');
    }


    public function get_customer_token(Request $request)
    {
        $notifyCustomerPortal = $request->notifyCustomerPortal;
        $user_id = $this->user_id;
        $project_id = $request->project_id;

        // get customer portal link
        $result = genCustomerPortalLink($user_id, $project_id);
        $url = $result['url'];
        $token = $result['token'];

        // send customer portal notification through email
        if ($notifyCustomerPortal) {
            $contractor_info = User::find($user_id);
            $data = [
                'portal_link' => $url,
                'pass_code' => $user_id,
                'contractor_name' => $contractor_info->display_name
            ];
            $customer_info = Project::find($project_id);
            $from = $contractor_info->company_name ? $contractor_info->company_name . ' via Takeoff Lite' : $contractor_info->display_name . ' via Takeoff Lite';
            if ($customer_info->customer_email) {
                Mail::send('mails.customer-portal-mail', $data, function ($messages) use ($customer_info, $from) {
                    $messages->to($customer_info->customer_email);
                    $messages->subject('Takeoff Lite');
                    $messages->from('email@takeofflite.com', $from);
                });
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Customer token generated successfully',
            'token' => $token,
            'url' => $url
        ]);
    }


    public function store_customer_email(Request $request)
    {
        $project_id = $request->project_id;
        $customer_email = $request->customer_email;
        $project = Project::find($project_id);
        $project->customer_email = $customer_email;
        $project->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer email is added successfully',
        ]);
    }

    // job share
    public function job_share(Request $request) {
        $share_project_number = $request->share_project_number;
        $share_receiver_user_id = trim($request->share_receiver_user_id);
        $share_sender_user_id = $this->user_id;

        $shareProject = ProjectShare::where('share_sender_user_id', $share_sender_user_id)
            ->where('share_receiver_user_id', $share_receiver_user_id)->where('share_project_number', $share_project_number)->first();
        if ($shareProject === null) {
            ProjectShare::create([
                'share_sender_user_id' => $share_sender_user_id,
                'share_receiver_user_id' => $share_receiver_user_id,
                'share_project_number' => $share_project_number
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Job is already shared with current code',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Job is shared successfully',
        ]);
    }
}
