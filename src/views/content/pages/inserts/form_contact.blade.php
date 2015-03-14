@unless(Session::get('messageSent'))

	<div class="row">
		<div class="col-md-12">

			{!! Form::open() !!}

				{!! Form::field('name') !!}

				{!! Form::field('email') !!}

				{!! Form::field('message', 'textarea') !!}

				{!! Form::field('[ICON: share-alt]Send Message', 'button') !!}

			{!! Form::close() !!}

		</div>
	</div>

@endunless