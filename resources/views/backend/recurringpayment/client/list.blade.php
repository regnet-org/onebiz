@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add User') }}" href="{{ route('clients.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
	    @if(!request()->unpaid)<a class="btn btn-primary btn-xs" data-title="{{ _lang('Card issue clients') }}" href="{{ route('clients.index', ['unpaid'=>1]) }}"><i class=""></i> {{ _lang('Card issue clients') }}</a>
	    @else<a class="btn btn-primary btn-xs" data-title="{{ _lang('All clients') }}" href="{{ route('clients.index') }}"><i class=""></i> {{ _lang('All clients') }}</a>@endif
	    
		<div class="card mt-2"> 
			<div class="card-body">
				<h4 class="mt-0 header-title d-none panel-title">{{ $title }}</h4>
				@if(get_option('netopia_rp_active')=="No")<h5 class="text-center text-danger">{{_lang('Netopia recurrent payment is inactive')}}</h5>@endif
				<table class="table table-bordered data-table">
					<thead>
					  <tr>
						<th>{{ _lang('ID') }}</th>
						<th>{{ _lang('Client Name') }}</th>
						<th>{{ _lang('Phone') }}</th>
						<th>{{ _lang('Amount') }} / {{ _lang('Payment date') }}</th>
						<th>{{ _lang('Total Paid') }}</th>
						<th>{{ _lang('Description') }}</th>
						<th>{{ _lang('Added') }}</th>
						<th>{{ _lang('Status') }}</th>
						<th>{{ _lang('Accepted') }}</th>
						<th class="text-center">{{ _lang('Action') }}</th>
					  </tr>
					</thead>
					<tbody>
					  
					  @foreach($clients as $client)
						<tr id="row_{{ $client->id }}">
							<td class='id'>{{ $client->id }}</td>
							<td class='name'>{{ $client->client_name }} @if($client->notified)<i class="far fa-bell text-primary" title="{{_lang('Notification sent')}}"></i>@endif</td>
							<td class='phone'>{{ $client->phone }}</td>
							<td class='amount'>@if(!is_null($client->amount_recurring)){{$client->amount_recurring}}@else {{$client->amount}} @endif RON {{_lang('monthly')}} @if(!is_null($client->last_payment))<small>({{$client->last_payment}})</small> @endif
							@if(!is_null($client->amount_recurring)) <br />{{_lang('Total to paid now')}} {{$client->amount}} RON 
							@endif
							@if(strpos($client->payment_response, "error-")!==FALSE)<p class="font-weight-bold text-danger"/>{{$error_code[str_replace("error-", "", $client->payment_response)] ?? ''}}</p>@endif / {{$client->payment_status}}
							</td>					
							<td class='amount'>{{ $client->total_paid }} @if(!is_null($client->payment_status) && $client->payment_status != 'paid')<span class="text-danger"></span>@endif</td>					
							<td class='description'>{{ \Str::limit($client->description, 100) }}</td>					
							<td class='created_at'>{{ $client->created_at }}</td>					
							<td class='status'>{!! $client->status == 1 ? clean(status(_lang('Active'), 'success')) : clean(status(_lang('Cancelled'), 'danger')) !!}</td>
							<td class='status'>{!! $client->status == 1 ? clean(status(_lang('Yes'), 'success')) : clean(status(_lang('No'), 'danger')) !!}</td>
							<td class="text-center">
							  <form action="{{ action('RecurringPayment\ClientController@destroy', $client->id) }}" method="post">
								@if(is_null($client->recurring) || (isset($client->payment_status) && $client->payment_response===NULL))<a href="{{ action('RecurringPayment\RacurringPaymentController@payRecurring', [encrypt($client->id), 'is_admin' => true]) }}" class="btn btn-outline-success btn-xs" target="_blank">{{ _lang('Pay') }}</a>@endif
								@if(!is_null($client->recurring))<a href="{{ action('RecurringPayment\OrderController@index', $client->id) }}" class="btn btn-outline-success btn-xs">{{ _lang('Payments') }}</a>@endif
								@if(is_null($client->last_payment) || $client->recurring_status)
									@if($client->status == 1)
									<a href="{{ action('RecurringPayment\ClientController@disable', $client->id) }}" class="btn btn-outline-primary btn-xs">{{ _lang('Cancel') }}</a>
									@else
									<a href="{{ action('RecurringPayment\ClientController@enable', $client->id) }}" class="btn btn-outline-primary btn-xs">{{ _lang('Enable') }}</a>
									@endif
								@endif
								{{-- @if(is_null($client->last_payment) || !$client->phone)<a href="{{ action('RecurringPayment\ClientController@edit', $client->id) }}" data-title="{{ _lang('Update User') }}" class="btn btn-outline-warning btn-xs ajax-modal">{{ _lang('Edit') }}</a>@endif --}}
								{{-- <a href="{{ action('RecurringPayment\ClientController@show', $client->id) }}" data-title="{{ _lang('View User') }}" class="btn btn-outline-primary btn-xs ajax-modal">{{ _lang('View') }}</a> --}}
								{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
								@if(get_option('netopia_rp_testmode')=="Yes")<button class="btn btn-outline-danger btn-xs btn-remove" type="submit">{{ _lang('Delete') }}</button>@endif
							  </form>
							</td>
						</tr>
					  @endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection


