@section('search-filters')

	<div class="row padding-vertical-10px">
		<div class="col-md-3">
			{{ Form::select('filters.media_type_id', Form::prepOptions(Regulus\Fractal\Models\MediaType::orderBy('name')->get(), ['id', 'name']), 'Select a Media Type', null, array('label' => 'Media Type')) }}
		</div>
		<div class="col-md-3">
			{{ Form::select('filters.media_set_id', Form::prepOptions(Regulus\Fractal\Models\MediaSet::orderBy('title')->get(), ['id', 'title']), 'Select a Media Set', null, array('label' => 'Media Set')) }}
		</div>
	</div>

@stop