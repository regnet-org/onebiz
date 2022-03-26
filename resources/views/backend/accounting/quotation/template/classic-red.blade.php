@extends('layouts.app')

@section('content')
<style type="text/css">
@media all {
	.classic-table{
		width:100%;
		color: #000;
	}
	.classic-table td{
		color: #000;
	}
	
	#invoice-item-table th, #invoice-item-table td{
		border: 1px solid #bdc3c7 !important;
	}
	
	#invoice-payment-history-table{
		margin-bottom: 100px;
	}
	
	#invoice-payment-history-table th, #invoice-payment-history-table td{
		border: 1px solid #bdc3c7 !important;
	}
	
	#quotation-view{
	   padding:15px;	
	}
	
	.invoice-note{
		margin-bottom: 50px;
	}
	
	.table th {
	   background-color: #cb3e3b !important;
	   color: #FFF;
	}
	
	.border-top{
		border-top: 2px solid #cb3e3b !important;
	}
	
	.table td {
	   color: #2d2d2d;
	}
	
	.base_color{
		background-color: #cb3e3b !important;
	}
	
}
</style>  

<div class="row">
	<div class="col-12">
	
	    @include('backend.accounting.quotation.quotation-actions')
		@php $date_format = get_company_option('date_format','Y-m-d'); @endphp	

		<div class="card clearfix">
			
			<span class="panel-title d-none">{{ _lang('View Quotation') }}</span>
			
			<div class="card-body">
				<div id="quotation-view">
					<table class="classic-table">
						<tbody>
							 <tr class="top">
								<td colspan="2" class="pb-5">
									 <table class="classic-table">
										<tbody>
											 <tr>
												<td>
													<h3><b>{{ get_company_option('company_name') }}</b></h3>
													{{ get_company_option('address') }}<br>
													{{ get_company_option('email') }}<br>
													{!! get_company_option('vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_option('vat_id')).'<br>' : '' !!}
													{!! get_company_option('reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_option('reg_no')).'<br>' : '' !!}
														{!! get_company_option('iban')!= '' ? _lang('Bank Account').': '.clean(get_company_option('iban')).'<br>' : '' !!}
													{!! get_company_option('bank_name')!= '' ? _lang('Bank Name').': '.clean(get_company_option('bank_name')).'<br>' : '' !!}
												</td>
												<td class="float-right">
													<img src="{{ get_company_logo() }}" class="wp-100">
												</td>
											 </tr>
										</tbody>
									 </table>
								</td>
							 </tr>
							 
							 <tr class="information">
								<td colspan="2" class="border-top" class="pt-2">
									<div class="row">
										<div class="invoice-col-6 pt-3">
											<h5><b>{{ _lang('Quotation To') }}</b></h5>
											@if($quotation->related_to == 'contacts' && isset($quotation->client))
												 {{ $quotation->client->contact_name }}<br>
												 {{ $quotation->client->contact_email }}<br>
												 {!! $quotation->client->company_name != '' ? clean($quotation->client->company_name).'<br>' : '' !!}
												 {!! $quotation->client->address != '' ? clean($quotation->client->address).'<br>' : '' !!}
												 {!! $quotation->client->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->client->vat_id).'<br>' : '' !!}
												 {!! $quotation->client->reg_no != '' ? _lang('REG NO').': '.clean($quotation->client->reg_no).'<br>' : '' !!}
											  {!! $quotation->client->iban != '' ? _lang('Bank Account').': '.clean($quotation->client->iban).'<br>' : '' !!}      
											{!! $quotation->client->bank_name != '' ? _lang('Bank Name').': '.clean($quotation->client->bank_name).'<br>' : '' !!}     
											@elseif($quotation->related_to == 'leads' && isset($quotation->lead))	 
												 {{ $quotation->lead->name }}<br>
												 {{ $quotation->lead->email }}<br>
												 {!! $quotation->lead->company_name != '' ? clean($quotation->lead->company_name).'<br>' : '' !!}
												 {!! $quotation->lead->address != '' ? clean($quotation->lead->address).'<br>' : '' !!}
												 {!! $quotation->lead->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->lead->vat_id).'<br>' : '' !!}
												 {!! $quotation->lead->reg_no != '' ? _lang('REG NO').': '.clean($quotation->lead->reg_no).'<br>' : '' !!}
											@endif                        
										</div>
														
										<div class="invoice-col-6 pt-3">
											<div class="d-inline-block float-md-right">		
												<h5><b>{{ _lang('Quotation Details') }}</b></h5>
												<b>{{ _lang('Quotation') }} #:</b> {{ $quotation->quotation_number }}<br>
												<b>{{ _lang('Quotation Date') }}:</b> {{ date($date_format, strtotime( $quotation->quotation_date)) }}<br>
											</div>
										</div>
									</div>
								</td>
							 </tr>
						</tbody>
					</table>
					 
					<!--End Quotation Information-->
					@php $currency = currency(); @endphp
					<!--Quotation Product-->
					
					<div class="table-responsive">
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
								@foreach($quotation->quotation_items as $item)
									<tr id="product-{{ $item->item_id }}">
										 <td>{{ $item->item->item_name }}<br>{{ $item->item->item_type == 'product' ? $item->item->product->description : $item->item->service->description }}</td>
										 <td class="text-center">{{ $item->quantity }}</td>
										 <td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
										 <td class="text-right">{{ decimalPlace($item->discount, $currency) }}</td>
										 <td class="no-print">{{ strtoupper($item->tax_method) }}</td>
										 <td class="text-right">{{ decimalPlace($item->tax_amount, $currency) }}</td>
										 <td class="text-right">{{ decimalPlace($item->sub_total, $currency) }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<!--End Quotation Product-->	
					
					@php $base_currency = base_currency(); @endphp
					 
					<!--Summary Table-->
					<div class="invoice-summary-right">
						<table class="table" id="invoice-summary-table">
							<tbody>
								<tr>
									 <td>{{ _lang('Tax') }}</td>
									 <td class="text-right">
										<span>{{ decimalPlace($quotation->tax_total, $currency) }}</span>
										@if($quotation->related_to == 'contacts' && isset($quotation->client))
											@if($quotation->client->currency != $base_currency)
												<br><span>{{ decimalPlace(convert_currency($base_currency, $quotation->client->currency, $quotation->tax_total), currency($quotation->client->currency)) }}</span>	
											@endif
										@elseif($quotation->related_to == 'leads' && isset($quotation->lead))
											@if($quotation->lead->currency != $base_currency)
												<br><span>{{ decimalPlace(convert_currency($base_currency, $quotation->lead->currency, $quotation->tax_total), currency($quotation->lead->currency)) }}</span>	
											@endif
										@endif
									 </td>
								</tr>
								<tr>
									<td><b>{{ _lang('Grand Total') }}</b></td>
									<td class="text-right">
										<b>{{ decimalPlace($quotation->grand_total, $currency) }}</b>
										@if($quotation->related_to == 'contacts' && isset($quotation->client))
											@if($quotation->client->currency != $base_currency)
												<br><b>{{ decimalPlace($quotation->converted_total, currency($quotation->client->currency)) }}</b>
											@endif
										@elseif($quotation->related_to == 'leads' && isset($quotation->lead))
											@if($quotation->lead->currency != $base_currency)
												<br><b>{{ decimalPlace($quotation->converted_total, currency($quotation->lead->currency)) }}</b>
											@endif
										@endif
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!--End Summary Table-->
					 
					<div class="clearfix"></div>
	 
	 				 @if($quotation->invoice_created_name != '')
					<b>{{ _lang('Invoice Created By') }}</b> <br>
					<div class="invoice-note">{{ $quotation->invoice_created_name }}{{ $quotation->invoice_created_cnp ? ', '._lang('Invoice Created CNP').': '.$quotation->invoice_created_cnp : '' }} </div>
				@endif
				
				
					<!--Quotation Note-->
					@if($quotation->note  != '')
						<div class="invoice-note border-top pt-4">{!! nl2br($quotation->note) !!}</div>
					@endif
					<!--End Quotation Note-->
					 
					<!--Quotation Footer Text-->
					@if(get_company_option('quotation_footer')  != '')
						<div class="invoice-note border-top">{!! clean(get_company_option('quotation_footer')) !!}</div> 
					@endif
					<!--End Quotation Note-->
				</div>
			</div>
		</div>
    </div><!--End Classic Quotation Column-->
</div><!--End Classic Quotation Row-->
@endsection

