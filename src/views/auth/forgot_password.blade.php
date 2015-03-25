@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function(){

			$('#field-identifier').focus();

		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{!! Form::open(['class' => 'form-login']) !!}

		<h2>{{ Site::heading() }}</h2>

		{!! Form::field('identifier', 'text', ['label' => Fractal::trans('labels.username')]) !!}

		<a href="{{ Fractal::url('login') }}" class="pull-right">{{ Fractal::trans('labels.return_to_log_in') }}</a>

		{!! Form::field('[ICON: check-circle]'.Fractal::trans('labels.reset_password'), 'button') !!}

	{!! Form::close() !!}

@stop