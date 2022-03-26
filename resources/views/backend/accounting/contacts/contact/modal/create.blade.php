
<form method="post" class="ajax-submit" autocomplete="off" action="{{ url('contacts') }}" enctype="multipart/form-data">
	{{ csrf_field() }}

	<div class="col-12">
		<div class="row">
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Profile Type') }}</label>						
				<select class="form-control select2" name="profile_type" required>
					<option value="Company" {{ old('profile_type') == 'Company' ? 'selected' : '' }}>{{ _lang('Company') }}</option>
					<option value="Individual" {{ old('profile_type') == 'Individual' ? 'selected' : '' }}>{{ _lang('Individual') }}</option>
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('VAT ID') }}</label>						
				<input type="text" class="form-control" name="vat_id" id="vat_id" value="{{ old('vat_id') }}" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Company Name') }}</label>						
				<input type="text" class="form-control" name="company_name" id="company_name" value="{{ old('company_name') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Contact Name') }}</label>						
				<input type="text" class="form-control" name="contact_name" id="contact_name" value="{{ old('contact_name') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Contact Email') }}</label>						
				<input type="text" class="form-control" name="contact_email" id="contact_email" value="{{ old('contact_email') }}">
			  </div>
			</div>
			
			

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Reg No') }}</label>						
				<input type="text" class="form-control" name="reg_no" id="reg_no" value="{{ old('reg_no') }}" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Bank Account') }}</label>						
				<input type="text" class="form-control" name="iban" id="iban" value="{{ old('iban') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Bank Name') }}</label>						
				<input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Contact Phone') }}</label>						
				<input type="text" class="form-control" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Country') }}</label>						
				<select class="form-control select2" name="country">
					<option value="">{{ _lang('Select Country') }}</option>
					{{ get_country_list( old('country') ) }}
				</select>
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Currency') }}</label>						
				<select class="form-control select2 auto-select" data-selected="{{ get_company_option('base_currency') }}" name="currency" id="currency" required>
				   <option value="">{{ _lang('Select Currency') }}</option>
				   {{ get_currency_list() }}
				</select>
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('contact_groups.create') }}" data-reload="false" data-title="{{ _lang('Add Contact Group') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Group') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="contact_groups" data-where="1" name="group_id" required>
					<option value="">{{ _lang('- Select Group -') }}</option>
				</select>
			 </div>
			</div>


			<div class="col-md-12">
			  <div class="form-group">
				<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
				<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
			  </div>
			</div>
		</div>
	</div>
 </form>
<script type="text/javascript">
	$('#vat_id').focusout(function() {
    var cuiValue = this.value;
    // console.log(cuiValue);
    var url = "{{ route('contact-autocomplete') }}?cui=" +cuiValue;
    $.get( url, function( data ) {
 		// console.log(data);
        $("#company_name").val( data.name );
        $("#reg_no").val( data.regcom );
        $("#contact_phone").val( data.phone );
        $("#address").val( data.address );
        $("#city").val( data.city );
        $("#state").val( data.county );
    });
})
</script>