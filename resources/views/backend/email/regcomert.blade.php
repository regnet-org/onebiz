<!DOCTYPE html>
<html>
<head>
    <title>{{ $content->subject }}</title>
	<style type="text/css">
	   .g-container{
		   padding: 15px 30px;
	   }
	</style>
</head>
<body>
    <div class="g-container">
		<p>{{ _lang('You have received new contact message from') }} : <b>{{ $content->name }}</b></p>
		<p>{{ _lang('Message details are bellow') }}:</p>

		<p><b>{{ _lang('Name') }}:</b> {{ $content->name }}<br>
		<b>{{ _lang('Phone') }}</b>: {{ $content->phone }}<br>
		<b>{{ _lang('Message') }}:</b><br>
		</p>
		<p>{!! nl2br($content->message) !!}</p>

		<br>
		<p>{{ _lang('Thank You') }}</p>
	</div>
</body>
</html>
