@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript" src="{{ Site::js('nested-sortable', 'regulus/fractal') }}"></script>
	<script type="text/javascript">
		var languageKeyOptions = $.parseJSON('{!! json_encode($languageKeyOptions) !!}');

		$(document).ready(function()
		{
			Fractal.preventBackspaceNavigation();

			Handlebars.registerHelper('dynamicLabel', function(languageKey)
			{
				var languageKeyOptions = $.parseJSON('{!! json_encode($languageKeyOptions) !!}');

				languageKeyLabel = languageKeyOptions[languageKey];

				return new Handlebars.SafeString(languageKeyLabel);
			});

			$('#menu-items').nestedSortable({
				handle:           'fieldset',
				items:            'li.item',
				listType:         'ul',
				toleranceElement: '> fieldset',
				maxLevels:        3,
				beforeStop:       function()
				{
					setMenuItemPositions();
				}
			});

			Formation.loadTemplates('#menu-items', $.parseJSON('{!! Form::getJsonValues('items') !!}'), menuItemTemplateCallback);

			$('.add-menu-item').click(function(e)
			{
				e.preventDefault();

				Formation.loadNewTemplate('#menu-items', menuItemTemplateCallback);
			});
		});

		var menuItemLevel = 0;
		var menuItemTemplateCallback = function(item, data)
		{
			if (data !== null)
			{
				if (data.label_language_key != "")
				{
					item.find('.field-label-type').val('Language Key');
					item.find('.label-language-key-area').removeClass('hidden');
					item.find('.label-text-area').addClass('hidden');
				}
				else
				{
					item.find('.field-label-type').val('Text');
					item.find('.label-language-key-area').addClass('hidden');
					item.find('.label-text-area').removeClass('hidden');
				}

				if (data.icon != "")
				{
					item.find('.field-label-type').val('Language Key');
					item.find('.label-language-key-area').removeClass('hidden');
					item.find('.label-text-area').addClass('hidden');
				}
				else
				{
					item.find('.field-label-type').val('Text');
					item.find('.label-language-key-area').addClass('hidden');
					item.find('.label-text-area').removeClass('hidden');
				}

				if (data.type != "")
				{
					if (data.type == "URI")
					{
						item.find('.uri-area').removeClass('hidden');
						item.find('.page-area').addClass('hidden');
					}
					else
					{
						item.find('.uri-area').addClass('hidden');
						item.find('.page-area').removeClass('hidden');
					}
				}
			}

			item.find('.field-label-type').change(function()
			{
				if ($(this).val() == "Language Key")
				{
					item.find('.label-language-key-area').removeClass('hidden');
					item.find('.label-text-area').addClass('hidden');
				}
				else
				{
					item.find('.label-language-key-area').addClass('hidden');
					item.find('.label-text-area').removeClass('hidden');
				}
			});

			item.find('.field-label').keyup(function()
			{
				var fieldset = $(this).parents('fieldset');

				fieldset.find('legend').html($(this).val());
				fieldset.find('h2').html($(this).val());
			});

			item.find('.field-label-language-key').change(function()
			{
				var languageKeyLabel = languageKeyOptions[$(this).val()];

				if (languageKeyLabel === undefined)
					languageKeyLabel = "Menu Item";

				var fieldset = $(this).parents('fieldset');

				fieldset.find('legend').html(languageKeyLabel);
				fieldset.find('h2').html(languageKeyLabel);
			});

			item.find('.field-type').change(function()
			{
				if ($(this).val() == "URI") {
					item.find('.uri-area').removeClass('hidden');
					item.find('.page-area').addClass('hidden');
				} else if ($(this).val() == "Content Page") {
					item.find('.uri-area').addClass('hidden');
					item.find('.page-area').removeClass('hidden');
				} else {
					item.find('.uri-area').addClass('hidden');
					item.find('.page-area').addClass('hidden');
				}
			});

			item.find('.field-icon').change(function()
			{
				setIcon($(this));
			});

			setIcon(item.find('.field-icon'));

			item.find('.btn-expand').click(function(e)
			{
				e.preventDefault();

				expandItem($(this).parents('fieldset'));
			});

			item.find('.btn-collapse').click(function(e)
			{
				e.preventDefault();

				collapseItem($(this).parents('fieldset'));
			});

			item.dblclick(function()
			{
				var item = $(this);

				if (item.data('expanded'))
					collapseItem(item);
				else
					expandItem(item);
			});

			Fractal.initButtonDropdownFields(item);

			if (data === null)
			{
				$('html, body').animate({
					scrollTop: (item.offset().top - 30) + 'px'
				}, 750);

				//set display order to greatest value
				var displayOrder = 0;
				$('#menu-items fieldset').each(function()
				{
					var fieldParentId     = $(this).find('.field-parent-id').val();
					var fieldDisplayOrder = parseInt($(this).find('.field-display-order').val());
					if ((fieldParentId == "" || fieldParentId == null) && fieldDisplayOrder > displayOrder)
						displayOrder = fieldDisplayOrder;
				});

				displayOrder ++;

				item.find('.field-display-order').val(displayOrder);

				item.find('.field-label').focus();
			}
		};

		function setMenuItemPositions()
		{
			var displayOrder = 1;

			$('ul#menu-items li.item').each(function()
			{
				var parents = $(this).parents('li.item');

				if (parents.length)
				{
					$(this).find('.field-parent-id').val($(parents[0]).find('.field-id').val());
				}
				else
				{
					$(this).find('.field-parent-id').val('');
				}

				$(this).find('.field-display-order').val(displayOrder);

				displayOrder ++;
			});
		}

		function expandItem(item)
		{
			item.find('.collapsed-area').addClass('absolute').slideUp(350);
			item.find('.expanded-area').hide().removeClass('hidden').removeClass('invisible').slideDown(350);

			item.find('.btn-expand').hide();
			item.find('.btn-collapse').removeClass('hidden').show();

			item.data('expanded', 1);
		}

		function collapseItem(item)
		{
			item.find('.expanded-area').addClass('invisible').slideUp(350);
			item.find('.collapsed-area').hide().removeClass('hidden').removeClass('absolute').slideDown(350);

			item.find('.btn-collapse').hide();
			item.find('.btn-expand').removeClass('hidden').show();

			item.data('expanded', 0);
		}

		function setIcon(iconField)
		{
			var icon = iconField.val();

			var fieldsets = iconField.parents('fieldset');

			if (icon != "")
				$(fieldsets[0]).find('h2 i').attr('class', 'fa fa-'+icon);
			else
				$(fieldsets[0]).find('h2 i').attr('class', 'fa fa-th');
		}
	</script>

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('name') !!}
			</div>
		</div>

		@if (Site::developer())
			<div class="row">
				<div class="col-md-12">
					{!! Form::field('cms', 'checkbox', ['label' => 'CMS']) !!}
				</div>
			</div>
		@endif

		{{-- Menu Items --}}
		<ul id="menu-items" data-template-id="menu-item-template"></ul>

		@include(Fractal::view('content.menus.templates.menu_item', true))

		<a href="" class="btn btn-primary add-menu-item pull-right">
			<i class="fa fa-plus-circle"></i> {{ Fractal::trans('labels.add_item', ['item' => Fractal::transChoice('labels.menu_item')]) }}
		</a>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.menu', 1)), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop