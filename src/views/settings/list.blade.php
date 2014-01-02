@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ Form::open() }}

		<?php $lastCategory = "*"; $settingsDisplayed = 0; ?>

		@foreach ($settings as $setting)

			@if ($setting->category != $lastCategory)

				@if ($lastCategory != "*")
					</div>
				@endif

				<div class="row vertical-divider">
					<div class="col-md-12">
						<h3>{{{ ($setting->category != "" ? $setting->category : Lang::get('fractal::labels.general')) }}}</h3>
					</div>
				</div>
				<div class="row">

				<?php $lastCategory = $setting->category; ?>

			@else
				@if (is_integer($settingsDisplayed / 3))
					</div>
					<div class="row">
				@endif
			@endif

			<div class="col-md-4">
				@if ($setting->type == "Boolean")

					{{ Form::field('setting_'.$setting->id, 'radio-set', array('label' => $setting->name, 'options' => Form::booleanOptions())) }}

				@elseif ($setting->type == "Integer")

					<?php $range = true;
					$options     = explode(':', $setting->options);
					if (count($options) == 1) {
						$range   = false;
						$options = explode(', ', $setting->options);
					} ?>

					@if (count($options) >= 2)

						@if ($range)
							{{ Form::field('setting_'.$setting->id, 'select', array(
								'label'   => $setting->name,
								'options' => Form::numberOptions($options[0], $options[1], (isset($options[2]) ? $options[2] : 1)),
							)) }}
						@else
							{{ Form::field('setting_'.$setting->id, 'select', array(
								'label'   => $setting->name,
								'options' => Form::simpleOptions($options),
							)) }}
						@endif

					@else

						{{ Form::field('setting_'.$setting->id, 'number', array('label' => $setting->name)) }}

					@endif

				@else

					{{ Form::field('setting_'.$setting->id, 'text', array('label' => $setting->name)) }}

				@endif
			</div>

			<?php $settingsDisplayed ++; ?>

			@if ($settingsDisplayed == $settings->count())
				</div>
			@endif

		@endforeach
		{{ Form::field(Form::submitResource(Lang::get('fractal::labels.settings'), true), 'button') }}
	{{ Form::close() }}

@stop