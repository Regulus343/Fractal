<div id="image-settings-area" class="hidden">
	<div class="row">
		<div class="col-md-4">
			{{ Form::field('width', 'number', array('placeholder-field' => 'Current Width')) }}
		</div>
		<div class="col-md-4">
			{{ Form::field('height', 'number', array('placeholder-field' => 'Current Height')) }}
		</div>
		<div class="col-md-4">
			{{ Form::field(null, 'checkbox-set', array(
				'options' => array('crop', 'create_thumbnail')
			)) }}
		</div>
	</div>

	<div id="thumbnail-settings-area" class="row{{ HTML::hiddenArea(!Form::value('create_thumbnail', 'checkbox'), true) }}">
		<div class="col-md-4">
			{{ Form::field('thumbnail_width', 'number', array('placeholder-field' => 'Current Width')) }}
		</div>
		<div class="col-md-4">
			{{ Form::field('thumbnail_height', 'number', array('placeholder-field' => 'Current Height')) }}
		</div>
	</div>
</div>