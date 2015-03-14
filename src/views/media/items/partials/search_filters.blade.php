@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{{ Form::select('filters.media_type_id', Form::prepOptions(Regulus\Fractal\Models\Media\Type::orderBy('name')->get(), ['id', 'getName(true)']), [
				'label'       => Fractal::trans('labels.mediaType'),
				'null-option' => Fractal::trans('labels.selectItem', ['item' => Format::a(Fractal::trans('labels.mediaType'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
		<div class="col-md-3">
			{{ Form::select('filters.media_set_id', Form::prepOptions(Regulus\Fractal\Models\Media\Set::orderBy('title')->get(), ['id', 'title']), [
				'label'       => Fractal::trans('labels.mediaSet'),
				'null-option' => Fractal::trans('labels.selectItem', ['item' => Format::a(Fractal::trans('labels.mediaSet'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
	</div>

@stop