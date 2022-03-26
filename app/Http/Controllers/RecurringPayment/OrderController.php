<?php

namespace App\Http\Controllers\RecurringPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\RecurringPaymentOrder;
use Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
	
	public function index($clientid = 0)
    {
        $orders = RecurringPaymentOrder::orderBy("id","desc");
        $orders = $orders->where(['clientid' => $clientid]);
        $orders = $orders->get();
        $title = _lang('Recurring Payment Order List');
        return view('backend.recurringpayment.order.list',compact('orders', 'title'));
    }
   
}