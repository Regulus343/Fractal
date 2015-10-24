@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			setTimeout(function()
			{
				$('#field-email').focus();
			}, 500);
		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{!! Form::open(['class' => 'form-login']) !!}

		<h2>{{ Site::heading() }}</h2>

		{!! Form::field('email', 'text', ['label' => Fractal::trans('labels.email')]) !!}

		{!! Form::field('[ICON: check-circle]'.Fractal::trans('labels.reset_password'), 'button', ['class' => 'btn-primary pull-right']) !!}

		<a href="{{ Fractal::url('login') }}" class="btn btn-default">
			<i class="fa fa-arrow-left"></i> {{ Fractal::trans('labels.return_to_log_in') }}
		</a>

	{!! Form::close() !!}

@stop