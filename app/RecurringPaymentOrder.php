<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecurringPaymentOrder extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recurring_payment_orders';

    public function getFiltersOrders($clientId, $clientStatus, $orderStatus, $startDate, $endDate)
    {
        $orders = RecurringPaymentOrder::select('recurring_payment_orders.id', 'recurring_payment_orders.amount', 'recurring_payment_orders.description', 'recurring_payment_orders.payment_date', 'recurring_payment_orders.created_at', 'recurring_payment_orders.payment_status', 'recurring_payment_clients.client_name')
            ->join('recurring_payment_clients', 'recurring_payment_clients.id', 'recurring_payment_orders.clientid')
            ->orderBy('recurring_payment_orders.id', 'desc');
        if (!is_null($clientId) && $clientId != 0) {
            $orders = $orders->where('clientid', $clientId);
        }
        if (!is_null($clientStatus) && $clientStatus != "Select an option") {
            $orders = $orders->where('recurring_payment_clients.status', $clientStatus);
        }
        if (!is_null($orderStatus) && $orderStatus != "Select an option") {
            $orders = $orders->where('payment_status', $orderStatus);
        }
        if (!is_null($startDate)) {
            $orders = $orders->where('recurring_payment_orders.created_at', '>=', $startDate);
        }
        if (!is_null($endDate)) {
            $orders = $orders->where('recurring_payment_orders.created_at', '<=', date('Y-m-d', strtotime($endDate . ' +1 day')));
        }

        return $orders->get();
    }

    public function getAvailableFilters($clientStatus): array
    {
        $clientName = RecurringPaymentClient::select('id', 'client_name')
            ->orderBy('client_name', 'asc');
        if (!is_null($clientStatus)) {
            $clientName->where('status', '=', $clientStatus);
        }
        $clientName = $clientName->get();

        $orderStatus = RecurringPaymentOrder::select('id', 'payment_status')
            ->where('payment_status', 'NOT LIKE', '%old')
            ->groupBy('payment_status')
            ->orderBy('payment_status', 'asc')
            ->get();

        $clientStatus = [1 => "Enabled", 0 => "Disabled"];

        return ['client_name' => $clientName, 'order_status' => $orderStatus, 'client_status' => $clientStatus];
    }

    public function getClientNameByStatus($clientStatus)
    {
        return RecurringPaymentClient::select('id', 'client_name')
            ->orderBy('client_name', 'asc')
            ->where('status', '=', $clientStatus)
            ->get();
    }
}
