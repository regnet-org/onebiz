<?php

namespace App\Http\Controllers\RecurringPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\RecurringPaymentClient;
use App\RecurringPaymentOrder;
use Validator;
use Illuminate\Validation\Rule;
use App\Utilities\Overrider;
use App\EmailTemplate;
use App\Mail\PremiumMembershipMail;
use App\Services\NetopiaPaymentService;

class ClientController extends Controller
{
	
	public function index($user_type = 'user')
    {
        
        $error_code = (new NetopiaPaymentService([]))->get_error_codes();
        if(request()->unpaid) {
            $title = _lang('Unpaid');
            $clients = RecurringPaymentOrder::select('recurring_payment_orders.id as orderid', 'recurring_payment_clients.*', 'recurring_payment_clients.status as status',  'recurring_payment_clients.id', 'recurring_payment_orders.has_child', 'recurring_payment_orders.id as rpo_id', 'recurring_payment_orders.recurring', 'recurring_payment_orders.status as recurring_status', 'recurring_payment_orders.payment_status', 'recurring_payment_orders.payment_response', "recurring_payment_orders.amount", "recurring_payment_orders.amount_recurring")
                ->where('token_id' , '!=', '')
                ->where('has_child' , '=', '0')
                ->where('recurring' , '=', '1')
                ->where('payment_response', 'like', 'error-%')
                ->where('recurring_payment_clients.status' , '=', '1')
                ->leftJoin('recurring_payment_clients', 'recurring_payment_clients.id', 'recurring_payment_orders.clientid')
                ->orderBy('orderid', 'desc')->get();
        } else {
            $title = _lang('Recurring Payments');
            // RecurringPaymentOrder::where('payment_status', 'Initialized')->whereNull('token_id')->delete();
            $clients = RecurringPaymentClient::select('recurring_payment_clients.*', 'recurring_payment_clients.status as status',  'recurring_payment_clients.id', 'recurring_payment_orders.has_child', 'recurring_payment_orders.id as rpo_id', 'recurring_payment_orders.recurring', 'recurring_payment_orders.status as recurring_status', 'recurring_payment_orders.payment_status', 'recurring_payment_orders.payment_response', "recurring_payment_orders.amount", "recurring_payment_orders.amount_recurring")->orderBy("recurring_payment_clients.id","desc")->orderBy("recurring_payment_clients.id","desc")->leftJoin('recurring_payment_orders', 'recurring_payment_clients.id', 'recurring_payment_orders.clientid')->groupBy('recurring_payment_clients.id')
                ->where(function ($query) {
                    $query->where('recurring_payment_orders.has_child', '=', 0)
                        ->orWhereNull('recurring_payment_orders.method');
                })->get();
        }
        // $myarray=$clients->toArray();echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
        return view('backend.recurringpayment.client.list',compact('clients', 'title', 'error_code'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
		if( ! $request->ajax()){
		   return view('backend.recurringpayment.client.create');
		}else{
           return view('backend.recurringpayment.client.modal.create');
		}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {	
		$validator = Validator::make($request->all(), [
			'client_name' => 'required|max:191',
			'email' => 'required|email|max:191',
			'phone' => 'nullable|max:20',
            'description' => 'required',
            // 'period' => 'required|integer',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect('recurring-payment/create')
							->withErrors($validator)
							->withInput();
			}			
		}
			

        $client= new RecurringPaymentClient();
	    $client->client_name = $request->input('client_name');
		$client->email = $request->input('email');
		$client->phone = $request->input('phone');
        $client->amount = $request->input('amount');
        $client->description = $request->input('description');
        // $client->period = $request->input('period');
        if($request->input('status')!==null)
		  $client->status = $request->input('status');
		$client->save();
        
        if($client->status ?? false) {
            $this->recurring_payment_request_notification($client);
        }
		if(! $request->ajax()){
           return redirect('recurring-payment/create')->with('success', _lang('Saved Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'),'data'=>$client]);
		}
        
    }
    
    public function recurring_payment_request_notification($client) {
        $replace = array(
            '{name}'=>$client->client_name,
            '{email}'=>$client->email,
            '{amount}'=>$client->amount,
            '{description}'=>$client->description,
            '{recurring_payment_link}'=>route('recurringpayment.pay.first', ['id' => encrypt($client->id)]),
        );

        //Send email Confrimation
        Overrider::load("Settings");
        $template = EmailTemplate::where('name','recurring_payment_request')->first();
        $template->body = process_string($replace,$template->body);

        try{
            \Mail::to($client->email)->send(new PremiumMembershipMail($template));
            $client->notified = 1;
            $client->save();
        }catch (\Exception $e) {
            //Nothing
        }
    }
	
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $client = RecurringPaymentClient::where("id",$id)->first();
        $client->recurring_status = RecurringPaymentOrder::where(["clientid"=>$id, 'has_child'=>0])->first()->recurring_status ?? 0;
		if(! $request->ajax()){
		   return view('backend.recurringpayment.client.edit',compact('client','id'));
		}else{
           return view('backend.recurringpayment.client.modal.edit',compact('client','id'));
		}  
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$validator = Validator::make($request->all(), [
			'client_name' => 'required|max:191',
			'email' => [
                'required',
                // Rule::unique('recurring_payment_clients')->ignore($id),
            ],
			'phone' => 'nullable|max:20',
            'description' => 'required',
            // 'period' => 'required|integer',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('clients.edit', $id)
							->withErrors($validator)
							->withInput();
			}			
		}
	   	

		$client = RecurringPaymentClient::where("id",$id)->first();
		$client->client_name = $request->input('client_name');
		$client->email = $request->input('email');
		$client->phone = $request->input('phone');
        $client->amount = $request->input('amount');
        $client->description = $request->input('description');
        // $client->period = $request->input('period');
		$client->status = $request->input('status');
		$client->save();
		
        if(!$client->notified) {
            if($client->status ?? false) {
                $this->recurring_payment_request_notification($client);
            }
        }
        
		if(! $request->ajax()){
           return redirect('clients')->with('success', _lang('Updated Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Sucessfully'),'data'=>$client]);
		}
	    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		$client = RecurringPaymentClient::where("id",$id);
        $client->delete();
        return redirect('recurring-payment/clients')->with('success',_lang('Removed Sucessfully'));
    }
    
    public function disable($id)
    {
        $client = RecurringPaymentClient::where("id",$id)->first();
        $client->status = 0;
        $client->save();
        return redirect('recurring-payment/clients')->with('success',_lang('Disabled Sucessfully'));
    }
    
    public function enable($id)
    {
        $client = RecurringPaymentClient::where("id",$id)->first();
        $client->status = 1;
        $client->save();
        return redirect('recurring-payment/clients')->with('success',_lang('Enabled Sucessfully'));
    }
    
    
}
