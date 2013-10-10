@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){

			setupUsersTable();

			$('#form-search').submit(function(e){
				e.preventDefault();

				var postData = $('#form-search').serialize();

				$.ajax({
					url: baseURL + '/users/search',
					type: 'post',
					data: postData,
					dataType: 'json',
					success: function(result){
						if (result.resultType == "Success") {
							$('table.table').html(result.table);

							setupUsersTable();

							setMainMessage(result.message, 'success');
						} else {
							setMainMessage(result.message, 'error');
						}
					},
					error: function(){
						setMainMessage(fractalMessages.errorGeneral, 'error');
					}
				});
			});
		});
	</script>

	<div class="row">
		<div class="col-md-9">
			{{ HTML::table(Config::get('fractal::tables.users'), $users) }}
		</div>

		<div class="col-md-3">
			{{ Form::open(Fractal::url('users/search'), 'post', array('id' => 'form-search')) }}

				{{ Form::text('search', null, array('placeholder' => Lang::get('fractal::labels.search'))) }}

			{{ Form::close() }}
		</div>
	</div>

	<a class="btn btn-default" href="{{ Fractal::url('users/create') }}">{{ Lang::get('fractal::labels.createUser') }}</a>

@stop