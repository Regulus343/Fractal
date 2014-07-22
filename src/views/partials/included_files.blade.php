{{-- jQuery --}}
@if (Config::get('fractal::loadJquery'))

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>

@endif

{{-- jQuery UI --}}
@if (Config::get('fractal::loadJqueryUi'))

	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>

@endif

{{-- Select2 --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('select2', 'regulus/fractal') }}" />
<link type="text/css" rel="stylesheet" href="{{ Site::css('select2-bootstrap', 'regulus/fractal') }}" />
<script type="text/javascript" src="{{ Site::js('select2.min', 'regulus/fractal') }}"></script>

{{-- Moment --}}
<script type="text/javascript" src="{{ Site::js('moment.min', 'regulus/fractal') }}"></script>

{{-- Bootstrap CSS / JS --}}
@if (Config::get('fractal::loadBootstrap'))

	<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap.min', 'regulus/fractal') }}" />
	<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-theme.min', 'regulus/fractal') }}" />
	<script type="text/javascript" src="{{ Site::js('bootstrap.min', 'regulus/fractal') }}"></script>

	{{-- Bootstrap Date Time Picker CSS --}}
	<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-datetimepicker.min', 'regulus/fractal') }}" />

	{{-- Bootstrap Date Time Picker JS --}}
	<script type="text/javascript" src="{{ Site::js('bootstrap-datetimepicker.min', 'regulus/fractal') }}"></script>

@endif



{{-- Boxy --}}
@if (Config::get('fractal::loadBoxy'))

	<link type="text/css" rel="stylesheet" href="{{ Site::css('boxy', 'regulus/fractal') }}" />
	<script type="text/javascript" src="{{ Site::js('jquery.boxy', 'regulus/fractal') }}"></script>

@endif

{{-- CK Editor --}}
@if (Site::get('wysiwyg'))
	<script type="text/javascript" src="{{ Site::asset('plugins/ckeditor/ckeditor.js', false, 'regulus/fractal') }}"></script>
@endif

{{-- Handlebars --}}
<script type="text/javascript" src="{{ Site::js('handlebars.min', 'regulus/fractal') }}"></script>

{{-- Inflection --}}
<script type="text/javascript" src="{{ Site::js('inflection', 'regulus/fractal') }}"></script>

{{-- Date Time Picker CSS --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-datetimepicker.min', 'regulus/fractal') }}" />

{{-- Date Time Picker JS --}}
<script type="text/javascript" src="{{ Site::js('bootstrap-datetimepicker.min', 'regulus/fractal') }}"></script>

{{-- Formation JS --}}
@include('formation::load_js')

{{-- Fractal CSS --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('fractal', 'regulus/fractal') }}" />

{{-- Fractal JS --}}
<script type="text/javascript">
	if (baseUrl === undefined)
		var baseUrl = "{{ Fractal::url() }}";

	var currentUrl       = "{{ Request::url() }}";

	var csrfToken        = "{{ Session::token() }}";

	var fractalLabels    = {{ json_encode(Lang::get('fractal::labels')) }};
	var fractalMessages  = {{ json_encode(Lang::get('fractal::messages')) }};

	var contentType      = "{{ Fractal::getContentType() }}";
	var page             = {{ Fractal::getCurrentPage() }};
	var lastPage         = {{ Fractal::getLastPage() }};
	var previousLastPage = lastPage;

	var sortField        = "{{ Fractal::getContentTypeFilter('sortField', 'id') }}";
	var sortOrder        = "{{ Fractal::getContentTypeFilter('sortOrder', 'asc') }}";

	function strToSlug(string) {
		var slug = string.toLowerCase()
			.replace(/!/g, '').replace(/\?/g, '').replace(/@/g, '')
			.replace(/#/g, '').replace(/\$/g, '').replace(/%/g, '')
			.replace(/&/g, '').replace(/\*/g, '').replace(/\+/g, '')
			.replace(/=/g, '').replace(/:/g, '').replace(/;/g, '')
			.replace(/\./g, '').replace(/,/g, '').replace(/'/g, '')
			.replace(/"/g, '').replace(/\//g, '-').replace(/\\/g, '-')
			.replace(/\(/g, '-').replace(/\)/g, '-').replace(/\[/g, '-')
			.replace(/\]/g, '-').replace(/ /g, '-').replace(/_/g, '-')
			.replace(/--/g, '-').replace(/--/g, '-');

		return slug;
	}

	$(document).ready(function(){

		if (contentType !== undefined)
			setupContentTable();

		@if (Site::get('loadFunction') != null && Site::get('loadFunction') != "")

			{{ Site::get('loadFunction') }};

		@endif

	});
</script>
<script type="text/javascript" src="{{ Site::js('select-helper', 'regulus/fractal') }}"></script>
<script type="text/javascript" src="{{ Site::js('fractal/core', 'regulus/fractal') }}"></script>

{{-- Quick Styles --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('quick-styles.min', 'regulus/fractal') }}" />