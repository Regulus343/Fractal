{{-- Success Message --}}
@if (Session::get('messages.success') != "")
	<div class="alert alert-success alert-dismissable alert-auto-hide">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ Session::get('messages.success') }}</div>
	</div>
@endif

@if (isset($messages['success']) && $messages['success'] != "")
	<div class="alert alert-success alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['success'] }}</div>
	</div>
@endif

<div class="alert alert-success alert-dismissable-hide hidden" id="message-success">
	<button type="button" class="close">&times;</button>
	<div></div>
</div>

{{-- Error Message --}}
@if (Session::get('messages.error') != "")
	<div class="alert alert-danger alert-dismissable alert-auto-hide">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ Session::get('messages.error') }}</div>
	</div>
@endif

@if (isset($messages['error']) && $messages['error'] != "")
	<div class="alert alert-danger alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['error'] }}</div>
	</div>
@endif

<div class="alert alert-danger alert-top alert-dismissable-hide hidden" id="message-error">
	<button type="button" class="close">&times;</button>
	<div></div>
</div>

{{-- Warning Message --}}
@if (Session::get('messages.warning') != "")
	<div class="alert alert-warning alert-dismissable alert-auto-hide">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ Session::get('messages.warning') }}</div>
	</div>
@endif

@if (isset($messages['warning']) && $messages['warning'] != "")
	<div class="alert alert-warning alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['warning'] }}</div>
	</div>
@endif

<div class="alert alert-warning alert-transparent alert-dismissable-hide hidden" id="message-warning">
	<button type="button" class="close">&times;</button>
	<div></div>
</div>

{{-- General Info Message --}}
@if (Session::get('messages.info') != "")
	<div class="alert alert-info alert-transparent alert-dismissable alert-auto-hide">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ Session::get('messages.info') }}</div>
	</div>
@endif

@if (isset($messages['info']) && $messages['info'] != "")
	<div class="alert alert-info alert-transparent alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['info'] }}</div>
	</div>
@endif

<div class="alert alert-info alert-transparent alert-dismissable-hide hidden" id="message-info">
	<button type="button" class="close">&times;</button>
	<div></div>
</div>