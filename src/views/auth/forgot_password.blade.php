@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){
			$('#username').focus();
		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{{ Form::open(['class' => 'form-login']) }}
		<h2>{{ Site::titleHeading() }}</h2>

		{{ Form::field('username') }}

		<a href="{{ Fractal::url('login') }}" class="pull-right">{{ Lang::get('fractal::labels.returnToLogIn') }}</a>

		{{ Form::field('[ICON: share-alt]'.Fractal::lang('labels.resetPassword'), 'button') }}
	{{ Form::close() }}

@stop