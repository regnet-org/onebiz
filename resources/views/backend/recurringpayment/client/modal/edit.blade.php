<form method="post" class="ajax-submit" autocomplete="off" action="{{action('RecurringPayment\ClientController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	<div class="row p-2">
		<div class="col-md-12">
		 <div class="form-group">
			<label class="control-label">{{ _lang('Client Name') }}</label>						
			<input type="text" class="form-control" name="client_name" value="{{ $client->client_name }}" required>
		 </div>
		</div>
		
		<div class="col-md-6">
		 <div class="form-group">
			<label class="control-label">{{ _lang('Email') }}</label>						
			<input type="email" class="form-control" name="email" value="{{ $client->email }}" required>
		 </div>
		</div>
		
		<div class="col-md-6">
		 <div class="form-group">
			<label class="control-label">{{ _lang('Phone') }}</label>						
			<input type="phone" class="form-control" name="phone" value="{{ $client->phone }}" required>
		 </div>
		</div>
		
		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">{{ _lang('Amount') }}</label>						
			<input type="text" class="form-control" name="amount" value="{{ $client->amount}}" required>
		  </div>
		</div>
		
		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">{{ _lang('Description') }}</label>						
			<textarea  class="form-control" name="description" rows="4" cols="44" required>{{ $client->description }}</textarea>
		  </div>
		</div>

		@if(is_null($client->last_payment) || $client->recurring_status)
		<div class="form-group">
		<label class="control-label">{{ _lang('Status') }}</label>						
		<select class="form-control select2 auto-select" data-selected="{{ $client->status }}" id="status" name="status" required>
		  <option value="1">{{ _lang('Active') }}</option>
		  <option value="0">{{ _lang('Inactive') }}</option>
		</select>
	  </div>	
	  @endif
	  
		<div class="form-group">
		  <div class="col-md-12">
			<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
		  </div>
		</div>
	</div>	
</form>
