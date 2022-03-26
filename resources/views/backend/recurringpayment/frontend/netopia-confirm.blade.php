@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-signin my-5">         
                <div class="card-header text-center">
                  {{ _lang('Plata') }}<br />
                  {{$description}}
                </div>

                <div class="card-body" id="extend_membership">
                    
                    <h5 class="text-center">{{ _lang('Payable Amount') }} : {{ g_decimal_place(convert_currency(get_option('currency','RON'),get_option('netopia_currency','RON'),$amount), currency(get_option('netopia_currency','RON'))) }}</h5>
                    <br>
                    
                    <form name="frmPaymentRedirect" method="post" action="{{$payment_url}}">
                    <input type="hidden" name="env_key" value="{{$env_key}}"/>
                    <input type="hidden" name="data" value="{{$data}}"/>
                    <div class="custom-control custom-checkbox mr-sm-2 pt-3">
                            {!! _lang('This payment require confirmation. By clicking this link you confrim the above payment.') !!}
                    </div>
                    <br /><br />
                    <p class="text-center">
                        <img src="{{ asset('public/images/plata_card_netopia.jpg') }}" />
                    </p>
                    <div class="row text-center">
                         <div class="col"><br /><input type="submit" name="submit" value="{{_lang('Confirm payment')}}" id="pay_button" class="submit" /></div>
                    </div>
                    
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/vendor/jquery-2.2.4.min.js') }}"></script>

{{-- <script language="Javascript" type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '#prec', function() {
        $("#pay_button").prop("disabled", !$(this).is(':checked'));
    });
});
</script> --}}
@endsection
