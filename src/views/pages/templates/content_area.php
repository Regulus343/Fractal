<script id="content-area-template" type="text/x-handlebars-template">
	<fieldset id="content-area-{{number}}" data-item-number="{{number}}">
		<legend>Content Area</legend>

		<?=Form::hidden('content_areas.{{number}}.id')?>

		<div class="row">
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.title')?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.pivot.layout_tag', 'select', array(
					'options'     => $layoutTagOptions,
					'null-option' => 'Select a Layout Tag'
				))?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.content_type', 'select', array(
					'options' => Form::simpleOptions(array('HTML', 'Markdown')),
					'value'   => Fractal::getSetting('Default Content Area Type'),
				))?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<?=Form::field('content_areas.{{number}}.content_html', 'textarea', array(
					'label'                 => 'HTML Content',
					'class-field-container' => 'html-content-area',
					'class-field'           => 'ckeditor',
				))?>

				<?=Form::field('content_areas.{{number}}.content_markdown', 'textarea', array(
					'label'                 => 'Markdown Content',
					'class-field-container' => 'markdown-content-area',
					'class-field'           => 'tab',
				))?>
			</div>
		</div>

		<a href="" class="btn btn-danger btn-xs remove-template-item pull-right">
			<span class="glyphicon glyphicon-remove-circle"></span>&nbsp; <?=Lang::get('fractal::labels.removeContentArea')?>
		</a>

		<?=Form::hidden('content_areas.{{number}}.content')?>
	</fieldset>
</script>