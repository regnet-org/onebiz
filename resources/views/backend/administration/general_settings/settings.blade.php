@extends('layouts.app')

@section('content')
    <div class="row">
          <div class="col-sm-3">
			  <ul class="nav flex-column nav-tabs settings-tab" role="tablist">
				  <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#general">{{ _lang('General Settings') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#system">{{ _lang('System Settings') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#email">{{ _lang('Email Settings') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#membership_settings">{{ _lang('Membership Settings') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#payment_gateway">{{ _lang('Payment Gateway') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#social_login">{{ _lang('Google Login') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#chat_settings">{{ _lang('Chat Settings') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#cron_jobs">{{ _lang('Cron Jobs') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#logo">{{ _lang('Logo and Favicon') }}</a></li>
				  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bt_settings">{{ _lang('BT settings') }}</a></li>
			  </ul>
		  </div>
		  
		  <div class="col-sm-9">
			  <div class="tab-content">
				  <div id="general" class="tab-pane active">
					  <div class="card">

						  <div class="card-body">
							  <h4 class="mb-4 header-title panel-title">{{ _lang('General Settings') }}</h4>
							  
							  <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
								{{ csrf_field() }}
								<div class="row">
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Company Name') }}</label>						
										<input type="text" class="form-control" name="company_name" value="{{ get_option('company_name') }}" required>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Site Title') }}</label>						
										<input type="text" class="form-control" name="site_title" value="{{ get_option('site_title') }}" required>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Phone') }}</label>						
										<input type="text" class="form-control" name="phone" value="{{ get_option('phone') }}" required>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Email') }}</label>						
										<input type="email" class="form-control" name="email" value="{{ get_option('email') }}" required>
									  </div>
									</div>

									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Timezone') }}</label>						
										<select class="form-control select2" name="timezone" required>
										<option value="">{{ _lang('-- Select One --') }}</option>
										{{ create_timezone_option(get_option('timezone')) }}
										</select>
									  </div>
									</div>
									
													
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Language') }}</label>						
										<select class="form-control select2" name="language" required>
											<option value="">{{ _lang('-- Select One --') }}</option>
											{{ load_language( get_option('language') ) }}
										</select>
									  </div>
									</div>

									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label">{{ _lang('Recurring Payment Email') }}</label>
											<input type="email" class="form-control" name="email" value="{{ get_option('recurring_email') }}" required>
										</div>
									</div>
	
									<div class="col-md-12">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Address') }}</label>						
										<textarea class="form-control" name="address" required>{{ get_option('address') }}</textarea>
									  </div>
									</div>

										
									<div class="col-md-12">
									  <div class="form-group">
										<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
									  </div>
									</div>
								</div>
							  </form>
						  </div>
					  </div>
				  </div>
				 
				  <div id="system" class="tab-pane">
					  <div class="card">

					  <div class="card-body">
						  <h4 class="mb-4 header-title panel-title">{{ _lang('System Settings') }}</h4>
						  
						  <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
							{{ csrf_field() }}
							<div class="row">
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Landing Page') }}</label>						
									<select class="form-control" name="website_enable" required>
										<option value="yes" {{ get_option('website_enable') == 'yes' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
										<option value="no" {{ get_option('website_enable') == 'no' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
									</select>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Website Language Dropdown') }}</label>						
									<select class="form-control" name="website_language_dropdown" required>
										<option value="yes" {{ get_option('website_language_dropdown') == 'yes' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
										<option value="no" {{ get_option('website_language_dropdown') == 'no' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
									</select>
								  </div>
								</div>

								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Backend Direction') }}</label>						
									<select class="form-control auto-select" data-selected="{{ get_option('backend_direction','ltr') }}" name="backend_direction" required>
										<option value="ltr">{{ _lang('LTR') }}</option>
										<option value="rtl">{{ _lang('RTL') }}</option>
									</select>
								  </div>
								</div>								
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Currency Converter') }}</label>						
									<select class="form-control" name="currency_converter" required>
										<option value="manual" {{ get_option('currency_converter') == 'manual' ? 'selected' : '' }}>{{ _lang('Manual') }}</option>
										<option value="fixer" {{ get_option('currency_converter') == 'fixer' ? 'selected' : '' }}>{{ _lang('Fixer API') }}</option>
									</select>
								  </div>
								</div>

								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Fixer API Key').' ('._lang('Currency Converter').')' }}</label>	
									<a href="https://fixer.io/" target="_blank" class="btn-link pull-right">{{ _lang('GET API KEY') }}</a>				
									<input type="text" class="form-control" name="fixer_api_key" value="{{ get_option('fixer_api_key') }}">
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Date Format') }}</label>						
									<select class="form-control auto-select" name="date_format" data-selected="{{ get_option('date_format','Y-m-d') }}" required>
										<option value="Y-m-d">{{ date('Y-m-d') }}</option>
										<option value="d-m-Y">{{ date('d-m-Y') }}</option>
										<option value="d/m/Y">{{ date('d/m/Y') }}</option>
										<option value="m-d-Y">{{ date('m-d-Y') }}</option>
										<option value="m.d.Y">{{ date('m.d.Y') }}</option>
										<option value="m/d/Y">{{ date('m/d/Y') }}</option>
										<option value="d.m.Y">{{ date('d.m.Y') }}</option>
										<option value="d/M/Y">{{ date('d/M/Y') }}</option>
										<option value="d/M/Y">{{ date('M/d/Y') }}</option>
										<option value="d M, Y">{{ date('d M, Y') }}</option>
									</select>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Time Format') }}</label>						
									<select class="form-control auto-select" name="time_format" data-selected="{{ get_option('time_format') }}" required>
										<option value="24">{{ _lang('24 Hours') }}</option>
										<option value="12">{{ _lang('12 Hours') }}</option>
									</select>
								  </div>
								</div>
		
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('File Manager File Type Supported') }}</label>						
									<input type="text" class="form-control" name="file_manager_file_type_supported" value="{{ get_option('file_manager_file_type_supported','png,jpg,jpeg') }}" required>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('File Manager Max Upload Size in MB') }}</label>						
									<input type="text" class="form-control" name="file_manager_max_upload_size" value="{{ get_option('file_manager_max_upload_size',2) }}" required>
								  </div>
								</div>							
									
								<div class="col-md-12">
								  <div class="form-group">
									<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
								  </div>
								</div>
							</div>
						  </form>
					  </div>
					  </div>
				  </div>
				 
				
				  <div id="email" class="tab-pane fade">
					<div class="card">
					  <div class="card-body">
						<h4 class="mb-4 header-title panel-title">{{ _lang('Email Settings') }}</h4>
						<form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
							{{ csrf_field() }}
							<div class="row">
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('Mail Type') }}</label>						
									<select class="form-control niceselect wide" name="mail_type" id="mail_type" required>
									  <option value="mail" {{ get_option('mail_type')=="mail" ? "selected" : "" }}>{{ _lang('PHP Mail') }}</option>
									  <option value="smtp" {{ get_option('mail_type')=="smtp" ? "selected" : "" }}>{{ _lang('SMTP') }}</option>
									  <option value="sendmail" {{ get_option('mail_type')=="sendmail" ? "selected" : "" }}>{{ _lang('Sendmail') }}</option>
									</select>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('From Email') }}</label>						
									<input type="text" class="form-control" name="from_email" value="{{ get_option('from_email') }}" required>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('From Name') }}</label>						
									<input type="text" class="form-control" name="from_name" value="{{ get_option('from_name') }}" required>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('SMTP Host') }}</label>						
									<input type="text" class="form-control smtp" name="smtp_host" value="{{ get_option('smtp_host') }}">
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('SMTP Port') }}</label>						
									<input type="text" class="form-control smtp" name="smtp_port" value="{{ get_option('smtp_port') }}">
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('SMTP Username') }}</label>						
									<input type="text" class="form-control smtp" autocomplete="off" name="smtp_username" value="{{ get_option('smtp_username') }}">
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('SMTP Password') }}</label>						
									<input type="password" class="form-control smtp" autocomplete="off" name="smtp_password" value="{{ get_option('smtp_password') }}">
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group">
									<label class="control-label">{{ _lang('SMTP Encryption') }}</label>						
									<select class="form-control smtp" name="smtp_encryption">
									   <option value="">{{ _lang('None') }}</option>
									   <option value="ssl" {{ get_option('smtp_encryption')=="ssl" ? "selected" : "" }}>{{ _lang('SSL') }}</option>
									   <option value="tls" {{ get_option('smtp_encryption')=="tls" ? "selected" : "" }}>{{ _lang('TLS') }}</option>
									</select>
								  </div>
								</div>
								
								<div class="col-md-12">
								  <div class="form-group">
									<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
								  </div>
								</div>
							</div>						
						</form>
					   </div>
					 </div>
				  </div>
				  
				  <div id="membership_settings" class="tab-pane fade">
					 <div class="card">
						<div class="card-body">
						   <h4 class="mb-4 header-title panel-title">{{ _lang('Membership Settings') }}</h4>
						   <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
								{{ csrf_field() }}
								<div class="row">
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Membership System') }}</label>						
										<select class="form-control" name="membership_system" required>
											<option value="enabled" {{ get_option('membership_system') == 'enabled' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
											<option value="disabled" {{ get_option('membership_system') == 'disabled' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
										</select>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Allow Sign Up') }}</label>						
										<select class="form-control" name="allow_singup" required>
											<option value="yes" {{ get_option('allow_singup') == 'yes' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
											<option value="no" {{ get_option('allow_singup') == 'no' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
										</select>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Email Verification') }}</label>						
										<select class="form-control" name="email_verification" required>
											<option value="enabled" {{ get_option('email_verification') == 'enabled' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
											<option value="disabled" {{ get_option('email_verification') == 'disabled' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
										</select>
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Trial Period') }}</label>						
										<select class="form-control" name="trial_period" required>
									        @for($i=0; $i<181; $i ++)
												<option value="{{ $i }}" {{ get_option('trial_period') == $i ? 'selected' : '' }}>{{ $i.' '._lang('days') }}</option>
											@endfor
										</select>
									  </div>
									</div>
									
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Currency') }}</label>						
										<select class="form-control select2 auto-select" data-selected="{{ get_option('currency','USD') }}" name="currency" id="currency" required>
											<option value="">{{ _lang('Select One') }}</option>
											{{ get_currency_list() }}
										</select>
									  </div>
									</div>
									
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Currency Position') }}</label>						
										<select class="form-control" name="currency_position" required>
											<option value="left" {{ get_option('currency_position') == 'left' ? 'selected' : '' }}>{{ _lang('Left') }}</option>
											<option value="right" {{ get_option('currency_position') == 'right' ? 'selected' : '' }}>{{ _lang('Right') }}</option>
										</select>
									  </div>
									</div>
									

									<div class="col-md-12">
									  <div class="form-group">
										<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
									  </div>
									</div>
								</div>
							</form>
						</div>
					 </div>
				  </div>
				  
				   <div id="payment_gateway" class="tab-pane fade">
					 <div class="card">
						<div class="card-body">
						   <h4 class="mb-4 header-title panel-title">{{ _lang('Payment Gateway') }}</h4>
						   <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
								{{ csrf_field() }}
								
								<h5 class="header-title">{{ _lang('PayPal') }}</h5>
								<div class="params-panel border border-dark p-3">
									<div class="row">
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('PayPal Active') }}</label>						
											<select class="form-control" name="paypal_active" required>
											   <option value="Yes" {{ get_option('paypal_active') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('paypal_active') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('PayPal Email') }}</label>						
											<input type="text" class="form-control" name="paypal_email" value="{{ get_option('paypal_email') }}">
										  </div>
										</div>

										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('PayPal Currency') }}</label>						
											<select class="form-control select2 auto-select" data-selected="{{ get_option('paypal_currency','USD') }}" name="paypal_currency" id="paypal_currency" required>
												<option value="USD">{{ _lang('U.S. Dollar') }}</option>
												<option value="AUD">{{ _lang('Australian Dollar') }}</option>
												<option value="BRL">{{ _lang('Brazilian Real') }}</option>
												<option value="CAD">{{ _lang('Canadian Dollar') }}</option>
												<option value="CZK">{{ _lang('Czech Koruna') }}</option>
												<option value="DKK">{{ _lang('Danish Krone') }}</option>
												<option value="EUR">{{ _lang('Euro') }}</option>
												<option value="HKD">{{ _lang('Hong Kong Dollar') }}</option>
												<option value="HUF">{{ _lang('Hungarian Forint') }}</option>
												<option value="INR">{{ _lang('Indian Rupee') }}</option>
												<option value="ILS">{{ _lang('Israeli New Sheqel') }}</option>
												<option value="JPY">{{ _lang('Japanese Yen') }}</option>
												<option value="MYR">{{ _lang('Malaysian Ringgit') }}</option>
												<option value="MXN">{{ _lang('Mexican Peso') }}</option>
												<option value="NOK">{{ _lang('Norwegian Krone') }}</option>
												<option value="NZD">{{ _lang('New Zealand Dollar') }}</option>
												<option value="PHP">{{ _lang('Philippine Peso') }}</option>
												<option value="PLN">{{ _lang('Polish Zloty') }}</option>
												<option value="GBP">{{ _lang('Pound Sterling') }}</option>
												<option value="SGD">{{ _lang('Singapore Dollar') }}</option>
												<option value="SEK">{{ _lang('Swedish Krona') }}</option>
												<option value="CHF">{{ _lang('Swiss Franc') }}</option>
												<option value="TWD">{{ _lang('Taiwan New Dollar') }}</option>
												<option value="THB">{{ _lang('Thai Baht') }}</option>
												<option value="TRY">{{ _lang('Turkish Lira') }}</option>
											</select>
										  </div>
										</div>
									</div>
								</div>
								
								<br>
								<h5 class="header-title">{{ _lang('Stripe') }}</h5>
								<div class="params-panel border border-dark p-3">
									<div class="row">
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Stripe Active') }}</label>						
											<select class="form-control" name="stripe_active" required>
											   <option value="Yes" {{ get_option('stripe_active') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('stripe_active') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Secret Key') }}</label>						
											<input type="text" class="form-control" name="stripe_secret_key" value="{{ get_option('stripe_secret_key') }}">
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Publishable Key') }}</label>						
											<input type="text" class="form-control" name="stripe_publishable_key" value="{{ get_option('stripe_publishable_key') }}">
										  </div>
										</div>


										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Stripe Currency') }}</label>						
											<select class="form-control select2 auto-select" data-selected="{{ get_option('stripe_currency','USD') }}" name="stripe_currency" id="stripe_currency" required>
												<option value="USD">{{ _lang('U.S. Dollar') }}</option>
												<option value="AUD">{{ _lang('Australian Dollar') }}</option>
												<option value="BRL">{{ _lang('Brazilian Real') }}</option>
												<option value="CAD">{{ _lang('Canadian Dollar') }}</option>
												<option value="CZK">{{ _lang('Czech Koruna') }}</option>
												<option value="DKK">{{ _lang('Danish Krone') }}</option>
												<option value="EUR">{{ _lang('Euro') }}</option>
												<option value="HKD">{{ _lang('Hong Kong Dollar') }}</option>
												<option value="HUF">{{ _lang('Hungarian Forint') }}</option>
												<option value="INR">{{ _lang('Indian Rupee') }}</option>
												<option value="ILS">{{ _lang('Israeli New Sheqel') }}</option>
												<option value="JPY">{{ _lang('Japanese Yen') }}</option>
												<option value="MYR">{{ _lang('Malaysian Ringgit') }}</option>
												<option value="MXN">{{ _lang('Mexican Peso') }}</option>
												<option value="NOK">{{ _lang('Norwegian Krone') }}</option>
												<option value="NZD">{{ _lang('New Zealand Dollar') }}</option>
												<option value="PHP">{{ _lang('Philippine Peso') }}</option>
												<option value="PLN">{{ _lang('Polish Zloty') }}</option>
												<option value="GBP">{{ _lang('Pound Sterling') }}</option>
												<option value="RON">{{ _lang('Romanian Leu') }}</option>
												<option value="SGD">{{ _lang('Singapore Dollar') }}</option>
												<option value="SEK">{{ _lang('Swedish Krona') }}</option>
												<option value="CHF">{{ _lang('Swiss Franc') }}</option>
												<option value="TWD">{{ _lang('Taiwan New Dollar') }}</option>
												<option value="THB">{{ _lang('Thai Baht') }}</option>
												<option value="TRY">{{ _lang('Turkish Lira') }}</option>
											</select>
										  </div>
										</div>

									</div>
								</div>
								
								<br /><h5 class="header-title">{{ _lang('Netopia') }}</h5>
								<div class="params-panel border border-dark p-3">
									<div class="row">
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Active') }}</label>						
											<select class="form-control" name="netopia_active" required>
											   <option value="Yes" {{ get_option('netopia_active') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('netopia_active') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Testmode') }}</label>						
											<select class="form-control" name="netopia_testmode" id="netopia_testmode" required>
											   <option value="Yes" {{ get_option('netopia_testmode') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('netopia_testmode') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Merchant ID') }}</label>						
											<input type="text" class="form-control" name="netopia_merchantid" value="{{ get_option('netopia_merchantid') }}">
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Currency') }}</label>						
											<select class="form-control select2 auto-select" data-selected="{{ get_option('netopia_currency','RON') }}" name="netopia_currency" id="netopia_currency" required>
											<option value="RON">{{ _lang('Romanian Leu') }}</option>
											</select>
										  </div>
										</div>
									<div id="netopia_testmode_cards_info">
                                    <table align="center" width="100%" border="0" cellspacing="0" cellpadding="5" class="table table-bordered">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Denumire card TESTMODE</th>
                                        <th>Numar card</th>
                                        <th>CVV</th>
                                        <th>Data expirarii</th>
                                        <th>Nume si prenume</th>
                                    </tr>
                                   </thead>
                                    <tr>
                                        <td>Card acceptat Garanti Bonuscard (*)</td>
                                        <td>9900004810225098</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Card acceptat Cardavantaj (*)</td>
                                        <td>99110059532258</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Card expirat</td>
                                        <td>9900541631437790</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Fonduri insuficiente</td>
                                        <td>9900518572831942</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>CVV incorect</td>
                                        <td>9900827979991500</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Verificare antifrauda</td>
                                        <td>9900005786662552</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Tranzactie nepermisa</td>
                                        <td>9900576270414197</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Eroare la banca emitenta</td>
                                        <td>9900130597497640</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    </table>
                                </div>


									</div>
								</div>
								
								<br /><h5 class="header-title">{{ _lang('Netopia Plati Recurente') }}</h5>
								<div class="params-panel border border-dark p-3">
									<div class="row">
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Plati Recurente Active') }}</label>						
											<select class="form-control" name="netopia_rp_active" required>
											   <option value="Yes" {{ get_option('netopia_rp_active') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('netopia_rp_active') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Plati Recurente Testmode') }}</label>						
											<select class="form-control" name="netopia_rp_testmode" id="netopia_rp_testmode" required>
											   <option value="Yes" {{ get_option('netopia_rp_testmode') == 'Yes' ? 'selected' : '' }}>{{ _lang('Yes') }}</option>
											   <option value="No" {{ get_option('netopia_rp_testmode') == 'No' ? 'selected' : '' }}>{{ _lang('No') }}</option>
											</select>
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Merchant ID') }}</label>						
											<input type="text" class="form-control" name="netopia_rp_merchantid" value="{{ get_option('netopia_rp_merchantid') }}">
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('API username') }}</label>						
											<input type="text" class="form-control" name="netopia_rp_api_username" value="{{ get_option('netopia_rp_api_username') }}">
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('API password') }}</label>						
											<input type="text" class="form-control" name="netopia_rp_api_password" value="{{ get_option('netopia_rp_api_password') }}">
										  </div>
										</div>
										
										<div class="col-md-4">
										  <div class="form-group">
											<label class="control-label">{{ _lang('Netopia Currency') }}</label>						
											<select class="form-control select2 auto-select" data-selected="{{ get_option('netopia_rp_currency','RON') }}" name="netopia_rp_currency" id="netopia_rp_currency" required>
											<option value="RON">{{ _lang('Romanian Leu') }}</option>
											</select>
										  </div>
										</div>
									<div id="netopia_rp_testmode_cards_info">
                                    <table align="center" width="100%" border="0" cellspacing="0" cellpadding="5" class="table table-bordered">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Denumire card TESTMODE</th>
                                        <th>Numar card</th>
                                        <th>CVV</th>
                                        <th>Data expirarii</th>
                                        <th>Nume si prenume</th>
                                    </tr>
                                   </thead>
                                    <tr>
                                        <td>Card acceptat Garanti Bonuscard (*)</td>
                                        <td>9900004810225098</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Card acceptat Cardavantaj (*)</td>
                                        <td>99110059532258</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Card expirat</td>
                                        <td>9900541631437790</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Fonduri insuficiente</td>
                                        <td>9900518572831942</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>CVV incorect</td>
                                        <td>9900827979991500</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Verificare antifrauda</td>
                                        <td>9900005786662552</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Tranzactie nepermisa</td>
                                        <td>9900576270414197</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    <tr>
                                        <td>Eroare la banca emitenta</td>
                                        <td>9900130597497640</td>
                                        <td>111</td>
                                        <td>Orice data din viitor</td>
                                        <td>Test Test</td>
                                    </tr>
                                    </table>
                                </div>


									</div>
								</div>

								<br>
								<div class="row">
									<div class="col-md-12">
									  <div class="form-group">
										<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
									  </div>
									</div>							
								</div>							
							</form>
						</div>
					 </div>
				  </div>
				  
				   <div id="social_login" class="tab-pane fade">
					 <div class="card">
						<div class="card-body">
							<h4 class="mb-4 header-title panel-title">{{ _lang('Google Login') }}</h4>
							<form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
								{{ csrf_field() }}
								<div class="row">		
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Google Login') }}</label>
										<select class="form-control select2" name="google_login" required>
											<option value="disabled" {{ get_option('google_login') == 'disabled' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
											<option value="enabled" {{ get_option('google_login') == 'enabled' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
										</select>
									  </div>
									</div>
									
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('GOOGLE CLIENT ID') }}</label>					<a href="https://console.developers.google.com/apis/credentials" target="_blank" class="btn-link pull-right">{{ _lang('GET API KEY') }}</a>	
										<input type="text" class="form-control" name="GOOGLE_CLIENT_ID" value="{{ get_option('GOOGLE_CLIENT_ID') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('GOOGLE CLIENT SECRET') }}</label>						
										<input type="text" class="form-control" name="GOOGLE_CLIENT_SECRET" value="{{ get_option('GOOGLE_CLIENT_SECRET') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('GOOGLE REDIRECT URL') }}</label>						
										<input type="text" class="form-control" value="{{ url('google/callback') }}" readOnly="true">
									  </div>
									</div>
									

									<div class="col-md-12">
									  <div class="form-group">
										<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
									  </div>
									</div>	
								</div>							
							</form>
						</div>
					 </div>
				  </div>
				  
				  <div id="chat_settings" class="tab-pane fade">
					 <div class="card">
						<div class="card-body">
						   <h4 class="mb-4 header-title panel-title">{{ _lang('Chat Settings') }}</h4>
						   <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
								{{ csrf_field() }}
								<div class="row">
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Messenger') }}</label>						
										<select class="form-control select2" name="live_chat" required>
											<option value="disabled" {{ get_option('live_chat') == 'disabled' ? 'selected' : '' }}>{{ _lang('Disable') }}</option>
											<option value="enabled" {{ get_option('live_chat') == 'enabled' ? 'selected' : '' }}>{{ _lang('Enable') }}</option>
										</select>
									  </div>
									</div>
									
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('PUSHER APP ID') }}</label>						
										<input type="text" class="form-control" name="PUSHER_APP_ID" value="{{ get_option('PUSHER_APP_ID') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('PUSHER APP KEY') }}</label>						
										<input type="text" class="form-control" name="PUSHER_KEY" value="{{ get_option('PUSHER_KEY') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('PUSHER APP SECRET') }}</label>						
										<input type="text" class="form-control" name="PUSHER_SECRET" value="{{ get_option('PUSHER_SECRET') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('CLUSTER') }}</label>						
										<input type="text" class="form-control" name="PUSHER_CLUSTER" value="{{ get_option('PUSHER_CLUSTER') }}">
									  </div>
									</div>
									
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Chat Max upload size MB') }}</label>						
										<input type="text" class="form-control" name="chat_max_upload_size" value="{{ get_option('chat_max_upload_size') }}">
									  </div>
									</div>
									
									<div class="col-md-6">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Chat File Type Supported') }}</label>						
										<input type="text" class="form-control" name="chat_file_type_supported" value="{{ get_option('chat_file_type_supported','png,jpg,jpeg,doc,pdf') }}" placeholder="Ex: png,jpeg,pdf,doc">
									  </div>
									</div>

									<div class="col-md-12">
									  <div class="form-group">
										<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
									  </div>
									</div>	
								</div>							
							</form>
						</div>
					 </div>
				  </div>
				  
				  <div id="cron_jobs" class="tab-pane fade">
					 <div class="card">
						<div class="card-body">
						   <h4 class="mb-4 header-title panel-title">{{ _lang('Cron Jobs') }}</h4>
						   <form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}">				         
								{{ csrf_field() }}
								<div class="row">
									<div class="col-md-12">
									  <div class="form-group">
										<label class="control-label">{{ _lang('Cron Jobs URL') }} (<b>{{ _lang('Run every 12 hours') }}</b>)</label>						
										<input type="text" class="form-control" value="wget -O- {{ url('console/run') }} >> /dev/null" readOnly>
									  </div>
									</div>
							
								</div>
						   </form>	
					   </div>
					 </div>
				  </div>
				  
				    <div id="logo" class="tab-pane fade">
					    <div class="card">
						<div class="card-body">
						   <h4 class="mb-4 header-title panel-title">{{ _lang('Logo and Favicon') }}</h4>

							<div class="row">
								<div class="col-md-6">
									<form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/upload_logo') }}" enctype="multipart/form-data">				         	
										{{ csrf_field() }}
										<div class="row">
											<div class="col-md-12">
											  <div class="form-group">
												<label class="control-label">{{ _lang('Upload Logo') }}</label>						
												<input type="file" class="form-control dropify" name="logo" data-max-file-size="8M" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-default-file="{{ get_logo() }}" required>
											  </div>
											</div>
											
											<br>
											<div class="col-md-12">
											  <div class="form-group">
												<button type="submit" class="btn btn-primary btn-block">{{ _lang('Upload') }}</button>
											  </div>
											</div>	
										</div>	
									</form>
								</div>

								<div class="col-md-6">
									<form method="post" class="appsvan-submit params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">	
										{{ csrf_field() }}
										<div class="row">	
											<div class="col-md-12">
											  <div class="form-group">
												<label class="control-label">{{ _lang('Upload Favicon') }} (PNG)</label>						
												<input type="file" class="form-control dropify" name="favicon" data-max-file-size="2M" data-allowed-file-extensions="png" data-default-file="{{ get_favicon() }}" required>
											  </div>
											</div>
											
											<br>
											<div class="col-md-12">
											  <div class="form-group">
												<button type="submit" class="btn btn-primary btn-block">{{ _lang('Upload') }}</button>
											  </div>
											</div>	
										</div>
                                    </form>										
								</div>									
							<div>
					    </div>
					 </div>
				  </div>
				  
			   </div>  
			</div>


			  <div id="bt_settings" class="tab-pane">
				  <div class="card">
				  <span class="d-none panel-title">{{ _lang('BT Settings') }}</span>

				  <div class="card-body">
					  
					  <form method="post" class="validate params-panel" autocomplete="off" action="{{ url('administration/general_settings/update') }}" enctype="multipart/form-data">
						{{ csrf_field() }}
						
						<div class="row">
							<div class="col-md-12">
							  <div class="form-group">
								<label class="control-label">{{ _lang('BT Client Name') }}</label>						
								<input type="text" class="form-control" name="bt_client_name" value="{{ get_option('bt_client_name') }}" required>
							  </div>
							</div>					
							
							@php
								/*
							@endphp
							<div class="col-md-6">
							  <div class="form-group">
								<label class="control-label">{{ _lang('Company Name') }}</label>						
								<input type="text" class="form-control" name="bt_company_name" value="{{ get_option('bt_company_name') }}">
							  </div>
							</div>					
							
							<div class="col-md-6">
							  <div class="form-group">
								<label class="control-label">{{ _lang('Contact Person') }}</label>						
								<input type="text" class="form-control" name="bt_contact_person" value="{{ get_option('bt_contact_person') }}">
							  </div>
							</div>
							
							<div class="col-md-6">
							  <div class="form-group">
								<label class="control-label">{{ _lang('Email') }}</label>						
								<input type="text" class="form-control" name="bt_email" value="{{ get_option('bt_email') }}" required>
							  </div>
							</div>
						
							<div class="col-md-6">
							  <div class="form-group">
								<label class="control-label">{{ _lang('Phone') }}</label>						
								<input type="text" class="form-control" name="bt_phone" value="{{ get_option('bt_phone') }}" required>
							  </div>
							</div>
							@php
								*/
							@endphp
							<div class="col-md-12">
							  <div class="form-group">
								<label class="control-label">{{ _lang('Redirect URL') }}</label>						
								<input type="text" class="form-control" name="bt_redirect_url" value="{{ get_option('bt_redirect_url') }}" required>
							  </div>
							</div>

								
							<div class="col-md-12">
							  <div class="form-group">
								<button type="submit" class="btn btn-primary">{{ _lang('Save Settings') }}</button>
							  </div>
							</div>

						</div>
					  </form>


						{{-- @if (get_option('redirect_uris')!='' && get_option('client_id')!='')
							<div class="row">
							<div class="col-md-12">
							  <div class="form-group text-center">
							  	<a href="https://apistorebt.ro/mga/sps/oauth/oauth20/authorize?response_type=code&client_id={{ get_option('client_id') }}&redirect_uri={{ get_option('redirect_uris') }}&scope=AIS:311de1574bd628b23cbe95ad66c881b2&state=statetest&code_challenge=Q8aVElfXiBwpn14GYiNZI_j2kee8OSHCt5DWTxbyBVs&code_challenge_method=S256" title="" class="btn btn-danger">{{ _lang('BT Authorization') }}</a>
							  </div>
							</div>
							</div>
						@endif --}}
				  </div>
				  </div>
			  </div>
			  

		  </div>
	  </div>
</div>
@endsection


@section('js-script')
<script language="Javascript" type="text/javascript">
(function($) {
    "use strict";
	$('[name="netopia_active"]').change(function () {
		$('#netopia_testmode').change();
	});
	$('#netopia_testmode').change(function () {
	    if ($(this).val() == 'Yes' && $('[name="netopia_active"]').val() == 'Yes')
	        $('#netopia_testmode_cards_info').show();
	    else
	        $('#netopia_testmode_cards_info').hide();
	});
	$('#netopia_testmode').change();
	
	$('[name="netopia_rp_active"]').change(function () {
		$('#netopia_rp_testmode').change();
	});
	$('#netopia_rp_testmode').change(function () {
	    if ($(this).val() == 'Yes' && $('[name="netopia_rp_active"]').val() == 'Yes')
	        $('#netopia_rp_testmode_cards_info').show();
	    else
	        $('#netopia_rp_testmode_cards_info').hide();
	});
	$('#netopia_rp_testmode').change();
	
})(jQuery);
</script>jquer
@endsection

