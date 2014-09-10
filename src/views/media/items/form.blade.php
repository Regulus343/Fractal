@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))
	
	<script type="text/javascript">
		var fileTypeExtensions = {{ json_encode($fileTypeExtensions) }};

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

			$('#description-type').change(function(){
				if ($(this).val() == "HTML") {
					$('.html-description-area').show().removeClass('hidden');
					$('.markdown-description-area').hide();
				} else {
					$('.markdown-description-area').show().removeClass('hidden');
					$('.html-description-area').hide();
				}
			});

			if ($('#description-type').val() == "HTML")
				$('#description-html').val($('#description').val());
			else
				$('#description-markdown').val($('#description').val());

			$('form').submit(function(e){
				if ($('#description-type').val() == "HTML")
					$('#description').val(CKEDITOR.instances[$('#description-html').attr('id')].getData());
				else
					$('#description').val($('#description-markdown').val());
			});
		});

		function publishedCheckedCallback(checked) {
			if (checked)
				$('#published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#published-at').val('');
		}
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/file-upload', 'regulus/fractal') }}"></script>

	@include(Fractal::view('partials.markdown_preview', true))

	{{ Form::openResource(array('files' => true)) }}

		<div class="row button-menu">
			<div class="col-md-12">
				@if (isset($update) && $update)
					<a href="{{ $itemUrl }}" target="_blank" class="btn btn-default right-padded pull-right">
						<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.viewItem') }}
					</a>
				@endif

				<a href="{{ Fractal::url('media/items') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-list"></span>&nbsp; {{ Lang::get('fractal::labels.returnToItemsList') }}
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('file', 'file', array('class-field' => 'file-upload-button')) }}
			</div>
			<div class="col-md-4" id="thumbnail-image-area">
				{{ Form::field('file2', 'file', array(
					'class-field' => 'file-upload-button',
					'label'       => 'Thumbnail Image',
					'data-file-type-field' => 'Image')) }}
			</div>
			<div class="col-md-4">
				{{ Form::field('file_type_id', 'select', array(
					'label'          => 'File Type',
					'options'        => Form::prepOptions(Regulus\Fractal\Models\FileType::orderBy('name')->get(), array('id', 'name')),
					'null-option'    => 'None',
					'disabled-field' => 'disabled',
				)) }}

				{{ Form::hidden('file_type_id_hidden') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-4 padding-vertical-10px">
				{{ Form::field('hosted_externally', 'checkbox', array(
					'label'                  => 'Media Hosted Externally',
					'data-checked-show'      => '.media-hosted-area',
					'data-show-hide-type'    => 'display',
					'data-callback-function' => 'publishedCheckedCallback'
				)) }}
			</div>

			<div class="media-hosted-area{{ (!Form::value('hosted_externally', 'checkbox') ? ' hidden' : '') }}">
				<div class="col-md-4">
					{{ Form::field('hosted_type', 'select', array(
						'options' => Form::simpleOptions(array('URL', 'YouTube', 'Vimeo', 'SoundCloud')),
					)) }}
				</div>
				<div class="col-md-4">
					{{ Form::field('hosted_uri', 'text', array('label' => 'Hosted URI')) }}
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
				{{ Form::field('media_type_id', 'select', array(
					'label'          => 'Media Type',
					'options'        => $mediaTypeOptions,
					'null-option'    => 'None',
				)) }}
			</div>
			<div class="col-md-3">
				{{ Form::field('description_type', 'select', array(
					'options' => Form::simpleOptions(array('HTML', 'Markdown')),
				)) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('description_html', 'textarea', array(
					'label'                 => 'Description',
					'class-field-container' => 'html-description-area'.(Form::value('description_type') != "HTML" ? ' hidden' : ''),
					'class-field'           => 'ckeditor',
				)) }}

				{{ Form::field('description_markdown', 'textarea', array(
					'label'                 => 'Description',
					'class-field-container' => 'markdown-description-area'.(Form::value('description_type') != "Markdown" ? ' hidden' : ''),
					'class-field'           => 'tab',
				)) }}

				{{ Form::hidden('description') }}
			</div>
		</div>

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
				{{ Form::field(Form::submitResource(Lang::get('fractal::labels.mediaItem')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop