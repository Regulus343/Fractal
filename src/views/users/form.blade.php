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
		});
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

		{{ Form::field('active', 'checkbox') }}

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
			{{ Form::field('password') }}

			{{ Form::field('password_confirm') }}
		@endif

		{{ Form::field(Form::submitResource('User', (isset($update) && $update)), 'button') }}
	{{ Form::close() }}

@stop