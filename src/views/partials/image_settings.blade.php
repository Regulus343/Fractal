<div id="image-settings-area" class="hidden">
	<div class="row">
		<div class="col-md-4">
			{{ Form::field('width', 'number', ['placeholder-field' => 'Current Width']) }}
		</div>
		<div class="col-md-4">
			{{ Form::field('height', 'number', ['placeholder-field' => 'Current Height']) }}
		</div>
		<div class="col-md-4">
			{{ Form::field(null, 'checkbox-set', [
				'options' => ['crop', 'create_thumbnail'],
			]) }}
		</div>
	</div>

	<div id="thumbnail-settings-area" class="row{{ HTML::hiddenArea(!Form::value('create_thumbnail', 'checkbox'), true) }}">
		<div class="col-md-4">
			{{ Form::field('thumbnail_width', 'number', ['placeholder-field' => 'Thumbnail Width']) }}
		</div>
		<div class="col-md-4">
			{{ Form::field('thumbnail_height', 'number', ['placeholder-field' => 'Thumbnail Height']) }}
		</div>
	</div>
</div>