@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
	<div class="card">
	<div class="d-none panel-title">{{ _lang('Create Order') }}</div>
	
		<form method="post" autocomplete="off" action="{{route('recurringpayment.create')}}" enctype="multipart/form-data">
		{{ csrf_field() }}
		<input type="hidden" name="clientid" value="{{request()->clientid}}" />
		<div class="row p-2">
			<div class="col-md-12">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Client Name') }} {{\App\RecurringPaymentClient::where('id', request()->clientid)->first()->client_name ?? ''}}</label>						
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Description') }}</label>						
				<input type="text" class="form-control" name="description" value="{{ old('description') }}" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>						
				<input type="text" class="form-control" name="amount" value="{{ old('amount') }}" required>
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
	  	</div>
 	</div>
</div>
@endsection
