<?php

namespace App\Http\Controllers\RecurringPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
use App\Services\NetopiaPaymentService;
use App\RecurringPaymentOrder;
use App\RecurringPaymentClient;
use App\Utilities\Overrider;
use App\EmailTemplate;
use App\Mail\PremiumMembershipMail;

class RacurringPaymentController extends Controller
{
    protected $merchantid;
    
	public function __construct(){
        $this->netopia_rp_active = get_option('netopia_rp_active');
        $this->merchantid = get_option('netopia_rp_merchantid');
        $this->recurring_payment_testmode = get_option('netopia_rp_testmode');
        $this->api_username = get_option('netopia_rp_api_username');
        $this->api_password = get_option('netopia_rp_api_password');
        $this->currency = get_option('netopia_rp_currency');

        $this->confirm_url = 'recurringpayment.ipn';
        $this->return_url = 'recurringpayment.thankyou';
    } 
    
    
    public function confirm(Request $request) {
        // echo encrypt(755);exit;
        try {
            $orderID = decrypt($request->id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect('/');
        }
        
        $order = RecurringPaymentOrder::findOrFail($orderID);
        $user = RecurringPaymentClient::where(['id'=>$order->clientid])->first();

        $data['description'] = $order->description;
        $data['amount'] = $order->amount;
        $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency, 'confirm_url' => $this->confirm_url,'return_url' => $this->return_url, 'api_username' => $this->api_username]);
            list($data['payment_url'], $data['env_key'], $data['data']) = $payment_service->preparePaymentRedirection($user, $order->id, $order->amount, 1, $data['description'], $order->token_id);
        return view('backend.recurringpayment.frontend.netopia-confirm', $data);
    }


    //aici se face redirect din mail pentru primul order;
    //AICI POT MARCA ORDERUL CA FIIND PRIMUL
    public function payRecurring($id_crypted = 0, Request $request) {
        if(get_option('netopia_rp_active')=="No")
            return redirect('/');
        
        // echo $id = encrypt($id_crypted);exit;
        try {
            $id = decrypt($id_crypted);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect('/');
        }

        $user = RecurringPaymentClient::where(['id'=>$id])->first();
        $order = RecurringPaymentOrder::where(['clientid'=>$id])->first();
        if(!$user->status || !is_null($user->last_payment) || ($order->retry > 1 && isset($order->payment_status) && ($order->payment_status != "Initialized" && $order->payment_status != "transaction not permitted to cardholder" )))
            return redirect('/');
        if($order == NULL) {
            $order = new RecurringPaymentOrder;
            $order->clientid = $id;
            $order->amount = $user->amount;
            $order->description = $user->description;
            $order->status = 1;
            $order->recurring = 1;
            $order->initial_order = 1;
            $order->payment_status = 'initialized';;
            $order->save();
        }
        $data['description'] = $order->description;
        $data['amount'] = $order->amount;
        $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency, 'confirm_url' => $this->confirm_url,'return_url' => $this->return_url, 'api_username' => $this->api_username]);
            list($data['payment_url'], $data['env_key'], $data['data']) = $payment_service->preparePaymentRedirection($user, $order->id, $order->amount, 1, $data['description']);
        return view('backend.recurringpayment.frontend.netopia',$data);
    }
    
    public function thankYou() {
        $payment = RecurringPaymentOrder::find(request()->orderId);
        if($payment && $payment->payment_status=="paid")
            return view('backend.recurringpayment.frontend.thankyou');
        else
            return view('backend.recurringpayment.frontend.failed');
    }
    
    
    public function send_notifications($client, $order) {
        $replace = array(
            '{name}'=>$client->client_name,
            '{email}'=>$client->email,
            '{amount}'=>$order->amount,
            '{description}'=>$order->description,
            '{unsubscribe_link}'=>route('recurringpayment.cancel', ['id' => encrypt($client->id)]),
        );

        //Send email Confrimation
        Overrider::load("Settings");
        $template = EmailTemplate::where('name','recurring_payment')->first();
        $template->body = process_string($replace,$template->body);

        try{
            \Mail::to($client->email)->send(new PremiumMembershipMail($template));
        }catch (\Exception $e) {
            //Nothing
        }

        // notify admin
        $replace = array(
            '{email}'=>$client->email,
            '{client_name}'=>$client->client_name,
            '{amount}'=>$order->amount,
            '{description}'=>$order->description,
        );
        //Send email notification to admin
        Overrider::load("Settings");
        $template = EmailTemplate::where('name','recurring_payment_admin_notification')->first();
        $template->body = process_string($replace,$template->body);

        try{
            \Mail::to(get_option('recurring_email'))->send(new PremiumMembershipMail($template));
        }catch (\Exception $e) {
            //Nothing
        }
    }


    public function ipn(Request $request) {
        $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency]);
        list($payment_id, $order_status, $errorMessage, $errorCode, $objPmNotify, $confirm_response) =  $payment_service->confirmPayment($request);
        $serviceLog = new \Monolog\Logger('payment_log');
        $serviceLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')));
        $serviceLog->info('Netopia IPN.', ['$payment_id' => $payment_id]);

        $payment_response = (isset($errorMessage) && trim($errorMessage)!='' ? $errorMessage : ($errorCode ?? ''));
        if(!isset($payment_id) || (int)$payment_id<0) {//transaction response is failed
            return false;
        } elseif(!in_array($order_status, ['confirmed/captured', 'pending', 'open/preauthorized'])) { //transaction is OK, but not confirmed

            $order = RecurringPaymentOrder::find($payment_id);
            $client = RecurringPaymentClient::find($order->clientid);

            $serviceLog->info('Netopia response.', ['payment_response' => $payment_response, 'payment_id ' => $payment_id]);

            if($order->method=="Netopia") { //it's a failed response, but allow only one notification (set methof Netopia), but inhibit all the followint rfailed emails
                return $payment_service->confirmPaymentResponse($confirm_response);
            }
            $order->method = "Netopia";
            $order->payment_response = $payment_response;
            $order->save();

            $error_number = (int)array_search($errorCode, $payment_service->get_error_codes());
            if($error_number< 34 || $error_number>38) {
                // notify admin
                $replace = array(
                    '{email}'=>$client->email,
                    '{client_name}'=>$client->client_name,
                    '{amount}'=>$order->amount,
                    '{description}'=>$order->description,
                    '{error_code}'=>$errorCode,
                );
                //Send email notification to admin
                Overrider::load("Settings");
                $template = EmailTemplate::where('name','recurring_payment_failed_admin_notification')->first();
                $template->body = process_string($replace,$template->body);

                try{
                    \Mail::to(get_option('recurring_email'))->send(new PremiumMembershipMail($template));
                }catch (\Exception $e) {
                    //Nothing
                }
            }

            return $payment_service->confirmPaymentResponse($confirm_response);
        }

        if(isset($payment_id)) {
            $order = RecurringPaymentOrder::find($payment_id);
            $client = RecurringPaymentClient::find($order->clientid);
            if($order->payment_status=='paid')
                return $payment_service->confirmPaymentResponse($confirm_response);

            $client->total_paid += $order->amount;
            $client->last_payment = \Carbon\Carbon::now()->format('Y-m-d');
            if ($order->initial_order) {
                $client->accepted_by_client = 1;
            }
            $client->save();

            //Save payment History
            $order->method = "Netopia";
            $order->payment_status = 'paid';
            $order->payment_response = $payment_response;
            $order->installments = (int)$objPmNotify->installments;
            $order->payment_date = \Carbon\Carbon::now();
            $order->initial_order = 0;
            if(isset($objPmNotify->token_id) && $objPmNotify->token_id!='') {
                $order->token_id = $objPmNotify->token_id ?? NULL;;
                $order->token_expiration_date = $objPmNotify->token_expiration_date ?? NULL;;
            }
            $order->save();

            $this->send_notifications($client, $order);

            if(!is_null($order->amount_recurring)) {
                $order->amount = $order->amount_recurring;
                $order->amount_recurring = NULL;
            }
            $order->save();
        }

        return $payment_service->confirmPaymentResponse($confirm_response);
    }

    public function addNewRecurringOrders()
    {
        $recurringClients = RecurringPaymentClient::select('*')
            ->where('status', '=', '1')
            ->where('accepted_by_client', '=', '1')
            ->get();
        $firstOfMonth = \Carbon\Carbon::now()->firstOfMonth()->toDateString();
        $serviceLog = new \Monolog\Logger('payment_log');
        $serviceLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')));

        foreach ($recurringClients as $recurringClient) {
            $lastRecurringOrder = RecurringPaymentOrder::select('*')
                ->where('clientid', '=', $recurringClient->id)
                ->orderBy('id', 'desc')
                ->first();
            if ($lastRecurringOrder && $lastRecurringOrder->created_at->toDateString() < $firstOfMonth) {
                try {
                    $order = $this->createOrder($recurringClient, $lastRecurringOrder->token_id);

                    $serviceLog->info('Add new recurring order.', ['client_id'=>$recurringClient->id, 'order_id' => $order->id]);
                } catch (\Exception $e) {
                    $serviceLog->error('New order could not be created.', [
                        'client_id'=> $recurringClient->id,
                        'error_message' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * @param $client
     * @param $tokenId
     * @return RecurringPaymentOrder
     */
    public function createOrder($client, $tokenId)
    {
        $order = new RecurringPaymentOrder;
        $order->amount = $client->amount;
        $order->description = $client->description;
        $order->clientid = $client->id;
        $order->status = 1;
        $order->recurring = 1;
        $order->payment_status = 'initialized';
        $order->payment_response = '';
        $order->token_id = $tokenId;

        $order->save();

        return $order;
    }

    public function cron()
    {
        $this->addNewRecurringOrders();

        if ($this->netopia_rp_active == "No") {
            return;
        }

        $recurringOrders = RecurringPaymentOrder::select('*', 'recurring_payment_clients.id as userid', 'recurring_payment_orders.id as orderid', "recurring_payment_orders.id", "recurring_payment_orders.created_at", "recurring_payment_orders.updated_at", "amount_recurring", "last_payment", "recurring_payment_orders.amount")
            ->where('recurring_payment_orders.token_id', '!=', '')
            ->where('recurring_payment_orders.status', '=', '1')
            ->where('recurring_payment_orders.recurring', '=', '1')
            ->where('recurring_payment_orders.payment_status', '=', 'initialized')
            ->where('recurring_payment_orders.initial_order', '=', '0')
            ->where('recurring_payment_clients.status', '=', '1')
            ->where('recurring_payment_clients.accepted_by_client', '=', '1')
            ->leftJoin('recurring_payment_clients', 'recurring_payment_clients.id', 'recurring_payment_orders.clientid')
            ->orderBy('orderid', 'desc')->get();

        $serviceLog = new \Monolog\Logger('payment_log');
        $serviceLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')));
        $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency, 'confirm_url' => $this->confirm_url,'return_url' => $this->return_url, 'api_username' => $this->api_username, 'api_password' => $this->api_password]);
        foreach ($recurringOrders as $clientOrder) {
            $order = RecurringPaymentOrder::find($clientOrder->id);
            try {
                $recurringPaymentResponse = $payment_service->makeRecurringPayment($clientOrder);
                $order->retry = $order->retry + 1;
                $order->save();
                $serviceLog->info('', ['orderID'=>$order->id, 'error_code'=>$recurringPaymentResponse->code, 'status'=>$payment_service->translate_error_code($recurringPaymentResponse->code)]);

                if ($recurringPaymentResponse->code > 0) {
                    $order->payment_response = 'error-'.$recurringPaymentResponse->code;
                    $order->save();

                    $serviceLog->error('', ['orderID'=>$order->id, 'error_code'=>$recurringPaymentResponse->code, 'status'=>$payment_service->translate_error_code($recurringPaymentResponse->code)]);

                    $this->recurring_payment_problem($clientOrder, $recurringPaymentResponse->code, $payment_service->translate_error_code($recurringPaymentResponse->code));
                } elseif($recurringPaymentResponse->action=='') {
                    $serviceLog->error('Payment isssue: No response got from Netopia.', ['orderID'=>$order->id]);
                }
            } catch(\Exception $e) {
                $serviceLog->error('Payment isssue: No response got from Netopia.', ['orderID'=>$order->id, 'error_message' => $e->getMessage()]);
            }
        }
    }

    
    public function cron_test() {
        if($this->netopia_rp_active=="No")
            exit();
        
        $process_month = date('Y-m');

        $recurring_orders = RecurringPaymentOrder::select('*', 'recurring_payment_clients.id as userid', 'recurring_payment_orders.id as orderid', "recurring_payment_orders.id", "recurring_payment_orders.created_at", "recurring_payment_orders.updated_at", "amount_recurring", "last_payment", "recurring_payment_orders.amount")
            ->where('token_id' , '!=', '')
            ->where('has_child' , '=', '0')
            ->where('recurring' , '=', '1')
            ->where('payment_status' , '!=', 'duplicate request')
            // Do not filter out these because we need to send emails // ->where('payment_response', 'not like', 'error-%')
            ->where('recurring_payment_clients.status' , '=', '1')
            ->leftJoin('recurring_payment_clients', 'recurring_payment_clients.id', 'recurring_payment_orders.clientid')
            ->orderBy('orderid', 'desc')->get();
        foreach ($recurring_orders as $client_order) {
            if(is_null($client_order->last_payment))
                continue;
            if($client_order->last_payment >= \Carbon\Carbon::now()->firstOfMonth()->toDateString())
                continue;

            if(strpos($client_order->payment_response, "error-")!==FALSE) {
                if($client_order->last_payment < \Carbon\Carbon::now()->subMonth()->firstOfMonth()->toDateString() && $client_order->updated_at < \Carbon\Carbon::now()->firstOfMonth()->toDateString()) { // there is a card error + user has not paid foe 2 months and order was updated last month => add amount to the total payment
                    $mod_order = RecurringPaymentOrder::find($client_order->id);
                    if(is_null($mod_order->amount_recurring))
                        $mod_order->amount_recurring = $mod_order->amount;
                    $mod_order->amount += $mod_order->amount_recurring; //add last invoice amount to hte current one
                    $mod_order->save();
                    $client_order->amount = $mod_order->amount;
                }
                
                $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency, 'confirm_url' => $this->confirm_url,'return_url' => $this->return_url, 'api_username' => $this->api_username, 'api_password' => $this->api_password]);
                $this->recurring_payment_problem($client_order, str_replace("error-", "", $client_order->payment_response), $payment_service->translate_error_code(str_replace("error-", "", $client_order->payment_response)));
                continue;
            }

            
            $new_user_order = new RecurringPaymentOrder;
            if($client_order->last_payment < \Carbon\Carbon::now()->subMonth()->firstOfMonth()->toDateString() && $client_order->updated_at < \Carbon\Carbon::now()->firstOfMonth()->toDateString()) {
                //if paid in the previous previous month and last updated last month then add the amount to full amount
                $new_user_order->amount_recurring = $client_order->amount;
                $client_order->amount = 2 *  $new_user_order->amount_recurring; //add last invoice amount to hte current one
                $new_user_order->amount = $client_order->amount;
            }

            $new_user_order->save();

            $new_user_order->amount = $client_order->amount;
            $new_user_order->description = $client_order->description;
            $new_user_order->clientid = $client_order->clientid;
            $new_user_order->status = 1;
            $new_user_order->recurring = 1;
            $new_user_order->payment_status = 'Initialized';
            $new_user_order->payment_response = '';
            $new_user_order->save();

            $payment_service = new NetopiaPaymentService(['testmode'=>($this->recurring_payment_testmode=="Yes" ? "yes" : "no"), 'merchantid'=>$this->merchantid, 'currency'=>$this->currency, 'confirm_url' => $this->confirm_url,'return_url' => $this->return_url, 'api_username' => $this->api_username, 'api_password' => $this->api_password]);

            try {
                $recurringPaymentResponse = $payment_service->makeRecurringPayment($client_order, $new_user_order);
                
                $infoLog = new \Monolog\Logger('payment_log');
                $infoLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')), \Monolog\Logger::INFO);
                $infoLog->info('', ['orderID'=>$new_user_order->id, 'error_code'=>$recurringPaymentResponse->code, 'status'=>$payment_service->translate_error_code($recurringPaymentResponse->code)]);
                
                // $myarray=$recurringPaymentResponse;echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
                if($recurringPaymentResponse->action=='confirmed') {
                    RecurringPaymentOrder::where(['id'=>$client_order->orderid])->update(['has_child'=>1]); //no more action on this order
                } elseif($recurringPaymentResponse->code > 0) {
                    RecurringPaymentOrder::where(['id'=>$client_order->orderid])->update(['has_child'=>1]); //no more action on old order
                    $new_user_order->token_id = $client_order->token_id;
                    $new_user_order->payment_response = 'error-'.$recurringPaymentResponse->code;
                    $new_user_order->save();
                    
                    $new_user_order_replicated = $new_user_order->replicate();
                    $new_user_order_replicated->save();
                    $this->recurring_payment_problem($client_order, $recurringPaymentResponse->code, $payment_service->translate_error_code($recurringPaymentResponse->code));
                    
                    $new_user_order->delete();
                } elseif($recurringPaymentResponse->action=='') {
                    \Log::error('Payment isssue: No response got from Netopia for order '.$client_order->orderid);
                    $new_user_order->delete();
                }
            } catch(Exception $e) {
                \Log::error('Payment isssue: No response got from Netopia for order '.$client_order->orderid.' - '.$e->getMessage());
                $new_user_order->delete();
            }
        }
        return;
    }

    
    public function recurring_payment_problem($clientOrder, $errorCode, $payment_issue) {
        if(in_array($errorCode, [34, 35, 36, 37, 38, ])) {
            $replace = array(
                '{name}'=>$clientOrder->client_name,
                '{email}'=>$clientOrder->email,
                '{amount}'=>$clientOrder->amount,
                '{description}'=>$clientOrder->description,
                '{recurring_payment_link}'=>route('recurringpayment.confirm', ['id' => encrypt($clientOrder->orderid)]),
            );

            //Send email Confrimation
            Overrider::load("Settings");
            $template = EmailTemplate::where('name','recurring_payment_confirmation')->first();
        } else {
            $replace = array(
                '{name}'=>$clientOrder->client_name,
                '{email}'=>$clientOrder->email,
                '{amount}'=>$clientOrder->amount,
                '{description}'=>$clientOrder->description,
                '{payment_issue}'=>$payment_issue,
            );

            //Send email Confrimation
            Overrider::load("Settings");
            $template = EmailTemplate::where('name','recurring_payment_problem')->first();
        }
        $template->body = process_string($replace,$template->body);

        try{
            \Mail::to($clientOrder->email)->send(new PremiumMembershipMail($template));
        }catch (\Exception $e) {
            //Nothing
        }
    }

    
    public function cancelSubscription($id_crypted=NULL) {
        try {
            $id = decrypt($id_crypted!=NULL ? $id_crypted : request()->cancel_subscription_id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect('/');
        }

        $data = $client = RecurringPaymentClient::where(['id'=>$id])->first();
        if(request()->cancel_subscription_id) {
            $order = RecurringPaymentOrder::where(['clientid'=>$id, 'has_child' =>0])->first();
            $data->status = 0;
            $data->save();
            
            RecurringPaymentOrder::where(['clientid'=>$id, 'has_child' =>0])->update([
                'status' => '0',
                'token_id' => '',
            ]);
            
            $replace = array(
                '{name}'=>$client->client_name,
                '{email}'=>$client->email,
                '{amount}'=>$order->amount,
                '{description}'=>$order->description,
                '{unsubscribe_link}'=>route('recurringpayment.cancel', ['id' => encrypt($client->id)]),
            );

            //Send email Confrimation
            Overrider::load("Settings");
            $template = EmailTemplate::where('name','recurring_payment_cancel')->first();
            $template->body = process_string($replace,$template->body);

            try{
                \Mail::to($client->email)->send(new PremiumMembershipMail($template));
            }catch (\Exception $e) {
                //Nothing
            }

            // notify admin
            $replace = array(
                '{email}'=>$client->email,
                '{client_name}'=>$client->client_name,
                '{amount}'=>$order->amount,
                '{description}'=>$order->description,
            );
            //Send email notification to admin
            Overrider::load("Settings");
            $template = EmailTemplate::where('name','recurring_payment_cancel_admin_notification')->first();
            $template->body = process_string($replace,$template->body);

            try{
                \Mail::to(get_option('recurring_email'))->send(new PremiumMembershipMail($template));
            }catch (\Exception $e) {
                //Nothing
            }
        } elseif($data->status==0)
            return redirect('/');
        
        $data->cancel_subscription_id = $id_crypted;
        return view('backend.recurringpayment.frontend.cancel',$data);
    }

}
