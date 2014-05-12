<script id="content-area-template" type="text/x-handlebars-template">
	<fieldset id="content-area-{{number}}" data-item-number="{{number}}">
		<legend>Content Area</legend>

		<?=Form::hidden('content_areas.{{number}}.id')?>

		<div class="row">
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.title')?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.layout_tag')?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.content_type', 'select', array(
					'class-field' => 'content-type',
					'options'     => Form::simpleOptions(array('HTML', 'Markdown')),
					'value'       => 'Markdown',
				))?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.updated_at')?>
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
	</fieldset>
</script>