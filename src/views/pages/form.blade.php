@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
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

			$('#layout-template-id').change(function(){
				if ($(this).val() != "")
					$('#layout-area').addClass('hidden');
				else
					$('#layout-area').removeClass('hidden');
			});

			//create load template callback function and load templates
			var contentAreaTemplateCallback = function(item, data) {
				setupContentTypeFields();

				$('#content-areas fieldset').each(function(){
					if (item.find('.field-content-type').val() == "HTML") {
						item.find('.markdown-content-area').addClass('hidden');
						item.find('.html-content-area').removeClass('hidden');

						item.find('.field-content-html').val(data.content);
					} else {
						item.find('.html-content-area').addClass('hidden');
						item.find('.markdown-content-area').removeClass('hidden');

						item.find('.field-content-markdown').val(data.content);
					}
				});
			};

			Formation.loadTemplates('#content-areas', $.parseJSON('{{ json_encode(Form::values('content_areas')) }}'), contentAreaTemplateCallback);
		});

		function setupContentTypeFields() {
			$('.content-type').off('change').on('change', function(){
				if ($(this).val() == "HTML") {
					$(this).parents('fieldset').find('.markdown-content-area').addClass('hidden');
					$(this).parents('fieldset').find('.html-content-area').removeClass('hidden');
				} else {
					$(this).parents('fieldset').find('.html-content-area').addClass('hidden');
					$(this).parents('fieldset').find('.markdown-content-area').removeClass('hidden');
				}
			});
		}
	</script>

	{{ Form::openResource() }}
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
					'options'     => Form::prepOptions(Regulus\Fractal\ContentLayoutTemplate::orderBy('name')->get(), array('id', 'name')),
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

		<div id="content-areas" data-template-id="content-area-template"></div>

		@include(Fractal::view('pages.templates.content_area', true))

		<a href="" class="btn btn-primary pull-right">
			<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.addContentArea') }}
		</a>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('active', 'checkbox') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Lang::get('fractal::labels.page'), (isset($update) && $update)), 'button') }}
			</div>
		</div>
	{{ Form::close() }}

@stop