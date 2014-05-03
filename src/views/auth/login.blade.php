@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){
			$('#username').focus();
		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{{ Form::open(null, 'post', array('class' => 'form-login')) }}
		<h2>{{ Site::titleHeading() }}</h2>

		{{ Form::field('username') }}

		{{ Form::field('password') }}

		{{ Form::field('[ICON: share-alt]Log In', 'button') }}
	{{ Form::close() }}

@stop