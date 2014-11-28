@section('search-filters')

	<div class="row padding-bottom-10px">
		<div class="col-md-3">
			{{ Form::select('filters.category_id', Form::prepOptions(Regulus\Fractal\Models\Blogs\Category::orderBy('name')->get(), ['id', 'name']), [
				'label'       => Fractal::lang('labels.category'),
				'null-option' => Fractal::lang('labels.selectItem', ['item' => Format::a(Fractal::lang('labels.category'))]),
				'class'       => 'search-filter',
			]) }}
		</div>
	</div>

@stop