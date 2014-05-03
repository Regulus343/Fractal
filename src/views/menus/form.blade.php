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

			<?php /*@foreach (Form::getDefaultsObject('items') as $item)
				console.log('{{ $item->id }}');

				var source   = $("#item-template").html();
				var template = Handlebars.compile(source);
				var context  = {id: "{{ $item->id }}"};
				var html     = template(context);

				$('#menu-items').append(html);

				@foreach ($item as $field => $value)
					$('#items-{{ $item->id }}-{{ str_replace('_', '-', $field) }}').val('{{ $value }}');
					//console.log('{{ $field }}');
					//console.log('{{ $value }}');
					//console.log('------------');
				@endforeach

				@if ($item->type == "URI")
					$('#item-{{ $item->id }} .uri-area').removeClass('hidden');
					$('#item-{{ $item->id }} .page-area').addClass('hidden');
				@else
					$('#item-{{ $item->id }} .uri-area').addClass('hidden');
					$('#item-{{ $item->id }} .page-area').removeClass('hidden');
				@endif
			@endforeach*/ ?>
		});
	</script>

	{{ Form::openResource() }}

		<div class="row">
			<div class="col-md-12">
				{{ Form::field('name') }}
			</div>
		</div>

		@if (Site::developer())
			<div class="row">
				<div class="col-md-12">
					{{ Form::field('cms', 'checkbox', array('label' => 'CMS')) }}
				</div>
			</div>
		@endif

		{{-- Menu Items --}}
		@include(Fractal::view('menus.templates.menu_item', true))

		<div id="menu-items"></div>

		<a href="" class="btn btn-default pull-right">
			<span class="glyphicon glyphicon-plus"></span>&nbsp; {{ Lang::get('fractal::labels.addMenuItem') }}
		</a>
		<div class="clear"></div>

		<?php /*@if (isset($menu))
			@foreach ($menu->items as $item)
				<fieldset>
					<div class="row">
						<div class="col-md-12">
							{{ Form::field('items.'.$item->id.'.label') }}
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							{{ Form::field('items.'.$item->id.'.type', 'select', array('class' => 'item-type', 'options' => $typeOptions, 'null-option' => 'Select a type')) }}
						</div><div class="col-md-6">
							<div id="item-{{ $item->id }}-uri-area"{{ HTML::hiddenArea(Form::value('items.'.$item->id.'.type') != "URI") }}>
								{{ Form::field('items.'.$item->id.'.uri', 'text', array('label' => 'URI')) }}
							</div>

							<div id="item-{{ $item->id }}-page-area"{{ HTML::hiddenArea(Form::value('items.'.$item->id.'.type') != "Content Page") }}>
								{{ Form::field('items.'.$item->id.'.page_id', 'select', array('label' => 'Page', 'options' => $pageOptions, 'null-option' => 'Select a page')) }}
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							{{ Form::field('items.'.$item->id.'.parent_id', 'select', array('label' => 'Parent Menu Item', 'options' => Form::prepOptions($menu->items, array('id', 'label')), 'null-option' => 'Select a parent menu item')) }}
						</div><div class="col-md-6">
							{{ Form::field('items.'.$item->id.'.display_order', 'select', array('options' => Form::numberOptions(1, 100))) }}
						</div>
					</div>

					@if (Site::developer())
						<div class="row">
							<div class="col-md-6">
								{{ Form::field('items.'.$item->id.'.icon') }}
							</div><div class="col-md-6">
								{{ Form::field('items.'.$item->id.'.class') }}
							</div>
						</div>
					@else
						{{ Form::hidden('items.'.$item->id.'.icon') }}
						{{ Form::hidden('items.'.$item->id.'.class') }}
					@endif

					@if (Auth::is('admin'))
						<div class="row">
							<div class="col-md-6">
								{{ Form::field('items.'.$item->id.'.auth_status', 'select', array('options' => array('All', 'Logged In', 'Logged Out'))) }}
							</div><div class="col-md-6">
								{{ Form::field('items.'.$item->id.'.auth_roles') }}
							</div>
						</div>
					@else
						{{ Form::hidden('items.'.$item->id.'.auth_status') }}
						{{ Form::hidden('items.'.$item->id.'.auth_roles') }}
					@endif

					{{ Form::field('items.'.$item->id.'.active', 'checkbox') }}

					{{ Form::hidden('items.'.$item->id.'.id') }}
				</fieldset>
			@endforeach
		@endif*/ ?>

		{{ Form::field(Form::submitResource(Lang::get('fractal::labels.menu'), (isset($update) && $update)), 'button') }}
	{{ Form::close() }}

@stop