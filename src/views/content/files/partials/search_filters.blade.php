@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{{ Form::select('filters.type_id', Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']), [
				'label'       => Fractal::lang('labels.fileType'),
				'null-option' => Fractal::lang('labels.selectItem', ['item' => Format::a(Fractal::lang('labels.fileType'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
	</div>

@stop