@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	{{-- Reports --}}
	<script type="text/javascript">

		var chartColors = {
			'Page': {
				fillColor: 'rgba(13, 98, 186, 0.2)',
				strokeColor: 'rgba(13, 98, 186, 1)',
				pointColor:  'rgba(13, 98, 186, 1)',
			},

			'Article': {
				fillColor: 'rgba(214, 11, 62, 0.2)',
				strokeColor: 'rgba(214, 11, 62, 1)',
				pointColor:  'rgba(214, 11, 62, 1)',
			},

			'Item': {
				fillColor: 'rgba(12, 122, 20, 0.2)',
				strokeColor: 'rgba(12, 122, 20, 1)',
				pointColor:  'rgba(12, 122, 20, 1)',
			},

			'1': {
				fillColor: 'rgba(13, 98, 186, 0.2)',
				strokeColor: 'rgba(13, 98, 186, 1)',
				pointColor:  'rgba(13, 98, 186, 1)',
			},

			'2': {
				fillColor: 'rgba(214, 11, 62, 0.2)',
				strokeColor: 'rgba(214, 11, 62, 1)',
				pointColor:  'rgba(214, 11, 62, 1)',
			},

			'3': {
				fillColor: 'rgba(12, 122, 20, 0.2)',
				strokeColor: 'rgba(12, 122, 20, 1)',
				pointColor:  'rgba(12, 122, 20, 1)',
			},

			'4': {
				fillColor: 'rgba(240, 132, 65, 0.2)',
				strokeColor: 'rgba(240, 132, 65, 1)',
				pointColor:  'rgba(240, 132, 65, 1)',
			},

			'5': {
				fillColor: 'rgba(182, 65, 240, 0.2)',
				strokeColor: 'rgba(182, 65, 240, 1)',
				pointColor:  'rgba(182, 65, 240, 1)',
			},
		};

		$(document).ready(function(){

			$('canvas.chart').each(function(){
				$(this).attr('width', $(this).parents('.well').width());
			});

			var charts = {};
			var i;

			@foreach (array_keys($reports) as $report)

				charts.{{ $report }} = {
					chartData: {
						labels:   {{ json_encode($reports[$report]['labels']) }},
						datasets: []
					}
				};

				i = 1;

				@foreach (array_keys($reports[$report]['values']) as $type)

					var dataSet = {
						label:                '{{ $type }}',
						fillColor:            getColor('{{ $type }}', i, 'fill'),
						strokeColor:          getColor('{{ $type }}', i, 'stroke'),
						pointColor:           getColor('{{ $type }}', i, 'point'),
						pointStrokeColor:     "#fff",
						pointHighlightFill:   "#fff",
						pointHighlightStroke: "rgba(220,220,220,1)",
						data:                 {{ json_encode(array_values($reports[$report]['values'][$type])) }}
					};

					i++;

					charts.{{ $report }}.chartData.datasets.push(dataSet);

				@endforeach

			@endforeach

			chartOptions = {
				scaleGridLineColor: 'rgba(0, 0, 0, .2)',
			}

			@foreach (array_keys($reports) as $report)

				charts.{{ $report }}.canvas = document.getElementById('chart-{{ Fractal::toDashed($report) }}').getContext('2d');
				charts.{{ $report }}.chart  = new Chart(charts.{{ $report }}.canvas).Line(charts.{{ $report }}.chartData, chartOptions);

				$('#chart-{{ Fractal::toDashed($report) }}').parent('.chart-area').children('.legend').html(charts.{{ $report }}.chart.generateLegend());

			@endforeach

			$('.chart-hide-on-load').hide();

			$('.chart-selection button').click(function(){
				$(this).parent('.chart-selection').children('.btn').not(this).addClass('btn-default').removeClass('btn-primary');
				$(this).addClass('btn-primary').removeClass('btn-default');

				$(this).parents('.well').find('.chart-area').not('.chart-' + $(this).attr('data-chart')).hide();
				$(this).parents('.well').find('.chart-' + $(this).attr('data-chart')).fadeIn('fast');
			});

		});

		function getColor(type, i, colorType) {
			var itemColor = chartColors[type] !== undefined ? chartColors[type] : chartColors[i];

			if (chartColors[i] === undefined)
				console.log(i);

			return itemColor[colorType + 'Color'];
		}

	</script>

	<h1>{{ Site::titleHeading() }}</h1>

	<div class="row">

		{{-- Total Views --}}
		<div class="col-md-6">
			<div class="well">
				<div class="btn-group chart-selection pull-right">
					<button class="btn btn-xs btn-primary" data-chart="month">Month</button>
					<button class="btn btn-xs btn-default" data-chart="year">Year</button>
				</div>

				<h2>Total Views</h2>

				<div class="charts">
					<div class="chart-area chart-month">
						<canvas id="chart-total-views-month" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>

					<div class="chart-area chart-year chart-hide-on-load">
						<canvas id="chart-total-views-year" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>
				</div>
			</div>
		</div>

		{{-- Unique Views --}}
		<div class="col-md-6">
			<div class="well">
				<div class="btn-group chart-selection pull-right">
					<button class="btn btn-xs btn-primary" data-chart="month">Month</button>
					<button class="btn btn-xs btn-default" data-chart="year">Year</button>
				</div>

				<h2>Unique Views</h2>

				<div class="charts">
					<div class="chart-area chart-month">
						<canvas id="chart-unique-views-month" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>

					<div class="chart-area chart-year chart-hide-on-load">
						<canvas id="chart-unique-views-year" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="row">

		{{-- Popular Content --}}
		<div class="col-md-12">
			<div class="well">
				<div class="btn-group chart-selection pull-right">
					<button class="btn btn-xs btn-primary" data-chart="month">Month</button>
					<button class="btn btn-xs btn-default" data-chart="year">Year</button>
				</div>

				<h2>Popular Content</h2>

				<div class="charts">
					<div class="chart-area chart-month">
						<canvas id="chart-popular-content-month" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>

					<div class="chart-area chart-year chart-hide-on-load">
						<canvas id="chart-popular-content-year" class="chart" height="300"></canvas>

						<div class="legend"></div>
					</div>
				</div>
			</div>
		</div>

	</div>

@stop