<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Exception;

class MembershipController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index() {
        $page_info = ['name' => 'Membership'];
        $projects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get();

        return view('membership.index', compact('projects', 'page_info'));
    }

    public function manage_billing()
    {
        $user = User::find($this->user_id);
        $customer = $user->stripe_id;
        if (!$customer) {
            return back()->with('error', 'Customer not found in billing system');
        }

        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET')
        );

        try {
            $session = $stripe->billingPortal->sessions->create([
                'customer' => $customer,
                'return_url' => env('APP_URL') . '/membership',
            ]);
            return redirect($session->url);
        } catch (Exception $e) {
            // dd($e);
            return back()->with('error', 'Customer not found in billing system!');
        }
    }
}
