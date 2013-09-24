{{-- Success Message --}}
@if (isset($sessionMessages['success']) && $sessionMessages['success'] != "")
	<div class="alert alert-success alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $sessionMessages['success'] }}</div>
	</div>
@endif

@if (isset($messages['success']) && $messages['success'] != "")
	<div class="alert alert-success alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['success'] }}</div>
	</div>
@endif

<div class="alert alert-success hidden" id="message-success"></div>

{{-- Error Message --}}
@if (isset($sessionMessages['error']) && $sessionMessages['error'] != "")
	<div class="alert alert-danger alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $sessionMessages['error'] }}</div>
	</div>
@endif

@if (isset($messages['error']) && $messages['error'] != "")
	<div class="alert alert-danger alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['error'] }}</div>
	</div>
@endif

<div class="alert alert-error hidden" id="message-error"></div>

{{-- Warning Message --}}
@if (isset($sessionMessages['warning']) && $sessionMessages['warning'] != "")
	<div class="alert alert-warning alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $sessionMessages['warning'] }}</div>
	</div>
@endif

@if (isset($messages['warning']) && $messages['warning'] != "")
	<div class="alert alert-warning alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['warning'] }}</div>
	</div>
@endif

<div class="alert alert-warning hidden" id="message-warning"></div>

{{-- General Info Message --}}
@if (isset($sessionMessages['info']) && $sessionMessages['info'] != "")
	<div class="alert alert-info alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $sessionMessages['info'] }}</div>
	</div>
@endif

@if (isset($messages['info']) && $messages['info'] != "")
	<div class="alert alert-info alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<div>{{ $messages['info'] }}</div>
	</div>
@endif

<div class="alert alert-info hidden" id="message-info"></div>