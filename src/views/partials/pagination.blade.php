<ul class="pagination pull-right{{ HTML::hiddenArea(Fractal::getLastPage() == 1, true) }}">
	<li{!! HTML::dynamicArea(Fractal::getCurrentPage() == 1, 'disabled') !!}>
		<a href="" data-page="1">&laquo;</a>
	</li>

	@for ($p = Fractal::getCurrentPage() - 3; $p <= Fractal::getCurrentPage() + 3; $p++)
		@if ($p > 0 && $p <= Fractal::getLastPage())
			<li{!! HTML::dynamicArea(Fractal::getCurrentPage() == $p, 'active') !!}>
				<a href="" data-page="{{ $p }}">{{ $p }}</a>
			</li>
		@endif
	@endfor

	<li{!! HTML::dynamicArea(Fractal::getCurrentPage() == Fractal::getLastPage(), 'disabled') !!}>
		<a href="" data-page="{{ Fractal::getLastPage() }}">&raquo;</a>
	</li>
</ul>