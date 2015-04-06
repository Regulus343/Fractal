@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var gridster;
		var items = [];

		$(document).ready(function()
		{
			Fractal.preventBackspaceNavigation();

			gridster = $('ul#items').gridster(
			{
				widget_margins:         [10, 10],
				widget_base_dimensions: [96, 96],

				serialize_params: function($w, wgd) {
					return {
						col:    wgd.col,
						row:    wgd.row,
						size_x: wgd.size_x,
						size_y: wgd.size_y,
						id:     $w.attr('data-item-id')
					}
				},

				draggable: {
					stop: function(){
						setItemsOrder();
					}
				}
			}).data('gridster');

			setItemsOrder();
			addItemRemoveAction();

			@if (!empty($items))

				var defaultItems = $.parseJSON('{!! json_encode($items) !!}');
				addInitialItems(defaultItems);

			@endif

			@if (!isset($update) || !$update)
				$('#field-title').keyup(function()
				{
					$('#field-title').val($('#field-title').val().replace(/  /g, ' '));

					var slug = Fractal.strToSlug($('#field-title').val());
					$('#field-slug').val(slug);
				});
			@endif

			Fractal.initSlugField();

			$('#field-description-type').change(function()
			{
				if ($(this).val() == "HTML")
				{
					$('.html-description-area').show().removeClass('hidden');
					$('.markdown-description-area').hide();
				} else {
					$('.markdown-description-area').show().removeClass('hidden');
					$('.html-description-area').hide();
				}
			});

			if ($('#field-description-type').val() == "HTML")
				$('#field-description-html').val($('#field-description').val());
			else
				$('#field-description-markdown').val($('#field-description').val());

			$('form').submit(function(e)
			{
				if ($('#field-description-type').val() == "HTML")
					$('#field-description').val(CKEDITOR.instances[$('#field-description-html').attr('id')].getData());
				else
					$('#field-description').val($('#field-description-markdown').val());
			});
		});

		function addInitialItems(items)
		{
			for (i in items)
			{
				var itemHtml = Formation.getTemplateHtml('#items', items[i]);

				gridster.add_widget(itemHtml, 1, 1);
			}

			setItemsOrder();

			addItemRemoveAction();

			checkImageGalleryOption();
		}

		function addSelectItemAction()
		{
			$('#select-item li').off('click').on('click', function(e)
			{
				var item = {
					'id':         $(this).attr('data-item-id'),
					'fileTypeId': $(this).attr('data-file-type-id'),
					'title':      $(this).attr('data-title'),
					'imageUrl':   $(this).attr('data-image-url')
				};

				var itemHtml = Formation.getTemplateHtml('#items', item);

				gridster.add_widget(itemHtml, 1, 1);

				setItemsOrder();

				addItemRemoveAction();

				checkImageGalleryOption();

				$('#modal').modal('hide');
			});
		}

		function addItemRemoveAction()
		{
			$('#items .remove').off('click').on('click', function()
			{
				gridster.remove_widget($(this).parent('li'));

				setItemsOrder();

				setTimeout(function()
				{
					checkImageGalleryOption();

				}, 500);
			});
		}

		function setItemsOrder()
		{
			items = [];
			var itemWidgets = gridster.sort_by_row_and_col_asc(gridster.serialize());
			for (i in itemWidgets) {
				items.push(itemWidgets[i].id);
			}

			updateItemsField();
		}

		function updateItemsField()
		{
			$('#field-items').val(items.join(','));
		}

		function checkImageGalleryOption()
		{
			var itemsExist = false;
			var imagesOnly = true;

			$('#items li').each(function()
			{
				itemsExist = true;

				if ($(this).attr('data-item-file-type-id') != 1)
					imagesOnly = false;
			});

			if (itemsExist && imagesOnly)
			{
				$('.image-gallery-area').hide().removeClass('hidden').fadeIn('fast');
			} else {
				$('.image-gallery-area').fadeOut('fast');
				$('#field-image-gallery').prop('checked', false);
			}
		}

		function publishedCheckedCallback(checked)
		{
			if (checked)
				$('#field-published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#field-published-at').val('');
		}
	</script>

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('title') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('slug') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('description_type', 'select', [
					'options' => Form::simpleOptions(['HTML', 'Markdown']),
				]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('description_html', 'textarea', [
					'label'                 => 'Description',
					'class-field-container' => 'html-description-area'.(Form::value('description_type') != "HTML" ? ' hidden' : ''),
					'class-field'           => 'ckeditor',
				]) !!}

				{!! Form::field('description_markdown', 'textarea', [
					'label'                 => 'Description',
					'class-field-container' => 'markdown-description-area'.(Form::value('description_type') != "Markdown" ? ' hidden' : ''),
					'class-field'           => 'tab',
				]) !!}

				{!! Form::hidden('description') !!}
			</div>
		</div>

		{{-- Items --}}
		<ul class="image-list gridster" id="items" data-template-id="item-template"></ul>

		{!! Form::hidden('items') !!}
		{!! Form::error('items') !!}

		@include(Fractal::view('media.sets.templates.item', true))

		<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="media/sets/add-item" data-modal-ajax-action="post" data-modal-ajax-data-variables="items" data-modal-callback-function="addSelectItemAction">
			<span class="glyphicon glyphicon-picture"></span>&nbsp; {{ Fractal::trans('labels.add_item', ['item' => Fractal::transChoice('labels.media_item')]) }}
		</a>

		<div class="row clear">
			<div class="col-md-2">
				<div class="form-group">
					{!! Form::field('published', 'checkbox', [
						'label'                  => Fractal::trans('labels.published'),
						'data-checked-show'      => '.published-at-area',
						'data-show-hide-type'    => 'visibility',
						'data-callback-function' => 'publishedCheckedCallback',
					]) !!}
				</div>
			</div>
			<div class="col-md-3 published-at-area{{ HTML::invisibleArea(!Form::value('published', 'checkbox'), true) }}">
				<div class="form-group">
					<div class="input-group date date-time-picker">
						{!! Form::text('published_at', [
							'class'       => 'date',
							'placeholder' => Fractal::trans('labels.date_time_published'),
						]) !!}

						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
			</div>
		</div>

		<div class="row image-gallery-area hidden">
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::field('image_gallery', 'checkbox') !!}
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.media_set')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop