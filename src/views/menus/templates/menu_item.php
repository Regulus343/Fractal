<script id="menu-item-template" type="text/x-handlebars-template">
	<fieldset id="menu-item-{{number}}" data-item-number="{{number}}" data-item-id="{{id}}">
		<legend><?=Lang::get('fractal::labels.menuItem')?></legend>

		<?=Form::hidden('items.{{number}}.id')?>

		<div class="row">
			<div class="col-md-3">
				<?=Form::field('items.{{number}}.label')?>
			</div>

			<div class="col-md-3">
				<?=Form::field('items.{{number}}.parent_id', 'select', array(
					'label'       => 'Parent Menu Item',
					'options'     => isset($menu) ? Form::prepOptions($menu->items, array('id', 'label')) : [],
					'null-option' => 'Select a parent menu item',
				))?>
			</div>

			<div class="col-md-2">
				<?=Form::field('items.{{number}}.type', 'select', array('options' => $typeOptions, 'null-option' => 'Select a type'))?>
			</div>

			<div class="col-md-4">
				<div class="uri-area hidden">
					<?=Form::field('items.{{number}}.uri', 'text', array('label' => 'URI'))?>
				</div>

				<div class="page-area hidden">
					<?=Form::field('items.{{number}}.page_id', 'select', array(
						'label'       => 'Page',
						'options'     => $pageOptions,
						'null-option' => 'Select a page',
					))?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-2">
				<?=Form::field('items.{{number}}.display_order', 'select', array('options' => Form::numberOptions(1, 100)))?>
			</div>

			<div class="col-md-3">
				<?=Form::field('items.{{number}}.auth_status', 'select', array('options' => array('All', 'Logged In', 'Logged Out')))?>
			</div>

			<div class="col-md-4">
				<?=Form::field('items.{{number}}.auth_roles')?>
			</div>

			<div class="col-md-3 checkbox-area-top-pad" style="padding-top: 30px;">
				<?=Form::field('items.{{number}}.active', 'checkbox')?>
			</div>
		</div>

		<?php if (Site::developer()) { ?>
			<div class="row">
				<div class="col-md-6">
					<?=Form::field('items.{{number}}.icon')?>
				</div>

				<div class="col-md-6">
					<?=Form::field('items.{{number}}.class')?>
				</div>
			</div>
		<?php } else { ?>
			<?=Form::hidden('items.{{number}}.icon')?>
			<?=Form::hidden('items.{{number}}.class')?>
		<?php } ?>

		<a href="" class="btn btn-danger btn-xs remove-template-item pull-right">
			<span class="glyphicon glyphicon-remove-circle"></span>&nbsp; <?=Lang::get('fractal::labels.removeMenuItem')?>
		</a>
	</fieldset>
</script>