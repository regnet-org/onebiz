@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-signin my-5">         
                <div class="card-header text-center">
                  {{ _lang('Payment') }}<br />
                </div>

                <div class="card-body" id="extend_membership">
                    
                    <h5 class="text-center text-success">{{ _lang('Thank you for your payment') }}<br /></h5>
                    <br>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


