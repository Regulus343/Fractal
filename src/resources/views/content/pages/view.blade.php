@extends(config('cms.layout_public'))

@section(config('cms.content_section'))

	@if (Auth::is('admin'))

		<div class="row padding-bottom-10px">
			<div class="col-md-12">
				<a href="{{ Fractal::url('pages/'.$page->slug.'/edit') }}" class="btn btn-primary btn-xs pull-right">
					<span class="glyphicon glyphicon-edit"></span>

					{{ Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.page')]) }}
				</a>
			</div>
		</div>

	@endif

	{!! $page->getRenderedContent() !!}

@stop