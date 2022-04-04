@extends('layouts.app')
@section('js-script')
    <script src="{{ asset('public/backend/assets/js/recurring_payment.js') }}"></script>
@stop
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card mt-2">
                    <div class="card-body">
                        <h4 class="mt-0 header-title d-none panel-title">{{ _lang('Recurring Payment Order List') }}</h4>
                        <div class="col-md-12">
                            <form method="post" autocomplete="off" action="{{ action('RecurringPayment\OrdersController@index') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="form-group col-2">
                                        <label class="control-label">{{ _lang('Client Name') }}</label>
                                        <select class="form-control select2 auto-select" data-selected="{{$availableFilters['client_id']}}" id="client_name" name="client_name">
                                            <option value="0">Select an option</option>
                                            @foreach($availableFilters['client_name'] as $client)
                                                <option value="{{$client->id}}">{{$client->client_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-2">
                                        <label class="control-label">{{ _lang('Client Status') }}</label>
                                        <select class="form-control select2 auto-select" data-selected="{{$availableFilters['client_status_id']}}" id="client_status" name="client_status">
                                            @foreach($availableFilters['client_status'] as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-2">
                                        <label class="control-label">{{ _lang('Payment status') }}</label>
                                        <select class="form-control select2 auto-select" data-selected="{{$availableFilters['order_id']}}" id="order_status" name="order_status">
                                            <option value="Select an option">Select an option</option>
                                            @foreach($availableFilters['order_status'] as $status)
                                                <option value="{{$status->payment_status}}">{{$status->payment_status}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-2">
                                        <label class="control-label">{{ _lang('From') }}</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" style="width: 100%; display: inline;" value="{{$availableFilters['start_date']}}">
                                    </div>
                                    <div class="form-group col-2">
                                        <label class="control-label">{{ _lang('To') }}</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" style="width: 100%; display: inline;" value="{{$availableFilters['end_date']}}">
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group" style="margin-top: 28px">
                                            <button type="submit" class="btn btn-primary">{{ _lang('Apply filters') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row">

{{--                            <div class="col-md-2">--}}
{{--                                    <form method="get" autocomplete="off" action="{{ action('RecurringPayment\OrdersController@index') }}" enctype="multipart/form-data">--}}
{{--                                        <div class="col-md-12">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <button type="submit" class="btn btn-danger">{{ _lang('Reset') }}</button>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </form>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>


                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{ _lang('ID') }}</th>
                                <th>{{ _lang('Client Name') }}</th>
                                <th>{{ _lang('Amount') }}</th>
                                <th>{{ _lang('Description') }}</th>
                                <th>{{ _lang('Created order date') }}</th>
                                <th>{{ _lang('Payment order date') }}</th>
                                <th>{{ _lang('Payment status') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($orders as $order)
                                <tr id="row_{{ $order->id }}">
                                    <td class='id'>{{ $order->id }}</td>
                                    <td class=''>{{ $order->client_name}}</td>
                                    <td class=''>{{ $order->amount }} RON</td>
                                    <td class=''>{{ $order->description }}</td>
                                    <td class=''>{{ $order->created_at }}</td>
                                    <td class=''>{{ $order->payment_date ?? $order->updated_at }}</td>
                                    <td class=''>{{ $order->payment_status }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </div>

@endsection
