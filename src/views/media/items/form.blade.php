@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	<script type="text/javascript">
		var update             = {{ (isset($update) && $update ? 'true' : 'false') }};
		var fileTypeExtensions = {{ json_encode($fileTypeExtensions) }};

		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#field-title').keyup(function(){
					setSlugForTitle();
				}).change(function(){
					setSlugForTitle();
				});
			@else
				$('#field-file').off('change');
			@endif

			$('#field-slug').keyup(function(){
				var slug = strToSlug($('#field-slug').val());
				$('#field-slug').val(slug);
			});

			checkHostedContentType();

			$('#field-hosted-externally').click(function(){
				checkHostedContentType();
			});

			$('#field-hosted-content-type').change(function(){
				checkHostedContentType();
			});

			$('#field-hosted-content-uri').change(function(){
				checkHostedContentUri();
			});

			$('#field-description-type').change(function(){
				if ($(this).val() == "HTML") {
					$('.html-description-area').show().removeClass('hidden');
					$('.markdown-description-area').hide();
					$('.col-markdown-preview-content').hide();
				} else {
					$('.markdown-description-area').show().removeClass('hidden');
					$('.col-markdown-preview-content').show().removeClass('hidden');
					$('.html-description-area').hide();
				}
			});

			if ($('#field-description-type').val() == "HTML")
				CKEDITOR.instances['field-description-html'].setData($('#field-description').val());
			else
				$('#field-description-markdown').val($('#field-description').val());

			$('#field-create-thumbnail').prop('checked', true).attr('readonly', 'readonly');

			$('form').submit(function(e){
				if ($('#field-description-type').val() == "HTML")
					$('#field-description').val(CKEDITOR.instances[$('#field-description-html').attr('id')].getData());
				else
					$('#field-description').val($('#field-description-markdown').val());
			});
		});

		function setSlugForTitle() {
			var slug = strToSlug($('#field-title').val());
			$('#field-slug').val(slug);
		}

		function checkHostedContentType() {
			if ($('#field-hosted-externally').prop('checked'))
			{
				if ($('#field-hosted-content-type').val() == "YouTube")
					$('#field-hosted-content-uri').val($('#field-hosted-content-uri').val().substr(0, 11)).attr('maxlength', 11);
				else
					$('#field-hosted-content-uri').attr('maxlength', false);

				$('.hosted-content-uri-help').fadeOut('fast');

				if ($('#field-hosted-content-type').val() == "YouTube") {
					$('#hosted-content-uri-help-youtube').hide().removeClass('hidden').fadeIn('fast');
				} else if ($('#field-hosted-content-type').val() == "Vimeo") {
					$('#hosted-content-uri-help-vimeo').hide().removeClass('hidden').fadeIn('fast');
				}

				if ($('#field-hosted-content-type').val() == "Vimeo" || $('#field-hosted-content-type').val() == "YouTube")
				{
					var fileTypeId = null;
					$('#field-file-type-id option').each(function(){
						if ($(this).text() == "Video")
							fileTypeId = $(this).attr('value');
					});

					if (fileTypeId)
						$('#field-file-type-id').val(fileTypeId).trigger('change').select2('readonly', true);

					var mediaTypeId = null;
					$('#field-media-type-id option').each(function(){
						if ($(this).text() == "Video")
							mediaTypeId = $(this).attr('value');
					});

					if (mediaTypeId)
						$('#field-media-type-id').val(mediaTypeId).trigger('change');

				} else if ($('#field-hosted-content-type').val() == "SoundCloud")
				{
					var fileTypeId = null;
					$('#field-file-type-id option').each(function(){
						if ($(this).text() == "Audio")
							fileTypeId = $(this).attr('value');
					});

					if (fileTypeId)
						$('#field-file-type-id').val(fileTypeId).trigger('change').select2('readonly', true);

				} else {
					$('#field-file-type-id').select2('readonly', false);
				}
			} else {
				$('#field-file-type-id').select2('readonly', false);
			}

			checkHostedContentUri();
		}

		function checkHostedContentUri() {
			if ($('#field-hosted-content-type').val() == "YouTube" && $('#field-hosted-content-uri').val().length == 11)
			{
				$.ajax({
					url:      'http://gdata.youtube.com/feeds/api/videos/'+ $('#field-hosted-content-uri').val() +'?v=2&alt=jsonc',
					dataType: 'json',
					success:  function(result) {
						$('#field-hosted-content-uri').parents('.form-group').removeClass('has-error');

						$('#field-title').val(result.data.title);
						setSlugForTitle();

						if ($('#field-description-type').val() == "HTML")
							CKEDITOR.instances['field-description-html'].setData(result.data.description);

						if ($('#field-description-type').val() == "Markdown")
							$('#field-description-markdown').val(result.data.description);
					},
					error: function() {
						$('#field-hosted-content-uri').parents('.form-group').addClass('has-error');
						setMainMessage(fractalMessages.errorHostedContentNotFound, 'error');
					}
				});
			}

			if ($('#field-hosted-content-type').val() == "Vimeo" && $('#field-hosted-content-uri').val() != "")
			{
				$.ajax({
					url:      'http://vimeo.com/api/v2/video/'+ $('#field-hosted-content-uri').val() +'.json',
					dataType: 'json',
					success:  function(result) {
						$('#field-hosted-content-uri').parents('.form-group').removeClass('has-error');

						$('#field-title').val(result[0].title);
						setSlugForTitle();

						if ($('#field-description-type').val() == "HTML")
							CKEDITOR.instances['field-description-html'].setData(result[0].description);

						if ($('#field-description-type').val() == "Markdown")
							$('#field-description-markdown').val(result[0].description.replace(/<br \/>/g, ''));

						$('#field-hosted-content-thumbnail-url').val(result[0].thumbnail_medium);
					},
					error: function() {
						$('#field-hosted-content-uri').parents('.form-group').addClass('has-error');
						setMainMessage(fractalMessages.errorHostedContentNotFound, 'error');
					}
				});
			} else {
				$('#field-hosted-content-thumbnail-url').val('');
			}
		}

		function publishedCheckedCallback(checked) {
			if (checked)
				$('#field-published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#field-published-at').val('');
		}
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/file-upload', 'regulus/fractal') }}"></script>

	@include(Fractal::view('partials.markdown_preview', true))

	{{ Form::openResource(array('files' => true)) }}

		<div class="row button-menu">
			<div class="col-md-12">
				@if (isset($update) && $update)
					<a href="{{ $itemUrl }}" class="btn btn-default right-padded pull-right">
						<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Fractal::lang('labels.viewItem') }}
					</a>
				@endif

				<a href="{{ Fractal::url('media/items') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-list"></span>&nbsp; {{ Fractal::lang('labels.returnToItemsList') }}
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('file', 'file', array('class-field' => 'file-upload-button')) }}
			</div>
			<div class="col-md-4" id="thumbnail-image-area">
				{{ Form::field('thumbnail_image', 'file', [
					'class-field'          => 'file-upload-button',
					'label'                => 'Thumbnail Image',
					'data-file-type-field' => 'Image',
				]) }}
			</div>
			<div class="col-md-4">
				{{ Form::field('file_type_id', 'select', [
					'label'          => 'File Type',
					'options'        => Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), array('id', 'name')),
					'null-option'    => 'None',
					'readonly-field' => 'readonly',
				]) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4 padding-vertical-10px">
				{{ Form::field('hosted_externally', 'checkbox', [
					'label'                  => 'Media Hosted Externally',
					'data-checked-show'      => '.media-hosted-area',
					'data-show-hide-type'    => 'display',
					'data-callback-function' => 'publishedCheckedCallback',
				]) }}
			</div>

			<div class="media-hosted-area{{ (!Form::value('hosted_externally', 'checkbox') ? ' hidden' : '') }}">
				<div class="col-md-4">
					{{ Form::field('hosted_content_type', 'select', [
						'options' => Form::simpleOptions(['URL', 'SoundCloud', 'Vimeo', 'YouTube']),
					]) }}
				</div>
				<div class="col-md-4">
					{{ Form::openFieldContainer('hosted_content_uri') }}

						{{ Form::label('hosted_content_uri', 'Hosted Content URI') }}
						{{ Form::text('hosted_content_uri', null, ['label' => 'Hosted URI']) }}

						<div id="hosted-content-uri-help-youtube" class="alert alert-info hosted-content-uri-help{{ HTML::hiddenArea(Form::value('hosted_type') != "YouTube", true) }}">
							https://www.youtube.com/watch?v=<strong class="highlight">y0uTuB3-iDx</strong>&amp;list=xyz
						</div>

						<div id="hosted-content-uri-help-vimeo" class="alert alert-info hosted-content-uri-help{{ HTML::hiddenArea(Form::value('hosted_type') != "Vimeo", true) }}">
							http://vimeo.com/<strong class="highlight">123456789</strong>
						</div>

						<div id="hosted-content-uri-help-soundcloud" class="alert alert-info hosted-content-uri-help{{ HTML::hiddenArea(Form::value('hosted_type') != "SoundCloud", true) }}">
							https://api.soundcloud.com/tracks/<strong class="highlight">123456789</strong> <em>(found in &lt;iframe&gt; Embed URL)</em>
						</div>

					{{ Form::closeFieldContainer('hosted_content_uri') }}

					{{ Form::hidden('hosted_content_thumbnail_url') }}
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3">
				{{ Form::field('title') }}
			</div>
			<div class="col-md-3">
				{{ Form::field('slug') }}
			</div>
			<div class="col-md-3">
				{{ Form::field('media_type_id', 'select', [
					'label'          => 'Media Type',
					'options'        => $mediaTypeOptions,
					'null-option'    => 'None',
				]) }}
			</div>
			<div class="col-md-3">
				{{ Form::field('description_type', 'select', [
					'options' => Form::simpleOptions(['HTML', 'Markdown']),
				]) }}
			</div>
		</div>

		<div class="row padding-bottom-20px">
			<div class="col-md-12 html-description-area{{ HTML::hiddenArea(Form::value('description_type') != "HTML", true) }}">
				{{ Form::field('description_html', 'textarea', [
					'label'       => 'Description',
					'class-field' => 'ckeditor',
				]) }}
			</div>

			<div class="col-md-12 col-lg-6 markdown-description-area{{ HTML::hiddenArea(Form::value('description_type') != "Markdown", true) }}">
				{{ Form::field('description_markdown', 'textarea', [
					'label'       => 'Description',
					'class-field' => 'tab markdown',
				]) }}

				<a href="" class="btn btn-default trigger-modal pull-right" data-modal-ajax-uri="api/view-markdown-guide" data-modal-ajax-action="get">
					<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Fractal::lang('labels.viewMarkdownGuide') }}
				</a>
			</div>

			<div class="col-lg-6 col-markdown-preview-content{{ HTML::hiddenArea(Form::value('description_type') != "Markdown", true) }}">
				<?=Form::label('')?>

				<div class="markdown-preview-content"></div>
			</div>

			{{ Form::hidden('description') }}
		</div>

		{{-- Image Settings --}}
		@include(Fractal::view('partials.image_settings', true))

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
						{{ Form::text('published_at', [
							'class'       => 'date',
							'placeholder' => 'Date/Time Published',
						]) }}

						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-md-offset-4">
				<div class="form-group">
					<div class="input-group date date-picker">
						{{ Form::text('date_created', null, [
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
				{{ Form::field(Form::submitResource(Fractal::lang('labels.mediaItem')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop