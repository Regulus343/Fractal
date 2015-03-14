{{-- jQuery --}}

<script type="text/javascript" src="http://code.jquery.com/jquery-2.1.3.min.js"></script>

{{-- jQuery UI --}}

<script type="text/javascript" src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>

{{-- Select2 --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('select2', 'regulus/fractal') }}" />
<link type="text/css" rel="stylesheet" href="{{ Site::css('select2-bootstrap', 'regulus/fractal') }}" />
<script type="text/javascript" src="{{ Site::js('select2.min', 'regulus/fractal') }}"></script>

{{-- Moment --}}

<script type="text/javascript" src="{{ Site::js('moment.min', 'regulus/fractal') }}"></script>

{{-- Bootstrap CSS / JS --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap.min', 'regulus/fractal') }}" />
<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-theme.min', 'regulus/fractal') }}" />
<script type="text/javascript" src="{{ Site::js('bootstrap.min', 'regulus/fractal') }}"></script>

{{-- Bootstrap Date Time Picker CSS --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-datetimepicker.min', 'regulus/fractal') }}" />

{{-- Bootstrap Date Time Picker JS --}}

<script type="text/javascript" src="{{ Site::js('bootstrap-datetimepicker.min', 'regulus/fractal') }}"></script>

{{-- CK Editor --}}

@if (Site::get('wysiwyg'))
	<script type="text/javascript" src="{{ Site::asset('libraries/ckeditor/ckeditor.js', false, 'regulus/fractal') }}"></script>
@endif

{{-- Handlebars --}}

<script type="text/javascript" src="{{ Site::js('handlebars.min', 'regulus/fractal') }}"></script>

{{-- Inflection --}}

<script type="text/javascript" src="{{ Site::js('inflection', 'regulus/fractal') }}"></script>

{{-- Date Time Picker CSS --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('bootstrap-datetimepicker.min', 'regulus/fractal') }}" />

{{-- Date Time Picker JS --}}

<script type="text/javascript" src="{{ Site::js('bootstrap-datetimepicker.min', 'regulus/fractal') }}"></script>

{{-- Gridster --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('jquery.gridster', 'regulus/fractal') }}" />
<script type="text/javascript" src="{{ Site::js('jquery.gridster', 'regulus/fractal') }}"></script>

{{-- Audio JS --}}

<script type="text/javascript" src="{{ Site::asset('libraries/audiojs/audio.min.js', false, 'regulus/fractal') }}"></script>

{{-- SolidSite JS --}}

@include('solid-site::load_js')

{{-- Formation JS --}}

@include('formation::load_js')

{{-- Markdown --}}

<script type="text/javascript" src="{{ Site::js('markdown.converter', 'regulus/fractal') }}"></script>
<script type="text/javascript" src="{{ Site::js('markdown.sanitizer', 'regulus/fractal') }}"></script>

{{-- Chart JS --}}

<script type="text/javascript" src="{{ Site::js('chart.min', 'regulus/fractal') }}"></script>

{{-- lightGallery --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('light-gallery', 'regulus/fractal') }}" />
<script type="text/javascript" src="{{ Site::js('light-gallery.min', 'regulus/fractal') }}"></script>

{{-- Fractal CSS --}}

<link type="text/css" rel="stylesheet" href="{{ Site::css('fractal/cms', 'regulus/fractal') }}" />

{{-- Fractal JS --}}

<script type="text/javascript" src="{{ Site::js('fractal/core', 'regulus/fractal') }}"></script>
<script type="text/javascript" src="{{ Site::js('fractal/menu', 'regulus/fractal') }}"></script>

<script type="text/javascript">

	Fractal.baseUrl          = "{{ Fractal::url() }}";

	Fractal.mediaUrl         = "{{ Fractal::mediaUrl() }}";
	Fractal.blogUrl          = "{{ Fractal::blogUrl() }}";

	Fractal.currentUrl       = "{{ Request::url() }}";

	Fractal.labels           = {!! json_encode(Fractal::trans('labels')) !!};
	Fractal.messages         = {!! json_encode(Fractal::trans('messages')) !!};

	Fractal.contentType      = "{{ Fractal::getContentType() }}";

	Fractal.page             = {{ Fractal::getCurrentPage() }};
	Fractal.lastPage         = {{ Fractal::getLastPage() }};
	Fractal.previousLastPage = Fractal.lastPage;

	Fractal.sortField        = "{{ Fractal::getContentTypeFilter('sortField', 'id') }}";
	Fractal.sortOrder        = "{{ Fractal::getContentTypeFilter('sortOrder', 'asc') }}";

	$(document).ready(function()
	{
		Fractal.init();

		@if (Site::get('loadFunction') != null && Site::get('loadFunction') != "")

			{{ Site::get('loadFunction') }};

		@endif
	});

</script>

<script type="text/javascript" src="{{ Site::js('select-helper', 'regulus/fractal') }}"></script>


{{-- Quick Styles --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('quick-styles.min', 'regulus/fractal') }}" />