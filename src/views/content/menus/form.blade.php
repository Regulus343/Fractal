@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			Fractal.preventBackspaceNavigation();

			Formation.loadTemplates('#menu-items', $.parseJSON('{!! Form::getJsonValues('items') !!}'), menuItemTemplateCallback);

			$('.add-menu-item').click(function(e){
				e.preventDefault();

				Formation.loadNewTemplate('#menu-items', menuItemTemplateCallback);
			});
		});

		var menuItemLevel = 0;
		var menuItemTemplateCallback = function(item, data) {
			//order menu items under their parent menu items and by display order
			if (Formation.allItemsLoaded())
				formatItemHierarchy();

			if (data !== null && data.type != "") {
				if (data.type == "URI") {
					item.find('.uri-area').removeClass('hidden');
					item.find('.page-area').addClass('hidden');
				} else {
					item.find('.uri-area').addClass('hidden');
					item.find('.page-area').removeClass('hidden');
				}
			}

			item.find('.field-type').change(function(){
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

			item.find('.field-parent-id').change(function(){
				formatItemHierarchy();

				$('html,body').animate({
					scrollTop: (item.offset().top - 180) + 'px'
				}, 750);
			});

			item.find('.field-display-order').change(function(){
				formatItemHierarchy();

				$('html,body').animate({
					scrollTop: (item.offset().top - 180) + 'px'
				}, 750);
			});

			if (data === null) {
				$('html, body').animate({
					scrollTop: (item.offset().top - 30) + 'px'
				}, 750);

				//set display order to greatest value
				var displayOrder = 0;
				$('#menu-items fieldset').each(function(){
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

		function formatItemHierarchy() {
			var menuItemHierarchy = getMenuItemHierarchy();
			var positionedItems   = [];

			for (level = 0; level < menuItemHierarchy.length; level++) {
				var itemNumbers = menuItemHierarchy[level];
				for (var itemNumber in itemNumbers)
				{
					var parentNumber = itemNumbers[itemNumber];
					if ($.inArray(itemNumber, positionedItems) < 0) {
						var item           = $('#menu-items fieldset[data-item-number="'+itemNumber+'"]');
						var lastItemNumber = 0;
						for (var itemNumberMatch in itemNumbers) {
							if (itemNumberMatch != itemNumber) {
								var parentNumberMatch = itemNumbers[itemNumberMatch];
								if (parentNumberMatch == parentNumber && $.inArray(itemNumberMatch, positionedItems) >= 0) {
									var itemMatch      = $('#menu-items fieldset[data-item-number="'+itemNumberMatch+'"]');
									var itemOrder      = parseInt(item.find('.field-display-order').val());
									var itemMatchOrder = parseInt(itemMatch.find('.field-display-order').val());
									if (itemOrder > itemMatchOrder)
										lastItemNumber = itemNumberMatch;
								}
							}
						}

						if (lastItemNumber) {
							item.insertAfter($('#menu-items fieldset[data-item-number="'+lastItemNumber+'"]'));
							positionedItems.push(itemNumber);
						} else {
							if (parentNumber) {
								item.insertAfter($('#menu-items fieldset[data-item-number="'+parentNumber+'"]'));
								positionedItems.push(itemNumber);
							}
						}
					}
				}
			}
		}

		function getMenuItemHierarchy() {
			var menuItemHierarchy = [];

			$('#menu-items fieldset').each(function(){
				menuItemLevel    = 0;
				var itemNumber   = $(this).attr('data-item-number');
				var parentId     = $(this).find('.field-parent-id').val();
				var parentNumber = parseInt($('#menu-items fieldset[data-item-id="'+parentId+'"]').attr('data-item-number'));

				if (isNaN(parentNumber))
					parentNumber = 0;

				addMenuItemLevel(parentId);

				if (menuItemHierarchy[menuItemLevel] === undefined)
					menuItemHierarchy[menuItemLevel] = [];

				if (menuItemHierarchy[menuItemLevel][itemNumber] === undefined)
					menuItemHierarchy[menuItemLevel][itemNumber] = parentNumber;

				if (menuItemLevel)
					$(this).attr('class', 'indent-level-'+menuItemLevel);
				else
					$(this).removeAttr('class');
			});

			return menuItemHierarchy;
		}

		function addMenuItemLevel(id) {
			if (id != "" && id) {
				menuItemLevel ++;
				addMenuItemLevel($('#menu-items fieldset[data-item-id="'+id+'"]').find('.field-parent-id').val());
			}

			return menuItemLevel;
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
		<div id="menu-items" data-template-id="menu-item-template"></div>

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