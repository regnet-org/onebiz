@extends('layouts.login')
<style>
 .stripe-button-el{width: 100% !important;}
</style>
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-signin my-5">			
				<div class="card-header text-center">
				  {{ _lang('Extend Membership') }}
				</div>

                <div class="card-body" id="extend_membership">

					<h5 class="text-center">{{ _lang('Payable Amount') }} : {{ g_decimal_place(convert_currency(get_option('currency','USD'),get_option('stripe_currency','USD'),$amount), currency(get_option('stripe_currency','USD'))) }}</h5>
					<form action="{{ url('membership/stripe_payment/'.$payment_id) }}" method="POST">
						{{ csrf_field() }}
						<script
							src="https://checkout.stripe.com/checkout.js" class="stripe-button"
							data-key="{{ get_option('stripe_publishable_key') }}"
							data-amount="{{ round(convert_currency(get_option('currency','USD'), get_option('stripe_currency','USD'),($amount * 100))) }}"
							data-name="{{ _lang('Extend Membership') }}"
							data-description="{{ $title }}"
							data-currency="{{ get_option('stripe_currency','USD') }}"
							data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
							data-locale="auto">
						</script>
					</form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection