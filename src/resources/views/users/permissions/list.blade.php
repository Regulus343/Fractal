@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<ul class="nav nav-tabs">
		<li role="presentation" class="active">
			<a href="#list-area">List</a>
		</li>

		<li role="presentation">
			<a href="#tree-area">Tree</a>
		</li>
	</ul>

	<div class="tab-content">

		<div id="list-area" class="tab-pane fade in active">

			{{-- Content Table --}}
			@include(Fractal::view('partials.content_table', true))

		</div><!-- /#list-area -->

		<div id="tree-area" class="tab-pane tab-pane-padded fade in">

			@include(Fractal::view('partials.tree'), ['permissions' => $rootPermissions])

		</div><!-- /#tree-area -->

	</div><!-- /.tab-content -->

@stop