@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			@if (!isset($update) || !$update)

				$('#field-name').keyup(function()
				{
					var slug = Fractal.strToSlug($('#field-name').val());
					$('#field-role').val(slug);
				});

			@endif

			Fractal.initSlugField('field-role');
		});
	</script>

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
					<div class="col-md-4">
						{!! Form::field('name') !!}
					</div>

					<div class="col-md-4">
						{!! Form::field('role') !!}
					</div>

					<div class="col-md-4">
						{!! Form::field('display_order', 'select', ['options' => Form::numberOptions(1, 36)]) !!}
					</div>
				</div>

				<div class="row">
					<div class="col-md-8">
						{!! Form::field('description', 'textarea') !!}
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						{!! Form::field(Form::submitResource(Fractal::transChoice('labels.role')), 'button') !!}
					</div>
				</div>

			{!! Form::close() !!}

		</div><!-- /#main-area -->

		@if (isset($update))

			<div id="permissions-area" class="tab-pane tab-pane-padded fade in padding-top-20px">

				@include(Fractal::view('users.permissions.partials.tree_legend', true))

				@include(Fractal::view('users.permissions.partials.tree', true), ['permissions' => $permissions])

			</div><!-- /#permissions-area -->

		@endif

	</div><!-- /.tab-content -->

@stop