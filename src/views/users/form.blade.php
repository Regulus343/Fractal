@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var minimumPasswordLength = {{ Fractal::getSetting('Minimum Password Length') }};
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/user', 'regulus/fractal') }}"></script>

	@if (isset($update))

	<ul class="nav nav-tabs">
		<li role="presentation" class="active">
			<a href="#main-area">Main</a>
		</li>

		<li role="presentation">
			<a href="#permissions-area">Permissions</a>
		</li>
	</ul>

	@endif

	<div class="tab-content">

		<div id="main-area" class="tab-pane{!! HTML::dynamicArea(isset($update), 'tab-pane-padded', true) !!} fade in active">

			{!! Form::openResource() !!}

				<div class="row">
					<div class="col-md-6">
						{!! Form::field('name', 'text', ['label' => Fractal::trans('labels.username')]) !!}
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
						{!! Form::field('phone', 'text', ['label' => 'Phone Number']) !!}
					</div>

					<div class="col-md-4">
						{!! Form::field('website') !!}
					</div>

					<div class="col-md-4">
						{!! Form::field('twitter', 'text', ['maxlength' => 16]) !!}
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						{!! Form::field('about', 'textarea', ['class-field' => 'ckeditor']) !!}
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						{!! Form::field('roles', 'checkbox-set', [
							'options'        => Form::prepOptions(Regulus\Identify\Models\Role::orderBy('display_order')->orderBy('name')->get(), array('id', 'name')),
							'label'          => Fractal::transChoice('labels.role', 2),
							'associative'    => true,
							'name-values'    => true,
						]) !!}
					</div>
				</div>

				@if (!isset($update) || !$update)
					<div class="row">
						<div class="col-md-4">
							{!! Form::field('password') !!}
						</div>

						<div class="col-md-4">
							{!! Form::field('password_confirmation', null, ['label' => 'Confirm Password']) !!}
						</div>

						<div class="col-md-4 passwords-check">
							<span class="glyphicon glyphicon-ok-circle passwords-match green hidden"></span>
							<span class="glyphicon glyphicon-remove-circle passwords-mismatch red hidden"></span>
						</div>
					</div>
				@endif

				<div class="row">
					<div class="col-md-12">
						{!! Form::field(null, 'checkbox-set', [
							'options'     => ['active' => 'Active', 'banned' => 'Banned'],
							'label'       => Fractal::transChoice('labels.status', 2),
							'associative' => true
						]) !!}
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						{!! Form::field(Form::submitResource(Fractal::transChoice('labels.user')), 'button') !!}
					</div>
				</div>

			{!! Form::close() !!}

		</div><!-- /#main-area -->

		@if (isset($update))

			<div id="permissions-area" class="tab-pane tab-pane-padded fade in padding-top-20px">

				@include(Fractal::view('users.permissions.partials.tree', true), ['permissions' => $permissions])

			</div><!-- /#permissions-area -->

		@endif

	</div><!-- /.tab-content -->

@stop