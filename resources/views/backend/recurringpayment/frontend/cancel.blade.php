@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-signin my-5">         
                @if(!request()->cancel_subscription_id)
                <div class="card-header text-center">
                  {{ _lang('Cancel Subscription') }}<br />
                  {{$description}} ({{ g_decimal_place(convert_currency(get_option('currency','RON'),get_option('netopia_currency','RON'),$amount), currency(get_option('netopia_currency','RON'))) }})
                </div>

                <div class="card-body" id="extend_membership">
                    
                    <h5 class="text-center"></h5>
                    <br>
                    
                    <form name="frmPaymentRedirect" method="post" action="{{route('recurringpayment.cancel')}}">
                    {{ csrf_field()}}
                    <input type="hidden" name="cancel_subscription_id" value="{{$cancel_subscription_id}}"/>
                    <br /><br />
                    <div class="row text-center">
                         <div class="col"><br /><input type="submit" name="submit" value="{{__('Cancel Subscription')}}" id="pay_button" class="submit"/></div>
                    </div>
                    
                    </form>
                @else
                <div class="card-header text-center">
                  {{ _lang('You have cancelled the subscription') }}<br />
                </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


