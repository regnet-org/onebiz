<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ get_option('site_title', 'ElitKit Quotation') }}</title>
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
	
	#invoice-item-table th, #invoice-item-table td{
		border: 1px solid #bdc3c7 !important;
	}
	
	#invoice-payment-history-table{
		margin-bottom: 100px;
	}
	
	#invoice-payment-history-table th, #invoice-payment-history-table td{
		border: 1px solid #bdc3c7 !important;
	}
	
	#invoice-view{
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
	.invoice-col-6{
	  width: 50%;
	  float:left;
	  padding-right: 0px;
	  padding-left: 0px;
	}
	
</style>  
</head>

<body>  

@php $base_currency = get_company_field( $quotation->company_id, 'base_currency', 'USD' ); @endphp
@php $date_format = get_company_field($quotation->company_id, 'date_format','Y-m-d'); @endphp	
@php $currency = currency($base_currency); @endphp
	
<div id="quotation-view" class="pdf">
	 <div>
		 <table class="classic-table">
			<tbody>
				 <tr class="top">
					<td colspan="2" class="pb-5">
						 <table class="classic-table vt">
							<tbody>
								 <tr>
									<td>
										<h3><b>{{ get_company_field($quotation->company_id,'company_name') }}</b></h3>
										<div class="pt-2">
										{{ get_company_field($quotation->company_id,'address') }}<br>
										{{ get_company_field($quotation->company_id,'email') }}<br>
										{!! get_company_field($quotation->company_id,'vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_field($quotation->company_id,'vat_id')).'<br>' : '' !!}
										{!! get_company_field($quotation->company_id,'reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_field($quotation->company_id,'reg_no')).'<br>' : '' !!}
										{!! get_company_field($quotation->company_id,'cod_vies')!= '' ? _lang('COD VIES').': '.clean(get_company_field($quotation->company_id,'cod_vies')).'<br>' : '' !!}
										{!! get_company_field($quotation->company_id,'iban')!= '' ? _lang('Bank Account').': '.clean(get_company_field($quotation->company_id,'iban')).'<br>' : '' !!}
														{!! get_company_field($quotation->company_id,'bank_name')!= '' ? _lang('Bank Name').': '.clean(get_company_field($quotation->company_id,'bank_name')).'<br>' : '' !!}
											@for ($i = 2; $i <= 5;  $i++)															
											{!! get_company_field($quotation->company_id,'iban'.$i)!= '' ? _lang('Bank Account').' '.$i.': '.clean(get_company_field($quotation->company_id,'iban'.$i)).'<br>' : '' !!}
											{!! get_company_field($quotation->company_id,'bank_name'.$i)!= '' ? _lang('Bank Name').' '.$i.': '.clean(get_company_field($quotation->company_id,'bank_name'.$i)).'<br>' : '' !!}
											@endfor				
										</div>
									</td>
									<td class="text-right">
										<img src="{{ get_company_logo($quotation->company_id) }}" class="wp-100">
									</td>
								 </tr>
							</tbody>
						 </table>
					</td>
				 </tr>
				 
				 <tr class="information">
					<td colspan="2" class="border-top pt-2">
						<div class="invoice-col-6 pt-3">
							<h5><b>{{ _lang('Quotation To') }}</b></h5>
							@if($quotation->related_to == 'contacts' && isset($quotation->client))
								 {!! $quotation->client->company_name != '' ? clean($quotation->client->company_name).'<br>' : '' !!}
								 {{ $quotation->client->contact_name }}<br>
								 {{ $quotation->client->contact_email }}<br>
								 {!! $quotation->client->address != '' ? clean($quotation->client->address).'<br>' : '' !!}
								 {!! $quotation->client->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->client->vat_id).'<br>' : '' !!}
								 {!! $quotation->client->reg_no != '' ? _lang('REG NO').': '.clean($quotation->client->reg_no).'<br>' : '' !!}
								 	 {!! $quotation->client->iban != '' ? _lang('Bank Account').': '.clean($quotation->client->iban).'<br>' : '' !!}      
											 			{!! $quotation->client->bank_name != '' ? _lang('Bank Name').': '.clean($quotation->client->bank_name).'<br>' : '' !!}  
							 @elseif($quotation->related_to == 'leads' && isset($quotation->lead))	 
								 {!! $quotation->lead->company_name != '' ? clean($quotation->lead->company_name).'<br>' : '' !!}
								 {{ $quotation->lead->name }}<br>
								 {{ $quotation->lead->email }}<br>
								 {!! $quotation->lead->address != '' ? clean($quotation->lead->address).'<br>' : '' !!}
								 {!! $quotation->lead->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->lead->vat_id).'<br>' : '' !!}
								 {!! $quotation->lead->reg_no != '' ? _lang('REG NO').': '.clean($quotation->lead->reg_no).'<br>' : '' !!}
							 @endif                    
						</div>
							
						<!--Company Address-->
						<div class="invoice-col-6 pt-3">
							<div class="d-inline-block float-md-right">
								<h5><b>{{ _lang('Quotation Details') }}</b></h5>
								<b>{{ _lang('Quotation') }} #:</b> {{ $quotation->quotation_number }}<br>
								<b>{{ _lang('Quotation Date') }}:</b> {{ date($date_format, strtotime( $quotation->quotation_date)) }}<br>
							</div>
						</div>
					</td>
				 </tr>
			</tbody>
		 </table>
	 </div>
	 <!--End Quotation Information-->
	 <div class="clearfix"></div>
	 <!--Quotation Product-->
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
	 
	 <!--Summary Table-->
	 <div class="invoice-summary-right">
		<table class="table" id="invoice-summary-table">
			<tbody>
				<tr>
					 <td>{{ _lang('Tax') }}</td>
					 <td class="text-right">
						@if($quotation->related_to == 'contacts' && isset($quotation->client))
							@if($quotation->client->currency != $base_currency)
								<span>{{ decimalPlace(convert_currency($base_currency, $quotation->client->currency, $quotation->tax_total), currency($quotation->client->currency)) }}</span><br>	
							@endif
						@elseif($quotation->related_to == 'leads' && isset($quotation->lead))
							@if($quotation->lead->currency != $base_currency)
								<span>{{ decimalPlace(convert_currency($base_currency, $quotation->lead->currency, $quotation->tax_total), currency($quotation->lead->currency)) }}</span><br>	
							@endif
						@endif
						<span>{{ decimalPlace($quotation->tax_total, $currency) }}</span>
					 </td>
				</tr>
				<tr>
					 <td>{{ _lang('Grand Total') }}</td>
					 <td class="text-right">
						@if($quotation->related_to == 'contacts' && isset($quotation->client))
							@if($quotation->client->currency != $base_currency)
								<b>{{ decimalPlace($quotation->converted_total, currency($quotation->client->currency)) }}</b><br>
							@endif
						@elseif($quotation->related_to == 'leads' && isset($quotation->lead))
							@if($quotation->lead->currency != $base_currency)
								<b>{{ decimalPlace($quotation->converted_total, currency($quotation->lead->currency)) }}</b><br>
							@endif
						@endif
						<b>{{ decimalPlace($quotation->grand_total, $currency) }}</b>
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
		<div>
			<div class="invoice-note border-top pt-4">{!! nl2br($quotation->note) !!}</div>
		</div> 
	 @endif
	 <!--End Quotation Note-->
	 
	 <!--Quotation Footer Text-->
	 @if(get_company_field($quotation->company_id,'quotation_footer')  != '')
		<div>
			<div class="invoice-note">{!! clean(get_company_field($quotation->company_id,'quotation_footer')) !!}</div>
		</div> 
	 @endif
	 <!--End Quotation Note-->
	 @include('backend.pdf-footer')
</div>

</body>
</html>