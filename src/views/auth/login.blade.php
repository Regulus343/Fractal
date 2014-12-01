@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	<script type="text/javascript">
		$(document).ready(function(){
			if ($('#username').val() != "")
				$('#password').focus();
			else
				$('#username').focus();
		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{{ Form::open(['class' => 'form-login']) }}
		<h2>{{ Site::titleHeading() }}</h2>

		{{ Form::field('username') }}

		{{ Form::field('password') }}

		<a href="{{ Fractal::url('forgot-password') }}" class="pull-right">{{ Fractal::lang('labels.forgotYourPassword') }}</a>

		{{ Form::field('[ICON: share-alt]'.Fractal::lang('labels.logIn'), 'button') }}
	{{ Form::close() }}

@stop