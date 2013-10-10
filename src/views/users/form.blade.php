@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){
			$('#username').keyup(function(){
				$('#username').val($('#username').val().replace(/ /g, '-'));
			});

			$('#country').change(function(){
				if ($(this).val() == "Canada") {
					$('#region-area').removeClass('hidden');
					$('label[for="region"]').text('Province');

				} else if ($(this).val() == "United States") {
					$('#region-area').removeClass('hidden');
					$('label[for="region"]').text('State');
				} else {
					$('#region-area').addClass('hidden');
					$('label[for="region"]').text('Region');
				}
			});

			$('#password').val(''); //prevent browser from automatically inserting a password

			$('#password, #password-confirmation').change(function(){
				checkPasswords();
			}).keyup(function(){
				checkPasswords();
			});
		});

		function checkPasswords() {
			var password             = $('#password').val();
			var passwordConfirmation = $('#password-confirmation').val();

			if (password != "") {
				if (password == passwordConfirmation) {
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

	{{ Form::openResource(null, null, 'users') }}
		<div class="row">
			<div class="col-md-6">
				{{ Form::field('username') }}
			</div><div class="col-md-6">
				{{ Form::field('email') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('first_name') }}
			</div><div class="col-md-6">
				{{ Form::field('last_name') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('city') }}
			</div><div class="col-md-4">
				{{ Form::field('country', 'select', array(
					'label'       => Lang::get('fractal::labels.country'),
					'options'     => Form::countryOptions(),
					'null-option' => Lang::get('fractal::messages.selectItem', array('item' => Format::a(strtolower(Lang::get('fractal::labels.country')))))
				)) }}
			</div><div class="col-md-4">
				{{ Form::field('region', 'select', array(
					'label'       => Fractal::regionLabel(Form::value('country')),
					'options'     => Form::provinceOptions(),
					'null-option' => Lang::get('fractal::messages.selectItem', array('item' => Format::a(strtolower(Fractal::regionLabel(Form::value('country'))))))
				)) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('phone', 'text', array('label' => 'Phone Number')) }}
			</div><div class="col-md-4">
				{{ Form::field('website') }}
			</div><div class="col-md-4">
				{{ Form::field('twitter', 'text', array('maxlength' => 16)) }}
			</div>
		</div>

		{{ Form::field('about', 'textarea', array('class-field' => 'ckeditor')) }}

		{{ Form::field('roles.', 'checkbox-set', array(
			'options'     => Form::prepOptions(Regulus\Identify\Role::orderBy('display_order')->orderBy('name')->get(), array('id', 'name')),
			'label'       => Lang::get('fractal::labels.roles'),
			'associative' => true,
			'name-values' => true
		)) }}

		@if (!isset($update) || !$update)
			<div class="row">
				<div class="col-md-4">
					{{ Form::field('password') }}
				</div><div class="col-md-4">
					{{ Form::field('password_confirmation', null, array('label' => 'Confirm Password')) }}
				</div><div class="col-md-4 passwords-check">
					<span class="glyphicon glyphicon-ok-circle passwords-match green hidden"></span>
					<span class="glyphicon glyphicon-remove-circle passwords-mismatch red hidden"></span>
				</div>
			</div>
		@endif

		{{ Form::field(null, 'checkbox-set', array(
			'options'     => array('active' => 'Active', 'banned' => 'Banned'),
			'label'       => Lang::get('fractal::labels.statuses'),
			'associative' => true
		)) }}

		{{ Form::field(Form::submitResource('User', (isset($update) && $update)), 'button') }}
	{{ Form::close() }}

@stop