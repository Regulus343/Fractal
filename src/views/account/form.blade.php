@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		var minimumPasswordLength = {{ Fractal::getSetting('Minimum Password Length') }};
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/user-form', 'regulus/fractal') }}"></script>

	{{ Form::openResource() }}
		<div class="row">
			<div class="col-md-6">
				{{ Form::field('username') }}
			</div>
			<div class="col-md-6">
				{{ Form::field('email') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('first_name') }}
			</div>
			<div class="col-md-6">
				{{ Form::field('last_name') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('city') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('country', 'select', array(
					'label'       => Lang::get('fractal::labels.country'),
					'options'     => Form::countryOptions(),
					'null-option' => Lang::get('fractal::messages.selectItem', array('item' => Format::a(strtolower(Lang::get('fractal::labels.country')))))
				)) }}
			</div>
			<div class="col-md-4">
				{{ Form::field('region', 'select', array(
					'label'       => Fractal::getRegionLabel(Form::value('country')),
					'options'     => Form::provinceOptions(),
					'null-option' => Lang::get('fractal::messages.selectItem', array('item' => Format::a(strtolower(Fractal::getRegionLabel(Form::value('country'))))))
				)) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('phone', 'text', array('label' => 'Phone Number')) }}
			</div>
			<div class="col-md-4">
				{{ Form::field('website') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('twitter', 'text', array('maxlength' => 16)) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('about', 'textarea', array('class-field' => 'ckeditor')) }}
			</div>
		</div>

		@if (!isset($update) || !$update)
			<div class="row">
				<div class="col-md-4">
					{{ Form::field('password') }}
				</div>
				<div class="col-md-4">
					{{ Form::field('password_confirmation', null, array('label' => 'Confirm Password')) }}
				</div>
				<div class="col-md-4 passwords-check">
					<span class="glyphicon glyphicon-ok-circle passwords-match green hidden"></span>
					<span class="glyphicon glyphicon-remove-circle passwords-mismatch red hidden"></span>
				</div>
			</div>
		@endif

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(null, true), 'button') }}
			</div>
		</div>
	{{ Form::close() }}

@stop