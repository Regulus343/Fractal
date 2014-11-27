<script id="content-area-template" type="text/x-handlebars-template">

	<fieldset id="content-area-{{number}}" data-item-number="{{number}}">
		<legend>Content Area</legend>

		<?=Form::hidden('content_areas.{{number}}.id')?>

		<?=Form::hidden('content_areas.{{number}}.content')?>

		<div class="row">
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.title')?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.pivot.layout_tag', 'select', [
					'options'     => Form::simpleOptions($layoutTagOptions),
					'null-option' => 'Select a Layout Tag',
				])?>
			</div>
			<div class="col-md-4">
				<?=Form::field('content_areas.{{number}}.content_type', 'select', [
					'options'     => Form::simpleOptions(['HTML', 'Markdown']),
					'null-option' => false,
					'value'       => Fractal::getSetting('Default Content Area Type'),
				])?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 html-content-area">
				<?=Form::field('content_areas.{{number}}.content_html', 'textarea', [
					'label'       => 'HTML Content',
					'class-field' => 'ckeditor',
				])?>
			</div>

			<div class="col-md-12 col-lg-6 markdown-content-area">
				<?=Form::field('content_areas.{{number}}.content_markdown', 'textarea', [
					'label'       => 'Markdown Content',
					'class-field' => 'tab',
				])?>

				<a href="" class="btn btn-default trigger-modal pull-right" data-modal-ajax-uri="api/view-markdown-guide" data-modal-ajax-action="get">
					<span class="glyphicon glyphicon-file"></span>&nbsp; <?=Lang::get('fractal::labels.viewMarkdownGuide')?>
				</a>
			</div>

			<div class="col-lg-6 col-markdown-preview-content">
				<?=Form::label('')?>

				<div class="markdown-preview-content"></div>
			</div>
		</div>

		<a href="" class="btn btn-danger btn-xs remove-template-item pull-right">
			<span class="glyphicon glyphicon-remove-circle"></span>&nbsp; <?=Fractal::lang('labels.removeContentArea')?>
		</a>
	</fieldset>

</script>