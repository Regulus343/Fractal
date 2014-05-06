{{-- jQuery --}}
@if (Config::get('fractal::loadJquery'))

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>

@endif

{{-- jQuery UI --}}
@if (Config::get('fractal::loadJqueryUi'))

	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>

@endif

{{-- Bootstrap CSS & JS --}}
@if (Config::get('fractal::loadBootstrap'))

	<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap', 'regulus/fractal') }}" />
	<script type="text/javascript" src="{{ Site::js('bootstrap.min', 'regulus/fractal') }}"></script>

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

{{-- Formation JS --}}
<script type="text/javascript" src="{{ Site::js('formation', 'aquanode/formation') }}"></script>

{{-- Fractal CSS --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('fractal', 'regulus/fractal') }}" />

{{-- Fractal JS --}}
<script type="text/javascript">
	if (baseURL === undefined)
		var baseURL = "{{ Fractal::url() }}";

	var csrfToken        = '{{ Session::token() }}';

	var fractalLabels    = {{ json_encode(Lang::get('fractal::labels')) }};
	var fractalMessages  = {{ json_encode(Lang::get('fractal::messages')) }};

	var contentType      = '{{ Fractal::getContentType() }}';
	var page             = {{ Fractal::getCurrentPage() }};
	var lastPage         = {{ Fractal::getLastPage() }};
	var previousLastPage = lastPage;

	var sortField        = '{{ Fractal::getContentTypeFilter('sortField', 'id') }}';
	var sortOrder        = '{{ Fractal::getContentTypeFilter('sortOrder', 'asc') }}';

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
<script type="text/javascript" src="{{ Site::js('fractal', 'regulus/fractal') }}"></script>

{{-- Quick Styles --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('quick-styles.min', 'regulus/fractal') }}" />