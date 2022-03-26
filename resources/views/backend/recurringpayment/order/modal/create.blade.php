<form method="post" class="ajax-submit" autocomplete="off" action="{{route('clients.store')}}" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="row p-2">
		<div class="col-md-12">
		  <div class="form-group">
			<label class="control-label">{{ _lang('Client Name') }}</label>						
			<input type="text" class="form-control" name="client_name" value="{{ old('client_name') }}" required>
		  </div>
		</div>
		
		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">{{ _lang('Email') }}</label>						
			<input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
		  </div>
		</div>

		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">{{ _lang('Phone') }}</label>						
			<input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
		  </div>
		</div>

		<div class="form-group">
		<label class="control-label">{{ _lang('Status') }}</label>						
		<select class="form-control select2 auto-select" data-selected="{{ old('status') }}" id="status" name="status" required>
		  <option value="1">{{ _lang('Active') }}</option>
		  <option value="0">{{ _lang('Inactive') }}</option>
		</select>
	  </div>	
	  
		<div class="col-md-12">
		  <div class="form-group">
			<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
			<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
		  </div>
		</div>
	</div>
</form>