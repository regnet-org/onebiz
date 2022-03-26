<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Stripe\Stripe;
use Stripe\Charge;
use App\PaymentHistory;
use App\EmailTemplate;
use App\Package;
use App\Company;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PremiumMembershipMail;
use App\Utilities\Overrider;
use Auth;

use App\Services\NetopiaPaymentService;

class MembershipController extends Controller
{
	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    	date_default_timezone_set(get_option('timezone','Asia/Dhaka'));	
    }

	/**
	* Show the membership extend form.
	*
	* @return \Illuminate\Http\Response
	*/
    public function my_subscription()
    {
    	$user = Auth::user();
		return view('membership.subscription_details', compact('user'));
    }

   /**
	* Show the membership extend form.
	*
	* @return \Illuminate\Http\Response
	*/
    public function extend()
    {
    	$user = Auth::user();
		return view('membership.extend', compact('user'));
    }
	
	public function pay(Request $request)
    {
		$validator = Validator::make($request->all(), [
			'package' => 'required',
			'package_type' => 'required',
			'gateway' => 'required',
		]);
		
		if ($validator->fails()) {
			return redirect('membership/extend')->withErrors($validator)
												->withInput();				
		}
		
		
		$data = array();

		$package = Package::find($request->package);

		$data['title'] = "Buy {$package->package_name} Package";

		if($request->package_type == 'monthly'){
			$data['amount'] = $package->cost_per_month;
		    $data['custom'] = $request->package_type;
		}else{
			$data['amount'] = $package->cost_per_year;
		    $data['custom'] = $request->package_type;
		}
		
		//Create Pending Payment
		$payment = new PaymentHistory();
		$payment->company_id = company_id();
		$payment->title = $data['title'];
		$payment->method = "";
		$payment->currency = get_option('currency','RON');
		$payment->amount = $data['amount'];
		$payment->package_id = $package->id;
		$payment->package_type = $request->package_type;
		$payment->status = 'pending';
		$payment->save();
		
		$data['payment_id'] = $payment->id;
		
		if($request->gateway == "PayPal"){
			return view('membership.gateway.paypal',$data);
		}elseif($request->gateway == "Stripe"){
			return view('membership.gateway.stripe',$data);
		}elseif($request->gateway == "Netopia"){
	        $payment_service = new NetopiaPaymentService(['testmode'=>(get_option('netopia_testmode')=="Yes" ? "yes" : "no"), 'merchantid'=>get_option('netopia_merchantid'), 'currency'=>get_option('netopia_currency'), 'confirm_url' => 'netopia.payment_ipn','return_url' => 'netopia']);
	        list($data['payment_url'], $data['env_key'], $data['data']) = $payment_service->preparePaymentRedirection($user ?? false, $payment->id, $payment->amount, 0,  _lang('Extend Membership'));
			return view('membership.gateway.netopia',$data);
		}
    }
    
    public function netopia() {
    	$payment = PaymentHistory::find(request()->orderId);
    	if($payment->status=="paid")
			return redirect('/dashboard')->with('netopia_success', _lang('Thank you, You have sucessfully extended your membership. Please wait until you get confrimation email if you still see your membership has expired.'));
		else
			return redirect('membership/extend')->with('message', _lang('Payment Canceled !'));
    }
    
    public function netopia_ipn(Request $request) {
    	$payment_service = new NetopiaPaymentService(['testmode'=>(get_option('netopia_testmode')=="Yes" ? "yes" : "no"), 'merchantid'=>get_option('netopia_merchantid'), 'currency'=>get_option('netopia_currency'), 'confirm_url' => 'netopia.payment_ipn','return_url' => 'netopia']);
        list($payment_id, $order_status, $errorMessage, $errorCode, $objPmNotify, $confirm_response) =  $payment_service->confirmPayment($request);

        $payment_response = (isset($errorMessage) && trim($errorMessage)!='' ? $errorMessage : ($errorCode ?? ''));
        if(!isset($payment_id) || (int)$payment_id<0) //transaction response is failed
        	return false;
        elseif(!in_array($order_status, ['confirmed/captured', 'pending', 'open/preauthorized'])) //transaction is OK, but not confirmed
        	return $payment_service->confirmPaymentResponse($confirm_response);

        if(isset($payment_id)) {
        	$payment = PaymentHistory::find($payment_id);
    		$company = Company::find($payment->company_id);
            if($payment->created_at!=$payment->updated_at)
                return $payment_service->confirmPaymentResponse($confirm_response);

    		if($payment->package_type == 'monthly'){
    			$company->valid_to = date('Y-m-d', strtotime('+1 months'));
    		}else{
    			$company->valid_to = date('Y-m-d', strtotime('+1 year'));
    		}

    		$company->membership_type = 'member';
    		$company->last_email = NULL;
    		$company->package_id = $payment->package_id;

    		 //Update Package Details
            $package = $payment->package;
            $company->staff_limit = unserialize($package->staff_limit)[$company->package_type];
            $company->contacts_limit = unserialize($package->contacts_limit)[$company->package_type];
            $company->invoice_limit = unserialize($package->invoice_limit)[$company->package_type];
            $company->quotation_limit = unserialize($package->quotation_limit)[$company->package_type];
            $company->project_management_module = unserialize($package->project_management_module)[$company->package_type];
            $company->recurring_transaction = unserialize($package->recurring_transaction)[$company->package_type];
            $company->live_chat = unserialize($package->live_chat)[$company->package_type];
            $company->file_manager = unserialize($package->file_manager)[$company->package_type];
            $company->online_payment = unserialize($package->online_payment)[$company->package_type];
            $company->inventory_module = unserialize($package->inventory_module)[$company->package_type];

    		$company->save();

    		//Save payment History
    		$payment->method = "Netopia";
    		$payment->status = 'paid';
    		$payment->save();
    		
    		
    		//Replace paremeter
    		$user = User::where('company_id',$company->id)
    					->where('user_type','user')
    					->first();

    		$replace = array(
    			'{name}'=>$user->name,
    			'{email}'=>$user->email,
    			'{valid_to}' =>date('d M,Y', strtotime($company->valid_to)),
    		);

    		//Send email Confrimation
    		Overrider::load("Settings");
    		$template = EmailTemplate::where('name','premium_membership')->first();
    		$template->body = process_string($replace,$template->body);

    		try{
    			Mail::to($user->email)->send(new PremiumMembershipMail($template));
    		}catch (\Exception $e) {
    			//Nothing
    		}

            // notify admin
            $replace = array(
                '{email}'=>$user->email,
                '{company}'=>$company->business_name,
                '{amount}'=>$payment->amount,
            );
            //Send email notification to admin
            Overrider::load("Settings");
            $template = EmailTemplate::where('name','premium_membership_admin_notification')->first();
            $template->body = process_string($replace,$template->body);

            try{
                Mail::to(get_option('recurring_email'))->send(new PremiumMembershipMail($template));
            }catch (\Exception $e) {
                //Nothing
            }
        }
        
        return $payment_service->confirmPaymentResponse($confirm_response);
    }
	
	//PayPal Payment Gateway
	public function paypal($action){
		if($action == "return"){
			return redirect('/dashboard')->with('paypal_success', _lang('Thank you, You have sucessfully extended your membership. Please wait until you get confrimation email if you still see your membership has expired.'));
		}else if($action == "cancel"){
			return redirect('membership/extend')->with('message', _lang('Payment Canceled !'));
		}
	}
	

	public function paypal_ipn(Request $request)
	{
		$payment_id = $request->item_number;
		//$amount = $request->mc_gross;
		$amount = convert_currency(get_option('paypal_currency','USD'), get_option('currency','USD'), $request->mc_gross);
		 
		$payment = PaymentHistory::find($payment_id);
		//$increment = $payment->extend;
		
		if( $amount >= $payment->amount){

			$company = Company::find($payment->company_id);

			if($payment->package_type == 'monthly'){
				$company->valid_to = date('Y-m-d', strtotime('+1 months'));
			}else{
				$company->valid_to = date('Y-m-d', strtotime('+1 year'));
			}

			$company->membership_type = 'member';
			$company->last_email = NULL;
			$company->package_id = $payment->package_id;

			 //Update Package Details
	        $package = $payment->package;
	        $company->staff_limit = unserialize($package->staff_limit)[$company->package_type];
	        $company->contacts_limit = unserialize($package->contacts_limit)[$company->package_type];
	        $company->invoice_limit = unserialize($package->invoice_limit)[$company->package_type];
	        $company->quotation_limit = unserialize($package->quotation_limit)[$company->package_type];
	        $company->project_management_module = unserialize($package->project_management_module)[$company->package_type];
	        $company->recurring_transaction = unserialize($package->recurring_transaction)[$company->package_type];
	        $company->live_chat = unserialize($package->live_chat)[$company->package_type];
	        $company->file_manager = unserialize($package->file_manager)[$company->package_type];
	        $company->online_payment = unserialize($package->online_payment)[$company->package_type];
	        $company->inventory_module = unserialize($package->inventory_module)[$company->package_type];

			$company->save();

			//Save payment History
			$payment->method = "PayPal";
			$payment->status = 'paid';
			$payment->save();
			
			
			//Replace paremeter
			$user = User::where('company_id',$company->id)
						->where('user_type','user')
						->first();

			$replace = array(
				'{name}'=>$user->name,
				'{email}'=>$user->email,
				'{valid_to}' =>date('d M,Y', strtotime($company->valid_to)),
			);
			
			//Send email Confrimation
			Overrider::load("Settings");
			$template = EmailTemplate::where('name','premium_membership')->first();
			$template->body = process_string($replace,$template->body);

			try{
				Mail::to($user->email)->send(new PremiumMembershipMail($template));
			}catch (\Exception $e) {
				//Nothing
			}
			
        }		
    }
	
	//Stripe payment Gateway
	public function stripe_payment($payment_id){
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		
		Stripe::setApiKey(get_option('stripe_secret_key'));
 
        $token = request('stripeToken');
		
        $payment = PaymentHistory::find($payment_id);
 
        $charge = Charge::create([
            'amount' => round(convert_currency(get_option('currency','USD'), get_option('stripe_currency','USD'),($payment->amount * 100))),
            'currency' => get_option('stripe_currency','USD'),
            'description' => $payment->title,
            'source' => $token,
        ]);

		
		$company = Company::find($payment->company_id);
		if($payment->package_type == 'monthly'){
			$company->valid_to = date('Y-m-d', strtotime('+1 months'));
		}else{
			$company->valid_to = date('Y-m-d', strtotime('+1 year'));
		}
		$company->membership_type = 'member';
		$company->last_email = NULL;
		$company->package_id = $payment->package_id;

		//Update Package Details
        $package = $payment->package;
        $company->staff_limit = unserialize($package->staff_limit)[$company->package_type];
        $company->contacts_limit = unserialize($package->contacts_limit)[$company->package_type];
        $company->invoice_limit = unserialize($package->invoice_limit)[$company->package_type];
        $company->quotation_limit = unserialize($package->quotation_limit)[$company->package_type];
        $company->project_management_module = unserialize($package->project_management_module)[$company->package_type];
		$company->recurring_transaction = unserialize($package->recurring_transaction)[$company->package_type];
        $company->live_chat = unserialize($package->live_chat)[$company->package_type];
        $company->file_manager = unserialize($package->file_manager)[$company->package_type];
        $company->online_payment = unserialize($package->online_payment)[$company->package_type];
		$company->inventory_module = unserialize($package->inventory_module)[$company->package_type];

		$company->save();
		
		session(['valid_to' => $company->valid_to]);

		//Save payment History
		$payment->method = "Stripe";
		$payment->status = 'paid';
		$payment->save();
		

		//Replace paremeter
        $user = User::where('company_id',$company->id)
                    ->where('user_type','user')
                    ->first();
		$replace = array(
			'{name}' =>$user->name,
			'{email}' =>$user->email,
			'{valid_to}' =>date('d M,Y', strtotime($company->valid_to)),
		);
		
		//Send email Confrimation
		Overrider::load("Settings");
		$template = EmailTemplate::where('name','premium_membership')->first();
		$template->body = process_string($replace,$template->body);

		try{
			Mail::to($user->email)->send(new PremiumMembershipMail($template));
		}catch (\Exception $e) {
			//Nothing
		}

        return redirect('/dashboard')->with('success', _lang('Thank you, You have sucessfully extended your membership.'));
	}
	
}
