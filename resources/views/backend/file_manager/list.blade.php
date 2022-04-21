@extends('layouts.app')

@section('content')

@php $date_format = get_company_option('date_format','Y-m-d'); @endphp	
@php $time_format = get_company_option('time_format',24) == '24' ? 'H:i' : 'h:i A'; @endphp	

<div class="row">
	<div class="col-12">
		@if($back == true) 
			<a class="btn btn-warning btn-xs" href="javascript:history.back();"><i class="far fa-arrow-alt-circle-left"></i> {{ _lang('Back') }}</a>&nbsp;
		@endif
		@if($data_allow_upload_after_10)
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('New File') }}" href="{{ url('file_manager/create/'.request()->route('parent_id')) }}"><i class="fas fa-cloud-upload-alt"></i> {{ _lang('New File') }}</a>&nbsp;
		<a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('New Folder') }}" href="{{ url('file_manager/create_folder/'.request()->route('parent_id')) }}"><i class="fas fa-folder-plus"></i> {{ _lang('New Folder') }}</a>
		@endif
		@if($back == true) 
			<a class="btn btn-success btn-xs" href="{{ url('file_manager') }}"><i class="fas fa-sitemap"></i> {{ _lang('Root') }}</a>
		@endif	
		
		<form method="post" name="fm_search" id="fm_search" class="validate" autocomplete="off" action="{{ route('file_manager.search') }}">
		{{ csrf_field() }}
		<div class="row">	
			<div class="col-md-3">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Invoice Number') }}</label>						
					<input type="text" class="form-control" name="invoice_number" id="invoice_number" value="{{request()->invoice_number}}">
				  </div>
				</div>

			<div class="col-md-3 d-none" id="contacts">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Client') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-table="contacts" data-where="1" name="client_id" id="client_id">
					<option value="">{{ _lang('Select One') }}</option>
					{{ create_option("contacts","id","contact_name", request()->client_id, array("company_id="=>company_id())) }}
				</select>
			  </div>
			</div>
			
		{{-- 	<div class="col-md-3">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Invoice Date') }}</label>						
					<input type="text" class="form-control datepicker" name="invoice_date" id="invoice_date" value="{{request()->invoice_date}}">
				  </div>
				</div> --}}
			<div class="col-md-3">
				  <div class="form-group">
				  	&nbsp;<br />
					<input type="submit" name="submit" value="Search" class="submit" />
					&nbsp;&nbsp;
					<a href="#" id="reset_form">Reset</a>
				  </div>
				</div>
    	</div>
		</form>
		
		<div class="card mt-2 clearfix">
			<span class="d-none panel-title">{{ _lang('File Manager') }}</span>

			<div class="card-body">
			 <table class="table table-striped file-manager-table data-table">
				<thead>
				  <tr>
					<th>{{ _lang('File') }}</th>
					<th>{{ _lang('Created') }}</th>
					<th>{{ _lang('Modified') }}</th>
					<th class="text-center">{{ _lang('Action') }}</th>
				  </tr>
				</thead>
				<tbody>
				  
				  @foreach($filemanagers as $filemanager)
				  <tr id="row_{{ $filemanager->id }}">
					@if($filemanager->is_dir == 'yes')
						<td class='name'><i class="far fa-folder"></i> <a href="{{ url('file_manager/directory/'.encrypt($filemanager->id)).($filemanager->name=="Modele acte" ? "?modele_acte=1" : "") }}">{{ $filemanager->name }}</a></td>
					@else
						<td class='name'><i class="far {{ file_icon($filemanager->mime_type) }}"></i> {{ $filemanager->name }}</td>
					@endif
					<td class='created_at'>{{ date("$date_format $time_format", strtotime($filemanager->created_at)) }}</td>
					<td class='updated_at'>{{ date("$date_format $time_format", strtotime($filemanager->updated_at)) }}</td>
					
					<td class="text-center">
					    <div class="dropdown">
						    <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">{{ _lang('Action') }}
							<i class="fa fa-angle-down"></i></button>
							<div class="dropdown-menu">
								@if($filemanager->is_dir == 'no')
									@if (Auth::user()->user_type == 'admin'))
										<a href="{{ action('FileManagerController@edit', $filemanager['id']) }}" data-title="{{ _lang('Update File') }}" class="ajax-modal dropdown-item"><i class="far fa-edit"></i> {{ _lang('Edit') }}</a></li>
									@endif
									<a class="dropdown-item" href="{{ route('file_manager.download', $filemanager['id']) }}" target="_blank"><i class="fas fa-cloud-download-alt"></i> {{ _lang('Download') }}</a></li>
								@else
									<a href="{{ action('FileManagerController@edit_folder', $filemanager['id']) }}" data-title="{{ _lang('Update Folder') }}" class="ajax-modal dropdown-item"><i class="far fa-edit"></i> {{ _lang('Edit') }}</a></li>
									<a class="dropdown-item" href="{{ route('file_manager.download.all', $filemanager['id']) }}" target="_blank"><i class="fas fa-cloud-download-alt"></i> {{ _lang('Download All') }}</a></li>
                                    <a class="dropdown-item" href="{{ url('file_manager/directory/'.encrypt($filemanager->id)) }}"><i class="fas fa-binoculars"></i> {{ _lang('View') }}</a></li>
								@endif
								
								@if (\App\Company::find(company_id())->allow_file_manager_delete == 1)
									<form action="{{ action('FileManagerController@destroy', $filemanager['id']) }}" method="post">									
										{{ csrf_field() }}
										<input name="_method" type="hidden" value="DELETE">
										<button class="button-link btn-remove" type="submit"><i class="fas fa-trash-alt"></i> {{ _lang('Delete') }}</button>
									</form>
								@endif
								
							</div>
						</div>
					
					</td>
				  </tr>
				  @endforeach
				</tbody>
			  </table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
<script language="Javascript" type="text/javascript">
$(document).ready(function() {
	$( "#reset_form" ).click(function() {
		$("#invoice_number").val("");
		$("#client_id").select2("val", " ");
		// $("#invoice_date").val("");
	});
    
});
</script>

@endsection
