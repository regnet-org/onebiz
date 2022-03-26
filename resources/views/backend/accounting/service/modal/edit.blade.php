<form method="post" class="validate ajax-submit" autocomplete="off" action="{{ action('ServiceController@update', $id) }}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				

	<div class="col-12">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Service Name') }}</label>						
					<input type="text" class="form-control" name="item_name" value="{{ $item->item_name }}" required>
				</div>
			</div>


			<div class="col-md-6">
			  <div class="form-group">
					<label class="control-label">{{ _lang('Currency') }}</label>						
					<select class="form-control select2" name="original_currency" id="original_currency" required>
						<option value="">{{ _lang('Select One') }}</option>
						{{ get_currency_list($item->service->original_currency) }}
					</select>
			  </div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Service Cost') }}</label>						
					<input type="text" class="form-control" name="original_cost" value="{{ $item->service->original_cost }}" required>
				</div>
			</div>


			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Service Cost').' '.currency() }}</label>						
					<input type="text" class="form-control" name="cost" value="{{ $item->service->cost }}" required readonly="readonly">
				</div>
			</div>


			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Tax Method') }}</label>						
					<select class="form-control" name="tax_method" required>
						<option value="exclusive" {{ $item->service->tax_method == 'exclusive' ? 'selected' : '' }}>{{ _lang('Exclusive') }}</option>
						<option value="inclusive" {{ $item->service->tax_method == 'inclusive' ? 'selected' : '' }}>{{ _lang('Inclusive') }}</option>
					</select>	
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Tax') }}</label>						
					<select class="form-control select2" name="tax_id">
						<option value="">{{ _lang('No Tax') }}</option>
						@foreach(App\Tax::where("company_id",company_id())->get() as $tax)
							<option value="{{ $tax->id }}" {{ $item->service->tax_id==$tax->id ? 'selected' : '' }}>{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Description') }}</label>						
					<textarea class="form-control" name="description">{{ $item->service->description }}</textarea>
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