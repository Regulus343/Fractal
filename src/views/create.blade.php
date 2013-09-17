@extends(Config::get('open-forum::layout'))

@section(Config::get('open-forum::section'))

	@include('open-forum::partials.included_files')

	@include('open-forum::partials.nav')

	@include('open-forum::partials.messages')

	<div class="create-post" id="create-forum-thread">
		{{ Form::open() }}

			<fieldset>
				<legend>{{ Lang::get('open-forum::labels.createThread') }}</legend>

				{{-- Title --}}
				<div class="field-row">
					{{ Form::field('title', null, array('placeholder' => Lang::get('open-forum::labels.addThreadTitlePlaceholder'))) }}
					<div class="clear"></div>
				</div>

				{{-- Content --}}
				<div class="field-row">
					{{ Form::field('content', 'textarea', array('id-field' => 'new-thread-post-content', 'class-field' => 'wysiwyg', 'placeholder' => Lang::get('open-forum::labels.addPostContentPlaceholder'), 'value' => '')) }}
					<div class="clear"></div>
				</div>

				{{-- Section ID --}}
				{{ Form::hidden('section_id') }}

				{{-- Preview --}}
				{{ Form::hidden('preview', null, array('id-field' => 'preview-thread')) }}

				{{-- Preview & Create Thread --}}
				<div class="field-row">
					{{ Form::button(Lang::get('open-forum::labels.previewThread'), array('id' => 'btn-preview-thread')) }}
					{{ Form::button(Lang::get('open-forum::labels.createThread'), array('id' => 'btn-create-thread')) }}
				</div>
			</fieldset>

		{{ Form::close() }}
	</div><!-- /#create-thread -->

@stop