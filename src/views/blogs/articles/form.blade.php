@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	<script type="text/javascript">
		var contentAreaId;
		var addingContentArea = false;

		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#field-title').keyup(function(){
					$('#field-title').val($('#field-title').val().replace(/  /g, ' '));

					var slug = strToSlug($('#field-title').val());
					$('#field-slug').val(slug);

					$('#field-content-areas-1-title').val($('#field-title').val());
				});
			@endif

			$('#field-slug').keyup(function(){
				var slug = strToSlug($('#slug').val());
				$('#field-slug').val(slug);
			});

			$('#field-layout-template-id, #field-layout').change(function(){
				getLayoutTags();
			});

			$('#remove-thumbnail-image').click(function(e){
				e.preventDefault();

				$('#field-thumbnail-image-type').val('');
				$('#field-thumbnail-image-file-id').val('');
				$('#field-thumbnail-image-media-item-id').val('');

				$('#thumbnail-image-area').fadeOut('fast');
			});

			Formation.loadTemplates('#content-areas', $.parseJSON('{{ Form::getJsonValues('content_areas') }}'), contentAreaTemplateCallback);

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
			var postData = {
				layout_template_id: 0,
				layout:             ''
			};

			if ($('#field-layout-template-id').val() != "") {
				$('#layout-area').addClass('hidden');
				postData.layout_template_id = $('#field-layout-template-id').val();
			} else {
				$('#layout-area').removeClass('hidden');
				postData.layout = $('#layout').val();
			}

			Formation.ajaxForSelect({
				url:              baseUrl + '/blogs/articles/layout-tags',
				postData:         postData,
				targetSelect:     '.field-pivot-layout-tag',
				callbackFunction: autoSelectFirstLayoutTagCallback,
			});
		}

		function selectThumbnailImageActions() {
			$('#select-thumbnail-image-type button').click(function(){
				$('#select-thumbnail-image-type button').removeClass('active');
				$(this).addClass('active');

				if ($(this).attr('data-type') == "File") {
					$('#select-thumbnail-image-media-item').hide().fadeOut('fast');
					$('#select-thumbnail-image-file').hide().removeClass('hidden').fadeIn('fast');
				} else {
					$('#select-thumbnail-image-file').hide().fadeOut('fast');
					$('#select-thumbnail-image-media-item').hide().removeClass('hidden').fadeIn('fast');
				}
			});

			$('#select-thumbnail-image-file li').off('click').on('click', function(e){
				$('#field-thumbnail-image-type').val('File');
				$('#field-thumbnail-image-file-id').val($(this).attr('data-file-id'));
				$('#field-thumbnail-image-media-item-id').val('');

				$('#modal').modal('hide');

				$('#thumbnail-image-area img').attr('src', $(this).attr('data-image-url'));
				$('#thumbnail-image-area').hide().removeClass('hidden').fadeIn();
			});

			$('#select-thumbnail-image-media-item li').off('click').on('click', function(e){
				$('#field-thumbnail-image-type').val('Media Item');
				$('#field-thumbnail-image-media-item-id').val($(this).attr('data-media-item-id'));
				$('#field-thumbnail-image-file-id').val('');

				$('#modal').modal('hide');

				$('#thumbnail-image-area img').attr('src', $(this).attr('data-image-url'));
				$('#thumbnail-image-area').hide().removeClass('hidden').fadeIn();
			});
		}

		var autoSelectFirstLayoutTagCallback = function() {
			if ($('#field-content-areas-1-pivot-layout-tag').val() == "")
				$('#field-content-areas-1-pivot-layout-tag').val($('#field-content-areas-1-pivot-layout-tag option:nth-child(2)').attr('value'));
		}

		var contentAreaTemplateCallback = function(item, data) {
			getLayoutTags();
			setupContentTypeFields();

			if (item.find('.field-content-type').val() != "HTML") {
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

			//set up WYSIWYG editor for HTML content field
			var htmlField = item.find('.field-content-html');
			if (htmlField)
				CKEDITOR.replace(htmlField.attr('id'), {
					on: {
						key: function() {
							setTimeout(function(){
								checkForSelectFileMediaItem(htmlField);
							}, 50);
						}
					}
				});

			//set up Markdown content field action preview window
			var markdownField = item.find('.field-content-markdown');
			setupMarkdownField(markdownField);

			//set up modal button triggers
			setupModalTriggers();

			if (addingContentArea) {
				$('html, body').animate({
					scrollTop: (item.offset().top - 30) + 'px'
				}, 750);

				item.find('.field-title').focus();
			}

			addingContentArea = false;
		};

		var deleteContentArea = function() {
			$.ajax({
				url:     baseUrl + '/blogs/articles/delete-content-area/' + contentAreaId,
				success: function(result) {
					if (result == "Success") {
						$('#modal-secondary').hide();
						$('#select-content-area li[data-content-area-id="'+contentAreaId+'"]').remove();
					}
				}
			});
		}

		function selectContentAreaActions() {
			$('#select-content-area li').off('click').on('click', function(e){
				if (!$(e.target).hasClass('delete')) {
					if ($(this).hasClass('new')) {
						addingContentArea = true;
						Formation.loadNewTemplate('#content-areas', contentAreaTemplateCallback);
					} else {
						$.ajax({
							url:      baseUrl + '/blogs/articles/get-content-area/' + $(this).attr('data-content-area-id'),
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

		function setupContentTypeFields() {
			$('.field-content-type').off('change').on('change', function(){
				if ($(this).val() == "HTML") {
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

		function setupContentFields() {
			$('.field-content-html, .field-content-markdown').off('change').on('change', function(){
				$(this).parents('fieldset').find('.field-content').val($(this).val());
			});
		}

		function publishedCheckedCallback(checked) {
			if (checked)
				$('#field-published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#field-published-at').val('');
		}
	</script>

	@include(Fractal::view('partials.markdown_preview', true))

	{{ Form::openResource() }}

		<div class="row button-menu">
			<div class="col-md-12">
				@if (isset($update) && $update)
					<a href="{{ $articleUrl }}" class="btn btn-default right-padded pull-right">
						<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Fractal::lang('labels.viewArticle') }}
					</a>
				@endif

				<a href="{{ Fractal::url('blogs/articles') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-list"></span>&nbsp; {{ Fractal::lang('labels.returnToArticlesList') }}
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('title') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('slug') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('layout_template_id', 'select', [
					'label'       => 'Layout Template',
					'options'     => Form::prepOptions(Regulus\Fractal\Models\Content\LayoutTemplate::orderBy('static', 'desc')->orderBy('name')->get(), ['id', 'name']),
					'null-option' => 'Custom Layout',
				]) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('layout', 'textarea', [
					'id-field-container'    => 'layout-area',
					'class-field-container' => HTML::hiddenArea(Form::value('layout_template_id') != "", true),
					'class-field'           => 'tab',
				]) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::hidden('thumbnail_image_type') }}

				{{ Form::hidden('thumbnail_image_file_id') }}
				{{ Form::hidden('thumbnail_image_media_item_id') }}

				<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="blogs/articles/select-thumbnail-image{{ (isset($id) ? '/'.$id : '') }}" data-modal-ajax-action="get" data-modal-callback-function="selectThumbnailImageActions">
					<span class="glyphicon glyphicon-picture"></span>&nbsp; {{ Fractal::lang('labels.selectThumbnailImage') }}
				</a>
			</div>
			<div class="col-md-8">
				<div id="thumbnail-image-area"{{ HTML::hiddenArea(!isset($article) || !$article->hasThumbnailImage()) }}>
					<img src="{{ (isset($article) ? $article->getThumbnailImageUrl() : '') }}" alt="Thumbnail Image" title="Thumbnail Image" class="thumbnail-image" />

					<a href="" class="btn btn-danger vertical-align-top" id="remove-thumbnail-image">
						<span class="glyphicon glyphicon-remove"></span>&nbsp; {{ Fractal::lang('labels.removeThumbnailImage') }}
					</a>
				</div><!-- /#thumbnail-image-area -->
			</div>
		</div>

		{{-- Content Areas --}}
		<div id="content-areas" data-template-id="content-area-template"></div>

		@include(Fractal::view('blogs.articles.templates.content_area', true))

		<div class="row">
			<div class="col-md-12">
				<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="blogs/articles/add-content-area{{ (isset($id) ? '/'.$id : '') }}" data-modal-ajax-action="get" data-modal-callback-function="selectContentAreaActions">
					<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Fractal::lang('labels.addContentArea') }}
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('categories.pivot.', 'select', [
					'label'    => Fractal::lang('labels.categories'),
					'multiple' => true,
					'options'  => Form::prepOptions(Regulus\Fractal\Models\Blogs\Category::orderBy('name')->get(), ['id', 'name']),
				]) }}
			</div>
		</div>

		<div class="row clear{{ HTML::hiddenArea(!Fractal::getSetting('Enable Article Comments', true), true) }}">
			<div class="col-md-2">
				<div class="form-group">
					{{ Form::field('comments_enabled', 'checkbox') }}
				</div>
			</div>
		</div>

		<div class="row clear">
			<div class="col-md-2">
				<div class="form-group">
					{{ Form::field('published', 'checkbox', [
						'data-checked-show'      => '.published-at-area',
						'data-show-hide-type'    => 'visibility',
						'data-callback-function' => 'publishedCheckedCallback',
					]) }}
				</div>
			</div>
			<div class="col-md-3 published-at-area{{ HTML::invisibleArea(!Form::value('published', 'checkbox'), true) }}">
				<div class="form-group">
					<div class="input-group date date-time-picker">
						{{ Form::text('published_at', null, [
							'class'       => 'date',
							'placeholder' => 'Date/Time Published',
						]) }}

						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Fractal::lang('labels.article')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop