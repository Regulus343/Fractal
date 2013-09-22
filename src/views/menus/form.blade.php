@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){
			$('.item-type').change(function(){
				var itemID = $(this).attr('id').replace('item-', '').replace('-type', '');
				if ($(this).val() == "URI") {
					$('#item-'+itemID+'-uri-area').removeClass('hidden');
					$('#item-'+itemID+'-page-area').addClass('hidden');
				} else if ($(this).val() == "Content Page") {
					$('#item-'+itemID+'-uri-area').addClass('hidden');
					$('#item-'+itemID+'-page-area').removeClass('hidden');
				} else {
					$('#item-'+itemID+'-uri-area').addClass('hidden');
					$('#item-'+itemID+'-page-area').addClass('hidden');
				}
			});
		});
	</script>

	{{ Aquanode\Formation\Formation::openResource() }}
		{{ Aquanode\Formation\Formation::field('name') }}

		@if (Regulus\SolidSite\SolidSite::developer())
			<div class="form-control">
				{{ Aquanode\Formation\Formation::field('cms', 'checkbox') }}
			</div>
		@endif

		@foreach ($menu->items as $menuItem)
			<fieldset>
				{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.label') }}

				{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.type', 'select', array('class' => 'item-type', 'options' => $typeOptions, 'null-option' => 'Select a type')) }}

				<div id="item-{{ $menuItem->id }}-uri-area"{{ HTML::hiddenArea(Form::value('item.'.$menuItem->id.'.type') != "URI") }}>
					{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.uri', 'text', array('label' => 'URI')) }}
				</div>

				<div id="item-{{ $menuItem->id }}-page-area"{{ HTML::hiddenArea(Form::value('item.'.$menuItem->id.'.type') != "Content Page") }}>
					{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.page_id', 'select', array('label' => 'Page', 'options' => $pageOptions, 'null-option' => 'Select a page')) }}
				</div>

				{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.parent_id', 'select', array('label' => 'Parent Menu Item', 'options' => $menuItemOptions, 'null-option' => 'Select a parent menu item')) }}

				{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.display_order', 'select', array('options' => Aquanode\Formation\Formation::numberOptions(1, 100))) }}

				{{ Aquanode\Formation\Formation::field('item.'.$menuItem->id.'.active', 'checkbox') }}
			</fieldset>
		@endforeach

		<button class="btn btn-lg btn-primary btn-block" type="submit">Update Menu</button>
	{{ Aquanode\Formation\Formation::close() }}

@stop