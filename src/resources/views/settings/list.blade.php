@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{!! Form::open() !!}

		<?php $lastCategory = "*"; $settingsDisplayed = 0; ?>

		@foreach ($settings as $setting)

			@if ($setting->category != $lastCategory)

				@if ($lastCategory != "*")
					</div>
				@endif

				<div class="row vertical-divider">
					<div class="col-md-12">
						<h3>{{ ($setting->category != "" ? $setting->category : Fractal::trans('labels.general')) }}</h3>
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
				{!! $setting->getField() !!}
			</div>

			<?php $settingsDisplayed ++; ?>

			@if ($settingsDisplayed == $settings->count())
				</div>
			@endif

		@endforeach

		{!! Form::field(Form::submitResource(Fractal::trans('labels.settings'), true), 'button') !!}

	{!! Form::close() !!}

@stop