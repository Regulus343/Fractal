@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<div class="jumbotron">
		<h1>Welcome to Fractal</h1>

		<p>Fractal is currently in development and is at the PRE-ALPHA stage. Stay tuned.</p>

		<p>Fractal's core philosophy is:</p>

		<h3>&ldquo;Maintain simplicity while ensuring the developer is free to customize and modify as they please.&rdquo;</h3>
	</div>

	<div class="row">
		<div class="col-md-9">
			<p>Your experience with Fractal is intended to be more "<strong>integrate a CMS into a website</strong>" rather than "<strong>build a website within the confines of a CMS</strong>". You will find that almost everything is customizable (and many things have multiple levels of customization). For example, to adjust the <a href="{{ Fractal::url('users') }}">User List</a> by editing the "<em>users</em>" array in <em>config/tables.php</em> or changing the "<em>viewsLocation</em>" variable in <em>config/config.php</em> to point to a directory of your own custom views. This way, you can copy the current views to another directory outside of Fractal's package directory and use them as a starting point for a more customized project..</p>

			<p>Lastly, you will likely find the <a href="https://github.com/Regulus343/Fractal/wiki/Documentation" target="_blank" class="bold">documentation</a> very informative.</p>
		</div>
	</div>

@stop