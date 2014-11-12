@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		var gridster;
		var items = [];

		$(document).ready(function(){

			gridster = $('ul#items').gridster({
				widget_margins:         [10, 10],
				widget_base_dimensions: [96, 96],
				serialize_params:       function($w, wgd) {
					return {
						col:    wgd.col,
						row:    wgd.row,
						size_x: wgd.size_x,
						size_y: wgd.size_y,
						id:     $w.attr('data-item-id')
					}
				},
				draggable:              {
					stop: function(){
						items = [];
						var itemWidgets = this.sort_by_row_and_col_asc(this.serialize());
						for (i in itemWidgets) {
							items.push(itemWidgets[i].id);
						}

						updateItemsField();
					}
				}
			}).data('gridster');

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
				if ($('#field-description-type').val() == "HTML")
					$('#field-description').val(CKEDITOR.instances[$('#field-description-html').attr('id')].getData());
				else
					$('#field-description').val($('#field-description-markdown').val());
			});

		});

		function addItemActions() {
			$('#select-item li').off('click').on('click', function(e){
				var item = {
					'id':       $(this).attr('data-item-id'),
					'title':    $(this).attr('data-title'),
					'imageUrl': $(this).attr('data-image-url')
				};

				var itemHtml = Formation.getTemplateHtml('#items', item);

				gridster.add_widget(itemHtml, 1, 1);

				items.push($(this).attr('data-item-id'));

				updateItemsField();

				$('#modal').modal('hide');
			});
		}

		function updateItemsField() {
			$('#field-items').val(items.join(','));
		}

		function publishedCheckedCallback(checked) {
			if (checked)
				$('#field-published-at').val(moment().format('MM/DD/YYYY hh:mm A'));
			else
				$('#field-published-at').val('');
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
				{{ Form::field('description_type', 'select', [
					'options' => Form::simpleOptions(['HTML', 'Markdown']),
				]) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('description_html', 'textarea', [
					'label'                 => 'Description',
					'class-field-container' => 'html-description-area'.(Form::value('description_type') != "HTML" ? ' hidden' : ''),
					'class-field'           => 'ckeditor',
				]) }}

				{{ Form::field('description_markdown', 'textarea', [
					'label'                 => 'Description',
					'class-field-container' => 'markdown-description-area'.(Form::value('description_type') != "Markdown" ? ' hidden' : ''),
					'class-field'           => 'tab',
				]) }}

				{{ Form::hidden('description') }}
			</div>
		</div>

		{{-- Items --}}
		<ul class="image-list gridster" id="items" data-template-id="item-template">

			<li data-item-id="5" data-sizex="1" data-sizey="1" data-row="1" data-col="1">
				<img src="http://laravel.local/uploads/media/images/calendar-13.png" alt="" title="" />
			</li>

			<li data-item-id="6" data-sizex="1" data-sizey="1" data-row="1" data-col="1">
				<img src="http://laravel.local/uploads/media/images/calendar-13.png" alt="" title="" />
			</li>

			<li data-item-id="7" data-sizex="1" data-sizey="1" data-row="1" data-col="1">
				<img src="http://laravel.local/uploads/media/images/calendar-13.png" alt="" title="" />
			</li>

		</ul>

		{{ Form::text('items') }}

		@include(Fractal::view('media.sets.templates.item', true))

		<a href="" class="btn btn-primary trigger-modal pull-right" data-modal-ajax-uri="media/sets/add-item{{ (isset($id) ? '/'.$id : '') }}" data-modal-ajax-action="get" data-modal-callback-function="addItemActions">
			<span class="glyphicon glyphicon-picture"></span>&nbsp; {{ Lang::get('fractal::labels.addMediaItem') }}
		</a>

		<div class="row clear">
			<div class="col-md-2">
				{{ Form::field('published', 'checkbox', [
					'data-checked-show'      => '.published-at-area',
					'data-show-hide-type'    => 'visibility',
					'data-callback-function' => 'publishedCheckedCallback'
				]) }}
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
				{{ Form::field(Form::submitResource(Fractal::lang('labels.mediaSet')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop