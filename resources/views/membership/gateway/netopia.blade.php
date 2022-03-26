@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-signin my-5">         
                <div class="card-header text-center">
                  {{ _lang('Extend Membership') }}
                </div>

                <div class="card-body" id="extend_membership">
                    
                    <h5 class="text-center">{{ _lang('Payable Amount') }} : {{ g_decimal_place(convert_currency(get_option('currency','RON'),get_option('netopia_currency','RON'),$amount), currency(get_option('netopia_currency','RON'))) }}</h5>
                    <br>
                    
                    <form name="frmPaymentRedirect" method="post" action="{{$payment_url}}">
                    <input type="hidden" name="env_key" value="{{$env_key}}"/>
                    <input type="hidden" name="data" value="{{$data}}"/>
                    <p>{{__('Payment::payment.You will be redirect to secure payment page of mobilpay.ro')}}</p>
                    <p>
                        <input type="image" src="{{ asset('public/images/plata_card_netopia.jpg') }}" />
                    </p>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script language="Javascript" type="text/javascript">
function redirect() {
    document.frmPaymentRedirect.submit();
}
setTimeout(redirect, 5000);
</script>
@endsection
