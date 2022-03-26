
@if($invoice->company->online_payment == 'Yes')

	@php $base_currency = get_company_field( $invoice->company_id, 'base_currency', 'USD' ); @endphp
	@php $currency = currency($base_currency); @endphp
	

	<div class="btn-group float-right">
		@if(get_company_field($invoice->company_id,'stripe_active') == 'yes' && $invoice->status != 'Paid')
		<form id="stripe-invoice" action="{{ url('client/make_payment/'.$invoice->id) }}" method="POST">
			{{ csrf_field() }}
			<script
				src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				data-key="{{ get_company_field($invoice->company_id,'stripe_publishable_key') }}"
				data-amount="{{ convert_currency($base_currency, get_company_field($invoice->company_id,'stripe_currency'), (round($invoice->grand_total-$invoice->paid) * 100)) }}"
				data-name="{{ _lang('Invoice Payment') }}"
				data-description="{{ _lang('Invoice Payment') }}"
				data-image="{{ get_company_logo($invoice->company_id) }}"
				data-currency="{{ get_company_field($invoice->company_id,'stripe_currency') }}"
				data-locale="auto">
			</script>
		</form>
		@endif
   </div>
   
   <div class="btn-group float-right">
		@if(get_company_field($invoice->company_id,'paypal_active') == 'Yes' && $invoice->status != 'Paid')
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="{{ get_company_field($invoice->company_id,'paypal_email') }}">
				<input type="hidden" name="item_name" value="{{ _lang('Invoice Payment') }}">
				<input type="hidden" name="item_number" value="{{ $invoice->invoice_number }}">
				<input type="hidden" name="amount" value="{{ convert_currency($base_currency, get_company_field($invoice->company_id,'paypal_currency'), ($invoice->grand_total - $invoice->paid)) }}">
				<input type="hidden" name="no_shipping" value="0">
				<input type="hidden" name="custom" value="{{ $invoice->id }}">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="{{ get_company_field($invoice->company_id,'paypal_currency') }}">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-BuyNowBF">
				
				<input type="hidden" name="return" value="{{ url('client/paypal/return/'.$invoice->id) }}"/>
				<input type="hidden" name="cancel_return" value="{{ url('client/paypal/cancel/'.$invoice->id) }}" />
				<input type="hidden" name="notify_url" value="{{ url('client/paypal_ipn') }}" />
				
				<button type="submit" name="submit" class="btn btn-primary btn-paypal" alt="PayPal - The safer, easier way to pay online.">
				  <span class="paypal-btn"><i class="fab fa-paypal"></i> {{ _lang('Pay Via PayPal') }}</span>
				</button>
			</form> 
		@endif
	</div>
@endif