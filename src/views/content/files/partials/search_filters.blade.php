@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{!! Form::select('filters.type_id', Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']), [
				'label'       => Fractal::transChoice('labels.file_type'),
				'null-option' => Fractal::trans('labels.select_item', ['item' => Format::a(Fractal::transChoice('labels.file_type'))]),
				'class'       => 'search-filter',
			]) !!}
		</div>
	</div>

@stop