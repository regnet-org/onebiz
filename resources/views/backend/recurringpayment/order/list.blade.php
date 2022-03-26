@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
		<div class="card mt-2"> 
			<div class="card-body">
				<h4 class="mt-0 header-title d-none panel-title">{{ $title }}</h4>
				<table class="table table-bordered data-table">
					<thead>
					  <tr>
						<th>{{ _lang('ID') }}</th>
						<th>{{ _lang('Client Name') }}</th>
						<th>{{ _lang('Amount') }}</th>
						<th>{{ _lang('Description') }}</th>
						<th>{{ _lang('Attempts') }}</th>
						<th>{{ _lang('Payment date') }}</th>
						<th>{{ _lang('Payment status') }}</th>
					  </tr>
					</thead>
					<tbody>
					  
					  @foreach($orders as $order)
						<tr id="row_{{ $order->id }}">
							<td class='id'>{{ $order->id }}</td>
							<td class=''>{{\App\RecurringPaymentClient::where('id', $order->clientid)->first()->client_name ?? ''}}</td>
							<td class=''>{{ $order->amount }} RON</td>					
							<td class=''>{{ $order->description }}</td>					
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


