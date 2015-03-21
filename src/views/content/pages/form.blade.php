@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var contentAreaId;
		var addingContentArea = false;

		$(document).ready(function()
		{
			@if (!isset($update) || !$update)

				$('#field-title').keyup(function(){
					$('#field-title').val($('#field-title').val().replace(/  /g, ' '));

					var slug = Fractal.strToSlug($('#field-title').val());
					$('#field-slug').val(slug);

					$('#field-content-areas-1-title').val($('#field-title').val());
				});

			@endif

			$('#field-slug').keyup(function(){
				var slug = Fractal.strToSlug($('#field-slug').val());
				$('#field-slug').val(slug);
			});

			$('#field-layout-template-id, #field-layout').change(function(){
				getLayoutTags();
			});

			Formation.loadTemplates('#content-areas', $.parseJSON('{!! Form::getJsonValues('content_areas') !!}'), contentAreaTemplateCallback);

			Fractal.initAutoSave();

			$('form').submit(function(e){
				$('#content-areas fieldset').each(function(){
					if ($(this).find('.field-content-type').val() == "HTML")
						$(this).find('.field-content').val(CKEDITOR.instances[$(this).find('.field-content-html').attr('id')].getData());
					else
						$(this).find('.field-content').val($(this).find('.field-content-markdown').val());
				});
			});
		});

		function getLayoutTags() {
			var postData = SolidSite.prepData({
				layout_template_id: 0,
				layout:             ''
			});

			if ($('#field-layout-template-id').val() != "") {
				$('#layout-area').addClass('hidden');
				postData.layout_template_id = $('#field-layout-template-id').val();
			} else {
				$('#layout-area').removeClass('hidden');
				postData.layout = $('#layout').val();
			}

			Formation.ajaxForSelect({
				url:              Fractal.createUrl('pages/layout-tags'),
				postData:         postData,
				targetSelect:     '.field-pivot-layout-tag',
				callbackFunction: autoSelectFirstLayoutTagCallback,
			});
		}

		var autoSelectFirstLayoutTagCallback = function()
		{
			if ($('#field-content-areas-1-pivot-layout-tag').val() == "")
				$('#field-content-areas-1-pivot-layout-tag').val($('#field-content-areas-1-pivot-layout-tag option:nth-child(2)').attr('value'));
		}

		var contentAreaTemplateCallback = function(item, data)
		{
			getLayoutTags();
			setupContentTypeFields();

			if (item.find('.field-content-type').val() != "HTML")
			{
				item.find('.html-content-area').addClass('hidden');
				item.find('.markdown-content-area').removeClass('hidden');
				item.find('.col-markdown-preview-content').removeClass('hidden');

				if (data !== null)
					item.find('.field-content-markdown').val(data.content);
			} else {
				item.find('.markdown-content-area').addClass('hidden');
				item.find('.col-markdown-preview-content').addClass('hidden');
				item.find('.html-content-area').removeClass('hidden');

				if (data !== null)
					item.find('.field-content-html').val(data.content);
			}

			// initialize WYSIWYG editor for HTML content field
			var htmlField = item.find('.field-content-html');
			if (htmlField)
				CKEDITOR.replace(htmlField.attr('id'));

			// initialize Markdown content field action preview window
			var markdownField = item.find('.field-content-markdown');
			Fractal.initMarkdownField(markdownField);

			// initialize modal button triggers
			Fractal.initModalTriggers();

			// initialize expand content area buttons
			$('.btn-expand-content-area').off('click').on('click', function(e)
			{
				e.preventDefault();

				$(this).hide();
				$(this).parents('.row').addClass('full-screen').find('.btn-compress-content-area').removeClass('hidden').show();
				$('#footer').hide();
			});

			$('.btn-compress-content-area').off('click').on('click', function(e)
			{
				e.preventDefault();

				$(this).hide();
				$(this).parents('.row').removeClass('full-screen').find('.btn-expand-content-area').removeClass('hidden').show();
				$('#footer').show();
			});

			if (addingContentArea) {
				$('html, body').animate({
					scrollTop: (item.offset().top - 30) + 'px'
				}, 750);

				item.find('.field-title').focus();
			}

			addingContentArea = false;
		};

		var deleteContentArea = function()
		{
			$.ajax({
				url:     Fractal.createUrl('pages/delete-content-area/' + contentAreaId),
				success: function(result) {
					if (result == "Success") {
						$('#modal-secondary').hide();
						$('#select-content-area li[data-content-area-id="'+contentAreaId+'"]').remove();
					}
				}
			});
		}

		function selectContentAreaActions()
		{
			$('#select-content-area li').off('click').on('click', function(e)
			{
				if (!$(e.target).hasClass('delete'))
				{
					if ($(this).hasClass('new'))
					{
						addingContentArea = true;
						Formation.loadNewTemplate('#content-areas', contentAreaTemplateCallback);
					} else {
						$.ajax({
							url:      Fractal.createUrl('pages/get-content-area/' + $(this).attr('data-content-area-id')),
							dataType: 'json',
							success:  function(contentArea) {
								addingContentArea = true;
								Formation.loadTemplate('#content-areas', contentArea, contentAreaTemplateCallback);
							}
						});
					}

					$('#modal').modal('hide');
				}
			});

			$('#select-content-area li span.delete').off('click').on('click', function(){
				contentAreaId = $(this).parents('li').attr('data-content-area-id');

				modalConfirm(
					fractalLabels.deleteContentArea,
					fractalMessages.confirmDelete.replace(':item', fractalLabels.contentArea),
					deleteContentArea,
					'modal-secondary'
				);
			});
		}

		function setupContentTypeFields()
		{
			$('.field-content-type').off('change').on('change', function()
			{
				if ($(this).val() == "HTML")
				{
					$(this).parents('fieldset').find('.markdown-content-area').addClass('hidden');
					$(this).parents('fieldset').find('.col-markdown-preview-content').addClass('hidden');
					$(this).parents('fieldset').find('.html-content-area').removeClass('hidden');
					$(this).parents('fieldset').find('.field-content').val($(this).parents('fieldset').find('.field-content-html').val());
				} else {
					$(this).parents('fieldset').find('.html-content-area').addClass('hidden');
					$(this).parents('fieldset').find('.markdown-content-area').removeClass('hidden');
					$(this).parents('fieldset').find('.col-markdown-preview-content').removeClass('hidden');
					$(this).parents('fieldset').find('.field-content').val($(this).parents('fieldset').find('.field-content-markdown').val());
				}
			});

			$('.field-content-html, .field-content-markdown').off('change').on('change', function(){
				$(this).parents('fieldset').find('.field-content').val($(this).val());
			});
		}

		function setupContentFields()
		{
			$('.field-content-html, .field-content-markdown').off('change').on('change', function(){
				$(this).parents('fieldset').find('.field-content').val($(this).val());
			});
		}

		function publishedCheckedCallback(checked)
		{
			if (checked)
				$('#field-published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#field-published-at').val('');
		}
	</script>

	@include(Fractal::view('partials.markdown_preview', true))

	{!! Form::openResource() !!}

		{!! Form::hidden('id') !!}

		<div class="row button-menu">
			<div class="col-md-12">
				@if (isset($update) && $update)
					<a href="{{ $pageUrl }}" class="btn btn-default right-padded pull-right">
						<i class="fa fa-file"></i> {{ Fractal::trans('labels.view_item', ['item' => Fractal::transChoice('labels.page')]) }}
					</a>
				@endif

				<a href="{{ Fractal::url('pages') }}" class="btn btn-default pull-right">
					<i class="fa fa-list"></i> {{ Fractal::trans('labels.return_to_items_list', ['items' => Fractal::transChoice('labels.page', 2)]) }}
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('title') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('slug') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('layout_template_id', 'select', [
					'label'       => 'Layout Template',
					'options'     => Form::prepOptions(Regulus\Fractal\Models\Content\LayoutTemplate::orderBy('static', 'desc')->orderBy('name')->get(), ['id', 'name']),
					'null-option' => 'Custom Layout',
				]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('layout', 'textarea', [
					'id-field-container'    => 'layout-area',
					'class-field-container' => HTML::hiddenArea(Form::value('layout_template_id') != "", true),
					'class-field'           => 'tab',
				]) !!}
			</div>
		</div>

		{{-- Content Areas --}}

		<div id="content-areas" data-template-id="content-area-template"></div>

		@include(Fractal::view('content.pages.templates.content_area', true))

		<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="pages/add-content-area{{ (isset($id) ? '/'.$id : '') }}" data-modal-ajax-action="get" data-modal-callback-function="selectContentAreaActions">
			<i class="fa fa-plus-circle"></i> {{ Fractal::trans('labels.add_item', ['item' => Fractal::transChoice('labels.content_area')]) }}
		</a>

		<div class="row clear">
			<div class="col-md-2">
				<div class="form-group">
					{!! Form::field('published', 'checkbox', [
						'data-checked-show'      => '.published-at-area',
						'data-show-hide-type'    => 'visibility',
						'data-callback-function' => 'publishedCheckedCallback',
					]) !!}
				</div>
			</div>
			<div class="col-md-3 published-at-area{{ HTML::invisibleArea(!Form::value('published', 'checkbox'), true) }}">
				<div class="form-group">
					<div class="input-group date date-time-picker">
						{!! Form::text('published_at', null, [
							'class'       => 'date',
							'placeholder' => 'Date/Time Published',
						]) !!}

						<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.page')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop