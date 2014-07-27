@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript" src="{{ Site::js('markdown.converter', 'regulus/fractal') }}"></script>
	<script type="text/javascript" src="{{ Site::js('markdown.sanitizer', 'regulus/fractal') }}"></script>
	<script type="text/javascript">
		var converter = Markdown.getSanitizingConverter();

		var markdownContentField;
		var markdownContentUpdateTimer;

		var contentAreaId;

		var addingContentArea = false;

		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#title').keyup(function(){
					$('#title').val($('#title').val().replace(/  /g, ' '));

					var slug = strToSlug($('#title').val());
					$('#slug').val(slug);
				});
			@endif

			$('#slug').keyup(function(){
				var slug = strToSlug($('#slug').val());
				$('#slug').val(slug);
			});

			$('#layout-template-id, #layout').change(function(){
				getLayoutTags();
			});

			if ($('#active').prop('checked') && $('#activated-at').val() == "")
				$('#activated-at').val(moment().format('MM/DD/YYYY hh:mm A'));

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

			if ($('#layout-template-id').val() != "") {
				$('#layout-area').addClass('hidden');
				postData.layout_template_id = $('#layout-template-id').val();
			} else {
				$('#layout-area').removeClass('hidden');
				postData.layout = $('#layout').val();
			}

			Formation.ajaxForSelect({
				url:              baseUrl + '/blog/articles/layout-tags',
				postData:         postData,
				targetSelect:     '.field-pivot-layout-tag',
				callbackFunction: autoSelectFirstLayoutTagCallback,
			});
		}

		var autoSelectFirstLayoutTagCallback = function() {
			if ($('#content-areas-1-pivot-layout-tag').val() == "")
				$('#content-areas-1-pivot-layout-tag').val($('#content-areas-1-pivot-layout-tag option:nth-child(2)').attr('value'));
		}

		var contentAreaTemplateCallback = function(item, data) {
			getLayoutTags();
			setupContentTypeFields();

			if (item.find('.field-content-type').val() != "HTML") {
				item.find('.html-content-area').addClass('hidden');
				item.find('.markdown-content-area').removeClass('hidden');

				if (data !== null)
					item.find('.field-content-markdown').val(data.content);
			} else {
				item.find('.markdown-content-area').addClass('hidden');
				item.find('.html-content-area').removeClass('hidden');

				if (data !== null)
					item.find('.field-content-html').val(data.content);
			}

			//set up WYSIWYG editor for HTML content field
			var htmlField = item.find('.field-content-html');
			if (htmlField)
				CKEDITOR.replace(htmlField.attr('id'));

			//set up Markdown content field action preview window
			var markdownField = item.find('.field-content-markdown');
			if (markdownField) {
				markdownField.on('focus', function(){
					renderMarkdown($(this));
					$('#markdown-preview').fadeIn();
				}).on('keydown', function(e){
					if (e.keyCode == 9) {
						var myValue   = "\t";
						var startPos  = this.selectionStart;
						var endPos    = this.selectionEnd;
						var scrollTop = this.scrollTop;
						this.value    = this.value.substring(0, startPos) + myValue + this.value.substring(endPos,this.value.length);
						this.focus();
						this.selectionStart = startPos + myValue.length;
						this.selectionEnd   = startPos + myValue.length;
						this.scrollTop      = scrollTop;

						e.preventDefault();
					}
				}).on('keyup', function(){
					renderMarkdown($(this));
				}).on('blur', function(){
					$('#markdown-preview').fadeOut();
				});
			}

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
				url:     baseUrl + '/blog/articles/delete-content-area/' + contentAreaId,
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
							url:      baseUrl + '/blogs/article/get-content-area/' + $(this).attr('data-content-area-id'),
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
					$(this).parents('fieldset').find('.html-content-area').removeClass('hidden');
					$(this).parents('fieldset').find('.field-content').val($(this).parents('fieldset').find('.field-content-html').val());
				} else {
					$(this).parents('fieldset').find('.html-content-area').addClass('hidden');
					$(this).parents('fieldset').find('.markdown-content-area').removeClass('hidden');
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

		function renderMarkdown(field) {
			$('#markdown-preview-content').html(converter.makeHtml(field.val()));
			$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);

			markdownContentField       = field;
			markdownContentUpdateTimer = setTimeout(incrementMarkdownContentUpdateTimer, 3000);
			console.log('rendered!' + Math.random(1, 100000));
		}

		function incrementMarkdownContentUpdateTimer() {
			clearTimeout(markdownContentUpdateTimer);

			$.ajax({
				type:     'post',
				url:      baseUrl + '/blog/articles/render-markdown-content',
				data:     {content: markdownContentField.val()},
				success:  function(content) {
					$('#markdown-preview-content').html(content);
					$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);
				}
			});
		}

		function publishedCheckedCallback(checked) {
			if (checked)
				$('#published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#published-at').val('');
		}
	</script>

	<div id="markdown-preview">
		<div id="markdown-preview-bg"></div>
		<div id="markdown-preview-content"></div>
	</div>

	{{ Form::openResource() }}

		<div class="row button-menu">
			<div class="col-md-12">
				@if (isset($update) && $update)
					<a href="{{ $articleUrl }}" target="_blank" class="btn btn-default right-padded pull-right">
						<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.viewArticle') }}
					</a>
				@endif

				<a href="{{ Fractal::url('blog/articles') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-list"></span>&nbsp; {{ Lang::get('fractal::labels.returnToArticlesList') }}
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
				{{ Form::field('layout_template_id', 'select', array(
					'label'       => 'Layout Template',
					'options'     => Form::prepOptions(Regulus\Fractal\Models\ContentLayoutTemplate::orderBy('name')->get(), array('id', 'name')),
					'null-option' => 'Custom Layout'
				)) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('layout', 'textarea', array(
					'id-field-container'    => 'layout-area',
					'class-field-container' => HTML::hiddenArea(Form::value('layout_template_id') != "", true),
					'class-field'           => 'tab'
				)) }}
			</div>
		</div>

		{{-- Content Areas --}}
		<div id="content-areas" data-template-id="content-area-template"></div>

		@include(Fractal::view('blogs.articles.templates.content_area', true))

		<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="blog/articles/add-content-area{{ (isset($id) ? '/'.$id : '') }}" data-modal-ajax-action="get" data-modal-callback-function="selectContentAreaActions">
			<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.addContentArea') }}
		</a>

		<div class="row clear">
			<div class="col-md-2">
				{{ Form::field('published', 'checkbox', array(
					'data-checked-show'      => '.published-at-area',
					'data-show-hide-type'    => 'visibility',
					'data-callback-function' => 'publishedCheckedCallback'
				)) }}
			</div>
			<div class="col-md-3 published-at-area{{ HTML::invisibleArea(!Form::value('published', 'checkbox'), true) }}">
				<div class="form-group">
					<div class="input-group date date-time-picker">
						{{ Form::text('published_at', null, array(
							'class'       => 'date',
							'placeholder' => 'Date/Time Published',
						)) }}

						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Lang::get('fractal::labels.article')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop