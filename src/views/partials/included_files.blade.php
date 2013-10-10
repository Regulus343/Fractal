{{-- jQuery --}}
@if (Config::get('fractal::loadJquery'))

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>

@endif

{{-- jQuery UI --}}
@if (Config::get('fractal::loadJqueryUI'))

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

{{-- Fractal CSS --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('fractal', 'regulus/fractal') }}" />

{{-- Fractal JS --}}
<script type="text/javascript">
	if (baseURL == undefined) var baseURL = "{{ URL::to('') }}";

	var fractalLabels   = {{ json_encode(Lang::get('fractal::labels')) }};
	var fractalMessages = {{ json_encode(Lang::get('fractal::messages')) }};

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
</script>
<script type="text/javascript" src="{{ Site::js('select-helper', 'regulus/fractal') }}"></script>
<script type="text/javascript" src="{{ Site::js('fractal', 'regulus/fractal') }}"></script>

{{-- Quick Styles --}}
<link type="text/css" rel="stylesheet" href="{{ Site::css('quick-styles.min', 'regulus/fractal') }}" />