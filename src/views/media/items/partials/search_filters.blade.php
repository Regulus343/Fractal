@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{{ Form::select('filters.media_type_id', Form::prepOptions(Regulus\Fractal\Models\Media\Type::orderBy('name')->get(), ['id', 'getName(true)']), [
				'label'       => Fractal::lang('labels.mediaType'),
				'null-option' => Fractal::lang('labels.selectItem', ['item' => Format::a(Fractal::lang('labels.mediaType'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
		<div class="col-md-3">
			{{ Form::select('filters.media_set_id', Form::prepOptions(Regulus\Fractal\Models\Media\Set::orderBy('title')->get(), ['id', 'title']), [
				'label'       => Fractal::lang('labels.mediaSet'),
				'null-option' => Fractal::lang('labels.selectItem', ['item' => Format::a(Fractal::lang('labels.mediaSet'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
	</div>

@stop