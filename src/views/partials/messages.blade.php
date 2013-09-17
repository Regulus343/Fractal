{{-- Success Message --}}
@if (isset($sessionMessages['success']) && $sessionMessages['success'] != "")
	<div class="alert alert-success">
		<div>{{ $sessionMessages['success'] }}</div>
	</div>
@endif

@if (isset($messages['success']) && $messages['success'] != "")
	<div class="alert alert-success">
		<div>{{ $messages['success'] }}</div>
	</div>
@endif

{{-- Error Message --}}
@if (isset($sessionMessages['error']) && $sessionMessages['error'] != "")
	<div class="alert alert-danger">
		<div>{{ $sessionMessages['error'] }}</div>
	</div>
@endif

@if (isset($messages['error']) && $messages['error'] != "")
	<div class="alert alert-danger">
		<div>{{ $messages['error'] }}</div>
	</div>
@endif

{{-- Warning Message --}}
@if (isset($sessionMessages['warning']) && $sessionMessages['warning'] != "")
	<div class="alert alert-warning">
		<div>{{ $sessionMessages['warning'] }}</div>
	</div>
@endif

@if (isset($messages['warning']) && $messages['warning'] != "")
	<div class="alert alert-warning">
		<div>{{ $messages['warning'] }}</div>
	</div>
@endif

{{-- General Info Message --}}
@if (isset($sessionMessages['info']) && $sessionMessages['info'] != "")
	<div class="alert alert-info">
		<div>{{ $sessionMessages['info'] }}</div>
	</div>
@endif

@if (isset($messages['info']) && $messages['info'] != "")
	<div class="alert alert-info">
		<div>{{ $messages['info'] }}</div>
	</div>
@endif