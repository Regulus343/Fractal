@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<style type="text/css">
		table tr td.actions a { margin-right: 4px; font-size: 16px; }
		table tr td.no-data { text-align: center; font-size: 18px; font-style: italic; }
	</style>

	<script type="text/javascript">
		$(document).ready(function(){
			$('.ban-user').click(function(e){
				e.preventDefault();

				var userID = $(this).attr('data-user-id');
				$.ajax({
					url: baseURL + '/users/ban/' + userID,
					dataType: 'json',

				});
			});
		});
	</script>

	{{ HTML::table(Config::get('fractal::tables.users'), $users) }}

	<a class="btn btn-default" href="{{ Fractal::url('users/create') }}">{{ Lang::get('fractal::labels.createUser') }}</a>

@stop