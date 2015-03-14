@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{!! Form::select('filters.category_id', Form::prepOptions(Regulus\Fractal\Models\Blogs\Category::orderBy('name')->get(), ['id', 'name']), [
				'label'       => Fractal::trans('labels.category'),
				'null-option' => Fractal::trans('labels.select_item', ['item' => Format::a(Fractal::transChoice('labels.category'))]),
				'class'       => 'search-filter',
			]) !!}
		</div>
	</div>

@stop