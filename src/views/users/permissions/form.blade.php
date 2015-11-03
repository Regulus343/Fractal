@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('name') !!}
			</div>

			<div class="col-md-4">
				{!! Form::field('parent_id', 'select', [
					'label'   => 'Parent',
					'options' => Form::prepOptions(Regulus\Fractal\Models\User\Permission::orderBy('display_order')->get(), ['id', 'name']),
				]) !!}
			</div>

			<div class="col-md-4">
				{!! Form::field('display_order', 'select', ['options' => Form::numberOptions(1, 36)]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('description', 'textarea') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.permission')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop