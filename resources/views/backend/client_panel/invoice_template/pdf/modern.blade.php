<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ get_option('site_title', 'ElitKit Invoice') }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style type="text/css">
@php include public_path('backend/assets/css/bootstrap.min.css') @endphp
@php include public_path('backend/assets/css/styles.css') @endphp

body { 
   -webkit-print-color-adjust: exact; !important;
   background: #FFF;
   font-size: 14px;
}
.classic-table{
	width:100%;
	color: #000;
}
.classic-table td{
	color: #000;
}

#invoice-item-table th{
	border: none;
}

#invoice-summary-table td{
	border: none !important;
}

#invoice-payment-history-table{
	margin-bottom: 50px;
}

#invoice-payment-history-table th{
	border: none !important;
}

#invoice-view{
   padding:15px 0px;	
}

.invoice-note{
	margin-bottom: 50px;
}

.table th {
   background-color: #008ae2 !important;
   color: #FFF;
}

.table td {
   color: #2d2d2d;
}

.base_color{
	background-color: #008ae2 !important;
}

.invoice-col-6{
  width: 50%;
  float:left;
  padding-right: 0px;
  padding-left: 0px;
}
			
</style>  
</head>

<body>

@php $base_currency = get_company_field( $invoice->company_id, 'base_currency', 'USD' ); @endphp
@php $date_format = get_company_field($invoice->company_id, 'date_format','Y-m-d'); @endphp	
{{-- @php $currency = currency($base_currency); @endphp --}}
@php $currency = $base_currency; @endphp

@if($invoice->related_to == 'contacts' && isset($invoice->client))
	@php $client_currency = $invoice->client->currency; @endphp
	@php $client = $invoice->client; @endphp
@else 
	@php $client_currency = $invoice->project->client->currency; @endphp
	@php $client = $invoice->project->client; @endphp
@endif

@if ($invoice->status == 'Canceled')
<div class="canceled_invoice">
{{ __('general.'.$invoice->status) }}
</div>	
@endif	


<div id="invoice-view" class="pdf">
	<div> 
		<table class="classic-table">
			<tbody>
				<tr class="top">
					<td colspan="2">
						<table class="classic-table">
							<tbody>
								 <tr>
									<td class="wp-100">
										<img src="{{ get_company_logo($invoice->company_id) }}" class="wp-100">  
									</td>
									<td>	
										<div class="text-left">
											<b class="fs-22">{{ get_company_field($invoice->company_id,'company_name') }}</b><br>
											{{ get_company_field($invoice->company_id,'address') }}<br>
											{{ get_company_field($invoice->company_id,'email') }}<br>
											{!! get_company_field($invoice->company_id,'vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_field($invoice->company_id,'vat_id')).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_field($invoice->company_id,'reg_no')).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'cod_vies')!= '' ? _lang('COD VIES').': '.clean(get_company_field($invoice->company_id,'cod_vies')).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'iban')!= '' ? _lang('Bank Account').': '.clean(get_company_field($invoice->company_id,'iban')).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'bank_name')!= '' ? _lang('Bank Name').': '.clean(get_company_field($invoice->company_id,'bank_name')).'<br>' : '' !!}

											@for ($i = 2; $i <= 5;  $i++)															
											{!! get_company_field($invoice->company_id,'iban'.$i)!= '' ? _lang('Bank Account').' '.$i.': '.clean(get_company_field($invoice->company_id,'iban'.$i)).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'bank_name'.$i)!= '' ? _lang('Bank Name').' '.$i.': '.clean(get_company_field($invoice->company_id,'bank_name'.$i)).'<br>' : '' !!}
											@endfor
										</div>
									</td>
									<td class="text-right">
										<img src="{{ asset('public/images/modern-invoice-bg.png') }}" class="wp-300">
									</td>
								 </tr>
							</tbody>
						</table>
					</td>
				</tr>
				 
				<tr class="information">
					<td colspan="2" class="pt-5">
						<div class="invoice-col-6 pt-3">
							 <h5><b>{{ _lang('Invoice To') }}</b></h5>	
							 {!! $client->company_name != '' ? clean($client->company_name).'<br>' : '' !!}
							 {{ $client->contact_name }}<br>
							 {{ $client->contact_email }}<br>
							 {!! $client->address != '' ? clean($client->address).'<br>' : '' !!}
							 {!! $client->vat_id != '' ? _lang('VAT ID').': '.clean($client->vat_id).'<br>' : '' !!}
							 {!! $client->reg_no != '' ? _lang('REG NO').': '.clean($client->reg_no).'<br>' : '' !!} 
							 {!! $client->iban != '' ? _lang('Bank Account').': '.clean($client->iban).'<br>' : '' !!}      
							 {!! $client->bank_name != '' ? _lang('Bank Name').': '.clean($client->bank_name).'<br>' : '' !!}                           
						</div>
							
						<!--Company Address-->
						<div class="invoice-col-6 pt-3">	
							<div class="d-inline-block float-md-right">
								<h5><b>{{ _lang('Invoice Details') }}</b></h5>
								
								<b>{{ _lang('Invoice') }} #:</b> {{ $invoice->invoice_number }}<br>
								
								<b>{{ _lang('Invoice Date') }}:</b> {{ date($date_format, strtotime( $invoice->invoice_date)) }}<br>
																				
								<b>{{ _lang('Due Date') }}:</b> {{ date($date_format, strtotime( $invoice->due_date)) }}<br>	
								@if ($invoice->status!='Unpaid')							
								<b>{{ _lang('Payment Status') }}:</b> {{ __('general.'.str_replace('_',' ',$invoice->status)) }}<br>
								@endif
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	 </div>
	 <!--End Invoice Information-->
	 <div class="clearfix"></div>
	 <!--Invoice Product-->
	 <div>
		<table class="table table-bordered mt-2" id="invoice-item-table">
			 <thead class="base_color">
				 <tr>
					 <th>{{ _lang('Name') }}</th>
					 <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
					 <th class="text-right">{{ _lang('Unit Cost') }}</th>
					 <th class="text-right wp-100">{{ _lang('Discount') }}</th>
					 <th class="no-print">{{ _lang('Tax method') }}</th>
					 <th class="text-right">{{ _lang('Tax') }}</th>
					 <th class="text-right">{{ _lang('Sub Total') }}</th>
				 </tr>
			 </thead>
			 <tbody id="invoice">
				 @foreach($invoice->invoice_items as $item)
					 <tr id="product-{{ $item->item_id }}">
						 <td>{{ $item->item->item_name }}<br>{{ $item->item->item_type == 'product' ? $item->item->product->description : $item->item->service->description }}</td>
						 <td class="text-center">{{ $item->quantity }}</td>
						 <td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
						 <td class="text-right">{{ decimalPlace($item->discoun, $currency) }}</td>
						 <td class="no-print">{{ strtoupper(__('general.'.$item->tax_method)) }}</td>
						 <td class="text-right">{{ decimalPlace($item->tax_amount, $currency) }}</td>
						 <td class="text-right">{{ decimalPlace($item->sub_total, $currency) }}</td>
					 </tr>
				 @endforeach
			 </tbody>
		</table>
	 </div>
	 <!--End Invoice Product-->	
	  @if($invoice->storno_invoice_id > 0)
	  @php $invoice_details_storno = invoice_details($invoice->storno_invoice_id); @endphp

{{ _lang('Invoice Storno') }} <b>#{{ $invoice_details_storno->invoice_number }}</b> {{ _lang('from date') }} {{ date($date_format,strtotime( $invoice_details_storno->invoice_date))  }}
@endif
	 <!--Summary Table-->
	 <div class="invoice-summary-right">
		<table class="table table-bordered" id="invoice-summary-table">
			<tbody>
				<tr>
					 <td>{{ _lang('Tax') }}</td>
					 <td class="text-right">
						@if($client_currency != $base_currency)
							<span>{{ decimalPlace(convert_currency($base_currency, $client_currency, $invoice->tax_total), $client_currency) }}</span><br>	
						@endif
						<span>{{ decimalPlace($invoice->tax_total, $currency) }}</span>
					 </td>
				</tr>
				<tr>
					 <td><b>{{ _lang('Grand Total') }}</b></td>
					 <td class="text-right">
						 @if($client_currency != $base_currency)
							<b>{{ decimalPlace($invoice->converted_total, $client_currency) }}</b><br>
						 @endif
						 <b>{{ decimalPlace($invoice->grand_total, $currency) }}</b>
					 </td>
				</tr>
				<tr>
					 <td>{{ _lang('Total Paid') }}</td>
					 <td class="text-right">
						@if($client_currency != $base_currency)
							<span>{{ decimalPlace(convert_currency($base_currency, $client_currency,$invoice->paid), $client_currency) }}</span><br>	
						@endif
						<span>{{ decimalPlace($invoice->paid, $currency) }}</span>
					 </td>
				</tr>
				@if($invoice->status != 'Paid')
					<tr>
						 <td>{{ _lang('Amount Due') }}</td>
						 <td class="text-right">
						 	<span>{{ date($date_format, strtotime( $invoice->due_date)) }}</span>
							{{-- <span>{{ decimalPlace(($invoice->grand_total - $invoice->paid), $currency) }}</span>
							@if($client_currency != $base_currency)
							<br><span>{{ decimalPlace(convert_currency($base_currency, $client_currency, ($invoice->grand_total - $invoice->paid)), currency($client_currency)) }}</span>	
							@endif --}}
						 </td>
					</tr>
				@endif
		    </tbody>
		</table>
	 </div>
	 <!--End Summary Table-->
	 
	 <div class="clearfix"></div>
	 
	 <!--Related Transaction-->
	 @if( ! $transactions->isEmpty() )
		<div>
			<table class="table table-bordered" id="invoice-payment-history-table">
				<thead class="base_color">
					<tr>
						<th>{{ _lang('Date') }}</th>
						<th>{{ _lang('Account') }}</th>
						<th class="text-right">{{ _lang('Amount') }}</th>
						<th class="text-right">{{ _lang('Base Amount') }}</th>
						<th>{{ _lang('Payment Method') }}</th>
					</tr>
				</thead>
				<tbody>  
				   @foreach($transactions as $transaction)
						<tr id="transaction-{{ $transaction->id }}">
							<td>{{ date($date_format, strtotime($transaction->trans_date)) }}</td>
							<td>{{ $transaction->account->account_title }}</td>
							<td class="text-right">{{ $transaction->account->account_currency.' '.decimalPlace($transaction->amount) }}</td>
							<td class="text-right">{{ $currency.' '.decimalPlace($transaction->amount) }}</td>
							<td>{{ $transaction->payment_method->name }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	 @endif
	 <!--END Related Transaction-->		
	 
	 @if($invoice->invoice_created_name != '')
						<b>{{ _lang('Invoice Created By') }}</b> <br>
						<div class="invoice-note">{{ $invoice->invoice_created_name }}{{ $invoice->invoice_created_cnp ? ', '._lang('Invoice Created CNP').': '.$invoice->invoice_created_cnp : '' }} </div>
					@endif
					
	 <!--Invoice Note-->
	 @if($invoice->note  != '')
		<div>
			<div class="invoice-note">{!! nl2br($invoice->note) !!}</div>
		</div> 
	 @endif
	 <!--End Invoice Note-->
	 
	 <!--Invoice Footer Text-->
	 @if(get_company_field($invoice->company_id,'invoice_footer') != '')
		<div>
			<div class="invoice-note">{!! clean(get_company_field($invoice->company_id,'invoice_footer')) !!}</div>
		</div> 
	 @endif
	 <!--End Invoice Note-->

	
	@include('backend.pdf-footer')
</div>
</body>
</html>


