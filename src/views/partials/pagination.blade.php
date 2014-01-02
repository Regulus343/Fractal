@if (isset($page) && isset($lastPage) && $lastPage > 1)

	<ul class="pagination pull-right">
		<li{{ HTML::dynamicArea($page == 1, 'disabled') }}>
			<a href="" data-page="1">&laquo;</a>
		</li>

		@for ($p = $page - 2; $p <= $page + 3; $p++)
			@if ($p > 0 && $p <= $lastPage)
				<li{{ HTML::dynamicArea($page == $p, 'active') }}>
					<a href="" data-page="{{ $p }}">{{ $p }}</a>
				</li>
			@endif
		@endfor

		<li{{ HTML::dynamicArea($page == $lastPage, 'disabled') }}>
			<a href="" data-page="{{ $lastPage }}">&raquo;</a>
		</li>
	</ul>

@endif