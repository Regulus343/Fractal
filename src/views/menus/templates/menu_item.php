<script id="menu-item-template" type="text/x-handlebars-template">
	<fieldset id="menu-item-{{number}}" data-item-number="{{number}}">
		<legend><?=Lang::get('fractal::labels.menuItem')?></legend>

		<?=Form::hidden('items.{{number}}.id')?>

		<div class="row">
			<div class="col-md-12">
				<?=Form::field('items.{{number}}.label')?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<?=Form::field('items.{{number}}.type', 'select', array('class' => 'item-type', 'options' => $typeOptions, 'null-option' => 'Select a type'))?>
			</div>

			<div class="col-md-6">
				<div class="uri-area">
					<?=Form::field('items.{{number}}.uri', 'text', array('label' => 'URI'))?>
				</div>

				<div class="page-area">
					<?=Form::field('items.{{number}}.page_id', 'select', array(
						'label'       => 'Page',
						'options'     => $pageOptions,
						'null-option' => 'Select a page',
					))?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<?=Form::field('items.{{number}}.parent_id', 'select', array(
					'label'       => 'Parent Menu Item',
					'options'     => Form::prepOptions($menu->items, array('id', 'label')),
					'null-option' => 'Select a parent menu item',
				))?>
			</div>

			<div class="col-md-6">
				<?=Form::field('items.{{number}}.display_order', 'select', array('options' => Form::numberOptions(1, 100)))?>
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
		<?php }

		if (Auth::is('admin')) { ?>
			<div class="row">
				<div class="col-md-6">
					<?=Form::field('items.{{number}}.auth_status', 'select', array('options' => array('All', 'Logged In', 'Logged Out')))?>
				</div>

				<div class="col-md-6">
					<?=Form::field('items.{{number}}.auth_roles')?>
				</div>
			</div>
		<?php } else { ?>
			<?=Form::hidden('items.{{number}}.auth_status')?>
			<?=Form::hidden('items.{{number}}.auth_roles')?>
		<?php } ?>

		<?=Form::field('items.{{number}}.active', 'checkbox')?>

		<a href="" class="btn btn-default pull-right">
			<span class="glyphicon glyphicon-minus"></span>&nbsp; <?=Lang::get('fractal::labels.remove')?>
		</a>
		<div class="clear"></div>
	</fieldset>
</script>