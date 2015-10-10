@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var minimumPasswordLength = {{ Fractal::getSetting('Minimum Password Length') }};

		$(document).ready(function()
		{
			$('#field-password').focus();

			$('#field-password, #field-password-confirmation').change(function()
			{
				checkPasswords();
			}).keyup(function()
			{
				checkPasswords();
			});
		});

		function checkPasswords()
		{
			var password             = $('#field-password').val();
			var passwordConfirmation = $('#field-password-confirmation').val();

			if (password != "")
			{
				if (password == passwordConfirmation && password.length >= minimumPasswordLength)
				{
					$('.passwords-check .passwords-mismatch').addClass('hidden');
					$('.passwords-check .passwords-match').removeClass('hidden');
				} else {
					$('.passwords-check .passwords-match').addClass('hidden');
					$('.passwords-check .passwords-mismatch').removeClass('hidden');
				}
			} else {
				$('.passwords-check span').addClass('hidden');
			}
		}
	</script>

	<link type="text/css" rel="stylesheet" href="{{ Site::css('login', 'regulus/fractal') }}" />
	<style type="text/css">
		form { position: relative; }
		.passwords-check { position: absolute; z-index: 50; top: 98px; right : -20px; }
	</style>

	{!! Form::open(['class' => 'form-login']) !!}

		{!! Form::hidden('token', ['value' => $token]) !!}

		<h2>{{ Site::heading() }}</h2>

		<div class="passwords-check">
			<span class="glyphicon glyphicon-ok-circle passwords-match green hidden"></span>
			<span class="glyphicon glyphicon-remove-circle passwords-mismatch red hidden"></span>
		</div>

		{!! Form::field('email', 'text', ['label' => Fractal::trans('labels.email')]) !!}

		{!! Form::field('password', 'password') !!}

		{!! Form::field('password_confirmation', 'password', array('label' => 'Confirm New Password')) !!}

		<a href="{{ Fractal::url('login') }}" class="pull-right">{{ Fractal::trans('labels.return_to_log_in') }}</a>

		{!! Form::field('[ICON: check-circle]'.Fractal::trans('labels.reset_password'), 'button') !!}

	{!! Form::close() !!}

@stop