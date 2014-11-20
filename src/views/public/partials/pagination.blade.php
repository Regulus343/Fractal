<ul class="pagination pull-right{{ HTML::hiddenArea(Site::get('lastPage') == 1, true) }}">
	<li{{ HTML::dynamicArea(Site::get('currentPage') == 1, 'disabled') }}>
		<a href="{{ Fractal::pageUrl(1) }}">&laquo;</a>
	</li>

	@for ($p = Site::get('currentPage') - 2; $p <= Site::get('currentPage') + 3; $p++)
		@if ($p > 0 && $p <= Site::get('lastPage'))
			<li{{ HTML::dynamicArea(Site::get('currentPage') == $p, 'active') }}>
				<a href="{{ Fractal::pageUrl($p) }}">{{ $p }}</a>
			</li>
		@endif
	@endfor

	<li{{ HTML::dynamicArea(Site::get('currentPage') == Site::get('lastPage'), 'disabled') }}>
		<a href="{{ Fractal::pageUrl(Site::get('lastPage')) }}">&raquo;</a>
	</li>
</ul>