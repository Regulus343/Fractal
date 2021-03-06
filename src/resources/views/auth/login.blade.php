@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			setTimeout(function()
			{
				if ($('#field-identifier').val() != "")
					$('#field-password').focus();
				else
					$('#field-identifier').focus();
			}, 500);

			$(document).keypress(function(e)
			{
				if (e.charCode == 13)
					$('form.form-login').submit();
			});
		});
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />

	{!! Form::open(['class' => 'form-login']) !!}

		<h2>{{ Site::heading() }}</h2>

		{!! Form::field('identifier', 'text', ['label' => Fractal::trans('labels.username')]) !!}

		{!! Form::field('password') !!}

		{!! Form::field('[ICON: sign-in]'.Fractal::trans('labels.log_in'), 'button', ['class' => 'btn-primary pull-right']) !!}

		<a href="{{ Fractal::url('password') }}" class="btn btn-default">
			<i class="fa fa-question-circle"></i> {{ Fractal::trans('labels.forgot_your_password') }}
		</a>

	{!! Form::close() !!}

@stop