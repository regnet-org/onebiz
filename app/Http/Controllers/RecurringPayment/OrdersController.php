<?php

namespace App\Http\Controllers\RecurringPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\RecurringPaymentOrder as RecurringPaymentModel;

class OrdersController extends Controller
{
    /**
     * @var RecurringPaymentModel
     */
    private $recurringPaymentModel;

    public function __construct(RecurringPaymentModel $recurringPaymentModel)
    {
        $this->recurringPaymentModel = $recurringPaymentModel;
    }

    public function index(Request $request)
    {
        $clientId = $request->input('client_name');
        $orderStatus = $request->input('order_status');
        $clientStatus = $request->input('client_status') ?? 1;
        $startDate = $request->input('start_date') ?? date("Y-m-d",strtotime("-1 month"));
        $endDate = $request->input('end_date') ?? date("Y-m-d");
        $availableFilters = $this->recurringPaymentModel->getAvailableFilters($clientStatus);
        $availableFilters['client_id'] = $clientId ?? 0;
        $availableFilters['order_id'] = $orderStatus ?? "Select an option";
        $availableFilters['client_status_id'] = $clientStatus;
        $availableFilters['start_date'] = $startDate;
        $availableFilters['end_date'] = $endDate;
        $orders = $this->recurringPaymentModel->getFiltersOrders($clientId, $clientStatus, $orderStatus, $startDate, $endDate);

        return view('backend.recurringpayment.order.all',compact('orders', 'availableFilters'));
    }

    public function clientNameFilters($clientStatus)
    {
        return $this->recurringPaymentModel->getClientNameByStatus($clientStatus);
    }
}
