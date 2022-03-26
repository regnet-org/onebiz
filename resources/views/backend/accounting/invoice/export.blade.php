@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
	<div class="card">
	<span class="d-none panel-title">{{ _lang('Export Invoice') }}</span>

	<div class="card-body">
	  <form method="post" action="{{ route('export') }}">
		{{ csrf_field() }}
		
		<div class="row">	
			
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"></label>						
					<input type="radio" name="date_type_select" value="3" id="date_type_selec3" class="form-control radio" />
				</div>
			</div>
			<div class="col-md-10">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Ultimele 7 zile') }}</label>						
				</div>
			</div>
			
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"></label>						
					<input type="radio" name="date_type_select" value="4" id="date_type_select4" class="form-control radio" />
				</div>
			</div>
			<div class="col-md-10">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Ultimele 30 de zile') }}</label>						
				</div>
			</div>
			
		
			
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"></label>						
					<input type="radio" name="date_type_select" value="1" id="date_type_select1" class="form-control radio" />
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Month') }}</label>						
				   <select class="form-control select2 auto-select"  name="date_month" id="date_month">
				   	<option value=""></option>
				   	@foreach(['ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'] as $mk=>$month)
				   		<option value="{{$mk}}">{{$month}}</option>
				   	@endforeach
					</select>
				</div>
			</div>
			
			<div class="col-md-5">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Year') }}</label>						
				   <select class="form-control select2 auto-select"  name="date_year" id="date_year">
				   	@for($i=2020; $i<=date("Y", strtotime('next year')); $i++)
				   		<option value="{{$i}}">{{$i}}</option>
				   	@endfor
					</select>
				</div>
			</div>

			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"></label>						
					<input type="radio" name="date_type_select" value="2" id="date_type_select2" class="form-control radio" />
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Start Date') }}</label>						
				   <input type="text" class="form-control datepicker" name="start_date" id="start_date">
				</div>
			</div>

			<div class="col-md-5">
				<div class="form-group">
				   <label class="control-label">{{ _lang('End Date') }}</label>						
				   <input type="text" class="form-control datepicker" name="end_date" id="end_date" >
				</div>
			</div>
			
			<div class="col-md-1">
			</div>
			<div class="col-md-11">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Trimite email') }}</label>						
				   <input type="text" class="form-control" name="email" id="email" >
				</div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Export') }}</button>
			  </div>
			</div>
		</div>
	  </form>
	</div>
  </div>
 </div>
</div>
@endsection

@section('js-script')
<script language="Javascript" type="text/javascript">
	(function($) {
		$("#date_type_select1").prop("checked", true);
		$('#date_year').val({{date('Y')}}).trigger('change');
        
        $("#date_type_select1").click(function () {
            $("#date_month").prop('disabled', false);
        	$("#date_year").prop('disabled', false);
        	$("#start_date").prop('disabled', true);
        	$("#end_date").prop('disabled', true);
        });
        
        $("#date_type_select2").click(function () {
        	$("#date_month").prop('disabled', true);
        	$("#date_year").prop('disabled', true);
            $("#start_date").prop('disabled', false);
        	$("#end_date").prop('disabled', false);
        });
        
    })(jQuery);
</script>
@endsection



