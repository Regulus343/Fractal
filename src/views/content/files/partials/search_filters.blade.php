@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{{ Form::select('filters.type_id', Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']), [
				'label'       => Fractal::trans('labels.fileType'),
				'null-option' => Fractal::trans('labels.selectItem', ['item' => Format::a(Fractal::trans('labels.fileType'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
	</div>

@stop