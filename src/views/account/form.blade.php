@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var minimumPasswordLength = {{ Fractal::getSetting('Minimum Password Length') }};
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/user', 'regulus/fractal') }}"></script>

	{!! Form::open() !!}

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('username') !!}
			</div>
			<div class="col-md-6">
				{!! Form::field('email') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('first_name') !!}
			</div>
			<div class="col-md-6">
				{!! Form::field('last_name') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('city') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('country', 'select', [
					'label'       => Fractal::trans('labels.country'),
					'options'     => Form::countryOptions(),
					'null-option' => Fractal::trans('messages.select_item', ['item' => Format::a(strtolower(Fractal::trans('labels.country')))])
				]) !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('region', 'select', [
					'label'       => Fractal::getRegionLabel(Form::value('country')),
					'options'     => Form::provinceOptions(),
					'null-option' => Fractal::trans('messages.select_item', ['item' => Format::a(strtolower(Fractal::getRegionLabel(Form::value('country'))))])
				]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('phone', 'text', array('label' => 'Phone Number')) !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('website') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('twitter', 'text', array('maxlength' => 16)) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('about', 'textarea', array('class-field' => 'ckeditor')) !!}
			</div>
		</div>

		@if (!isset($update) || !$update)
			<div class="row">
				<div class="col-md-4">
					{!! Form::field('password') !!}
				</div>
				<div class="col-md-4">
					{!! Form::field('password_confirmation', null, array('label' => 'Confirm Password')) !!}
				</div>
				<div class="col-md-4 passwords-check">
					<span class="glyphicon glyphicon-ok-circle passwords-match green hidden"></span>
					<span class="glyphicon glyphicon-remove-circle passwords-mismatch red hidden"></span>
				</div>
			</div>
		@endif

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(null, true), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop