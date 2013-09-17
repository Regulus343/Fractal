@extends(Config::get('open-forum::layout'))

@section(Config::get('open-forum::section'))

	@include('open-forum::partials.included_files')

	@include('open-forum::partials.nav')

	@include('open-forum::partials.reply')

	@include('open-forum::partials.messages')

	{{-- Ajax Loading Image --}}
	<div class="loading" id="loading-forum-posts"></div>

	{{-- Threads List --}}
	<ul id="forum-posts" class="content"></ul>

	{{-- JS Template for Threads --}}
	@include(Config::get('open-forum::viewsLocation').'templates.posts')

	{{-- Bottom Pagination --}}
	<ul class="forum-pagination hidden"></ul>

@stop