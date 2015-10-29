<script id="menu-item-template" type="text/x-handlebars-template">

	<li class="item">

		<fieldset id="menu-item-{{number}}" data-item-number="{{number}}" data-item-id="{{id}}" data-expanded="0">

			<legend>
				{{#if label}}
					{{label}}
				{{else}}
					{{dynamicLabel label_language_key}}
				{{/if}}
			</legend>

			<?=Form::hidden('items.{{number}}.id')?>
			<?=Form::hidden('items.{{number}}.parent_id')?>
			<?=Form::hidden('items.{{number}}.display_order')?>

			<div class="collapsed-area pull-left">
				<h2>
					<i class="fa fa-th"></i>

					{{#if label}}
						{{label}}
					{{else}}
						{{dynamicLabel label_language_key}}
					{{/if}}
				</h2>
			</div>

			<div class="expanded-area hidden">

				<div class="row">
					<div class="col-md-3">
						<?=Form::field('items.{{number}}.label_type', 'select', [
							'options'     => Form::simpleOptions(['Text', 'Language Key']),
							'null-option' => 'Select a Label Type',
						])?>
					</div>

					<div class="col-md-3 label-text-area hidden">
						<?=Form::field('items.{{number}}.label')?>
					</div>

					<div class="col-md-3 label-language-key-area hidden">
						<?=Form::field('items.{{number}}.label_language_key', 'select', [
							'label'       => Fractal::trans('labels.language_key'),
							'options'     => $languageKeyOptions,
							'null-option' => 'Select a Language Key',
						])?>
					</div>

					<div class="col-md-3">
						<?=Form::label('icon')?>
						<div class="clear"></div>

						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="dropdown-menu-field-value"></span> <span class="caret"></span>
							</button>

							<ul class="dropdown-menu dropdown-menu-field" data-null-option="No Icon">
								<li class="null-option"><a href="" data-value=""></a></li>

								<?php foreach ($iconOptions as $value => $icon) { ?>

									<li><a href="" data-value="<?=$value?>"><?=$icon?></a></li>

								<?php } ?>
							</ul>

							<?=Form::hidden('items.{{number}}.icon')?>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-2">
						<?=Form::field('items.{{number}}.type', 'select', ['options' => $typeOptions, 'null-option' => 'Select a type'])?>
					</div>

					<div class="col-md-4 uri-area hidden">
						<?=Form::field('items.{{number}}.uri', 'text', ['label' => 'URI'])?>
					</div>

					<div class="col-md-3 uri-area hidden">
						<?=Form::field('items.{{number}}.subdomain', 'text', ['label' => 'Subdomain'])?>
					</div>

					<div class="col-md-4 page-area hidden">
						<?=Form::field('items.{{number}}.page_id', 'select', [
							'label'       => Fractal::transChoice('labels.page'),
							'options'     => $pageOptions,
							'null-option' => 'Select a Page',
						])?>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3">
						<?=Form::field('items.{{number}}.auth_status', 'select', ['options' => ['All', 'Logged In', 'Logged Out'], 'null-option' => false])?>
					</div>

					<div class="col-md-3">
						<?=Form::field('items.{{number}}.class')?>
					</div>

					<div class="col-md-3 checkbox-area-top-pad" style="padding-top: 30px;">
						<?=Form::field('items.{{number}}.active', 'checkbox')?>
					</div>
				</div>

			</div><!-- /.expanded-area -->

			<div class="buttons-area">
				<a href="" class="btn btn-default btn-xs btn-expand">
					<i class="fa fa-expand"></i>&nbsp; <?=Fractal::trans('labels.expand')?>
				</a>

				<a href="" class="btn btn-default btn-xs btn-collapse hidden">
					<i class="fa fa-compress"></i>&nbsp; <?=Fractal::trans('labels.collapse')?>
				</a>

				<a href="" class="btn btn-danger btn-xs remove-template-item">
					<i class="fa fa-minus-circle"></i> <?=Fractal::trans('labels.remove_item', ['item' => Fractal::transChoice('labels.menu_item')])?>
				</a>
			</div>

		</fieldset>

	</li>

</script>