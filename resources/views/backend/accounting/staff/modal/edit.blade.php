<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('StaffController@update', $id) }}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-12">
		<div class="row">
			<div class="col-md-12">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Name') }}</label>						
				<input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
			 </div>
			</div>

			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Email') }}</label>						
				<input type="email" class="form-control" name="email" value="{{ $user->email }}" required readonly>
			 </div>
			</div>

			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Password') }}</label>						
				<input type="password" class="form-control" name="password">
			 </div>
			</div>
			
			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Confirm Password') }}</label>						
				<input type="password" class="form-control" name="password_confirmation">
			 </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Status') }}</label>						
				<select class="form-control select2 auto-select" data-selected="{{ $user->status }}" id="status" name="status" required>
				  <option value="1">{{ _lang('Active') }}</option>
				  <option value="0">{{ _lang('Inactive') }}</option>
				</select>
			  </div>
			</div>
			
			<div class="col-md-12">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Profile Picture') }} ( 300 X 300 {{ _lang('for better view') }} )</label>						
				<input type="file" class="dropify" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-max-file-size="1M"  data-max-width="300" data-default-file="{{ $user->profile_picture != "" ? asset('public/uploads/profile/'.$user->profile_picture) : '' }}" >
			 </div>
			</div>

						
			<div class="form-group">
			  <div class="col-md-12">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

