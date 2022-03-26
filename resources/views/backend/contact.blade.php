@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title text-center pb-10">
                    <h4 class="title">{{ _lang('Get In touch') }}</h4>
                    <p class="text">{{ _lang('Stop wasting time and money designing and managing a website that does not get results. Happiness guaranteed!') }}</p>
                </div> <!-- section title -->
            </div>
        </div> <!-- row -->
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="alert alert-success d-none" id="contact-message"></div>

                <div class="contact-form">
                    <form id="contact-form" action="{{ url('contact/send_message') }}" method="post" data-toggle="validator">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="single-form form-group">
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ _lang('Your Name') }}" data-error="Name is required." required="required">
                                    <div class="help-block with-errors"></div>
                                </div> <!-- single form -->
                            </div>
                            <div class="col-md-6">
                                <div class="single-form form-group">
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ _lang('Your Email') }}" data-error="Valid email is required." required="required">
                                    <div class="help-block with-errors"></div>
                                </div> <!-- single form -->
                            </div>
                            <div class="col-md-12">
                                <div class="single-form form-group">
                                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="{{ _lang('Subject') }}" data-error="Subject is required." required="required">
                                    <div class="help-block with-errors"></div>
                                </div> <!-- single form -->
                            </div>

                            <div class="col-md-12">
                                <div class="single-form form-group">
                                    <textarea placeholder="{{ _lang('Your Mesaage') }}" name="message" data-error="Please, leave us a message." required="required">{{ old('message') }}</textarea>
                                    <div class="help-block with-errors"></div>
                                </div> <!-- single form -->
                            </div>
                            <p class="form-message"></p>
                            <div class="col-md-12">
                                <div class="single-form form-group text-center">
                                    <button type="submit" class="main-btn">{{ _lang('send message') }}</button>
                                </div> <!-- single form -->
                            </div>
                        </div> <!-- row -->
                    </form>
                </div> <!-- row -->
            </div>
        </div> <!-- row -->
    </div> <!-- conteiner -->
@endsection

@push('styles')
   <style>
   	.section-title .title {
    font-size: 50px;
    font-weight: 600;
    line-height: 55px;
    color: #121212;
}
.section-title .text {
    font-size: 16px;
    line-height: 24px;
    color: #6c6c6c;
    margin-top: 24px;
}
.contact-area {
  padding-top: 120px;
  padding-bottom: 120px; }
  @media only screen and (min-width: 768px) and (max-width: 991px) {
    .contact-area {
      padding-top: 100px;
      padding-bottom: 100px; } }
  @media (max-width: 767px) {
    .contact-area {
      padding-top: 80px;
      padding-bottom: 80px; } }

.form-group {
  margin: 0; }

p.form-message.success,
p.form-message.error {
  font-size: 16px;
  color: #121212;
  background: #cbced1;
  padding: 10px 15px;
  margin-left: 15px;
  margin-top: 15px; }

p.form-message.error {
  color: #f00; }

.contact-form .single-form {
  margin-top: 30px; }
  .contact-form .single-form textarea,
  .contact-form .single-form input {
    width: 100%;
    height: 56px;
    border: 1px solid #cbced1;
    border-radius: 5px;
    padding: 0 25px;
    background-color: #fff;
    font-size: 16px; }
    .contact-form .single-form textarea::placeholder,
    .contact-form .single-form input::placeholder {
      opacity: 1;
      color: #a4a4a4; }
    .contact-form .single-form textarea::-moz-placeholder,
    .contact-form .single-form input::-moz-placeholder {
      opacity: 1;
      color: #a4a4a4; }
    .contact-form .single-form textarea::-moz-placeholder,
    .contact-form .single-form input::-moz-placeholder {
      opacity: 1;
      color: #a4a4a4; }
    .contact-form .single-form textarea::-webkit-input-placeholder,
    .contact-form .single-form input::-webkit-input-placeholder {
      opacity: 1;
      color: #a4a4a4; }
  .contact-form .single-form textarea {
    height: 160px;
    padding-top: 15px;
    resize: none; }
  .contact-form .single-form .main-btn {
    border-radius: 50px;
    background-color: #0067f4;
    color: #fff; }
    .contact-form .single-form .main-btn:hover {
      background-color: #005ad5; }

      .contact-form .single-form .main-btn {
    border-radius: 50px;
    background-color: #0067f4;
    color: #fff;
}
.main-btn {
    display: inline-block;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    border: 2px solid transparent;
    padding: 0 32px;
    font-size: 16px;
    line-height: 48px;
    color: #0067f4;
    cursor: pointer;
    z-index: 5;
    -webkit-transition: all 0.4s ease-out 0s;
    -moz-transition: all 0.4s ease-out 0s;
    -ms-transition: all 0.4s ease-out 0s;
    -o-transition: all 0.4s ease-out 0s;
    transition: all 0.4s ease-out 0s;
    position: relative;
    text-transform: uppercase;
}
   </style>
@endpush

@section('js-script')
<script>
 //Submit Contact Form
    $(document).on('submit', '#contact-form', function( event ){
        
        event.preventDefault();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            beforeSend: function(){
                $("#preloader").fadeIn();
                $("#contact-form .main-btn").prop('disabled',true);
            },
            success : function(data){
                $("#preloader").fadeOut();
                $("#contact-form .main-btn").prop('disabled',false);           
                var json = JSON.parse(data);
                
                if (json['result'] == true){
                    $("#contact-message").removeClass('alert-danger').addClass('alert-success');
                    $("#contact-message").html('<p>'+ json['message'] +'</p>');
                    $("#contact-message").removeClass('d-none');
					$("#contact-form")[0].reset();
                } else {
                    $("#contact-message").removeClass('alert-success').addClass('alert-danger');
                    $("#contact-message").html('<p>'+ json['message'] +'</p>');
                    $("#contact-message").removeClass('d-none');
                }
            },
            complete: function(request, status, error){
               $("#preloader").fadeOut();
               $("#contact-form .main-btn").prop('disabled',false);
            }
        });
    });
</script>    
@endsection    