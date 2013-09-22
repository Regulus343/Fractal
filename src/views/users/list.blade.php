@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){

			$('table td.actions a[title]').tooltip();

			$('.ban-user').click(function(e){
				e.preventDefault();

				var userID = $(this).attr('data-user-id');
				$.ajax({
					url: baseURL + '/users/ban/' + userID,
					dataType: 'json',
					success: function(){
						console.log('Test...');
					}
				});
			});
		});
	</script>

	{{ HTML::table(Config::get('fractal::tables.users'), $users) }}

	<a class="btn btn-default" href="{{ Fractal::url('users/create') }}">{{ Lang::get('fractal::labels.createUser') }}</a>

@stop