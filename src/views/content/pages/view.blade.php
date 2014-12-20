@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::contentSection'))

	@if (Auth::is('admin'))

		<div class="row padding-bottom-10px">
			<div class="col-md-12">
				<a href="{{ Fractal::url('pages/'.$page->slug.'/edit') }}" class="btn btn-primary btn-xs pull-right">
					<span class="glyphicon glyphicon-edit"></span>

					{{ Fractal::lang('labels.editPage') }}
				</a>
			</div>
		</div>

	@endif

	{{ $page->getRenderedContent() }}

@stop