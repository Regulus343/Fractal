@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		var page     = {{ $users->getCurrentPage() }};
		var lastPage = {{ $users->getLastPage() }};
		$(document).ready(function(){

			setupUsersTable();

			$('#form-search').submit(function(e){
				e.preventDefault();
				$('#changing-page').val(0);
				searchUsers();
			});

			$('#search').change(function(){
				$('#changing-page').val(0);
				searchUsers();
			});

			$('.pagination li a').click(function(e){
				e.preventDefault();
				if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
					page = $(this).attr('data-page');

					$('#page').val(page);
					$('#changing-page').val(1);

					$('.pagination li').removeClass('active');
					$('.pagination li a').each(function(){
						if ($(this).text() == page) $(this).parents('li').addClass('active');
					});

					if (page == 1) {
						$('.pagination li:first-child').addClass('disabled');
					} else {
						$('.pagination li:first-child').removeClass('disabled');
					}

					if (page == lastPage) {
						$('.pagination li:last-child').addClass('disabled');
					} else {
						$('.pagination li:last-child').removeClass('disabled');
					}

					searchUsers();
				}
			});
		});

		function searchUsers() {
			var postData = $('#form-search').serialize();
			$('.alert-dismissable').addClass('hidden');
			$.ajax({
				url: baseURL + '/users/search',
				type: 'post',
				data: postData,
				dataType: 'json',
				success: function(result){
					if (result.resultType == "Success") {
						setMainMessage(result.message, 'success');
					} else {
						setMainMessage(result.message, 'error');
					}

					$('table.table').html(result.table);
					setupUsersTable();
				},
				error: function(){
					setMainMessage(fractalMessages.errorGeneral, 'error');
				}
			});
		}
	</script>

	<div class="row search-pagination-area">
		{{-- Search --}}
		<div class="col-md-4">
			{{ Form::open(Fractal::url('users/search'), 'post', array('id' => 'form-search')) }}

				{{ Form::text('search', null, array('placeholder' => Lang::get('fractal::labels.search'))) }}

				{{ Form::hidden('page', $users->getCurrentPage()) }}
				{{ Form::hidden('changing_page', 0) }}

			{{ Form::close() }}
		</div>

		{{-- Pagination --}}
		@if ($users->getLastPage() > 1)
			<div class="col-md-8">
				<ul class="pagination">
					<li{{ HTML::dynamicArea($users->getCurrentPage() == 1, 'disabled') }}>
						<a href="" data-page="1">&laquo;</a>
					</li>

					@for ($p = $users->getCurrentPage() - 2; $p <= $users->getCurrentPage() + 3; $p++)
						@if ($p > 0 && $p <= $users->getLastPage())
							<li{{ HTML::dynamicArea($users->getCurrentPage() == $p, 'active') }}>
								<a href="" data-page="{{ $p }}">{{ $p }}</a>
							</li>
						@endif
					@endfor

					<li{{ HTML::dynamicArea($users->getCurrentPage() == $users->getLastPage(), 'disabled') }}>
						<a href="" data-page="{{ $users->getLastPage() }}">&raquo;</a>
					</li>
				</ul>
			</div>
		@endif
	</div>

	<div class="row">
		<div class="col-md-12">
			{{ HTML::table(Config::get('fractal::tables.users'), $users) }}
		</div>
	</div>

	<a class="btn btn-default" href="{{ Fractal::url('users/create') }}">{{ Lang::get('fractal::labels.createUser') }}</a>

@stop