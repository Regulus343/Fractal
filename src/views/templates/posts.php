<script id="forum-posts-template" type="text/x-handlebars-template">
	{{#each posts}}

		<li id="forum-post{{id}}" class="{{#if active_user_post}}active-user{{/if}}{{#if edit_time}} editable{{/if}}{{#if deleted}} deleted{{/if}}">

			<!-- Messages -->
			<div class="top-messages">
				<!-- Success Message -->
				<div class="message success hidden"></div>

				<!-- Error Message -->
				<div class="message error hidden"></div>

				<!-- General Info Message -->
				<div class="message info hidden"></div>
			</div>

			<div class="info">
				<h1><a href="" class="profile-popup" rel="{{user_id}}">{{user}}</a></h1>
				<ul class="info">
					<li>
						<label>Role:</label>
						<span>{{user_role}}</span>
					</li><li>
						<label>Member Since:</label>
						<span>{{user_since}}</span>
					</li><li>
						<label>Posts:</label>
						<span>{{user_posts}}</span>
					</li>
				</ul>

				<a href="" class="display-pic profile-popup" rel="{{user_id}}"><img src="{{user_image}}" alt="" /></a>

				<div class="clear"></div>
			</div>

			<div class="post">
				<!-- Post Content -->
				<div class="text">{{{content}}}</div>

				<!-- Date Posted -->
				<div class="date-posted">
					{{created_at}}

					{{#if updated}}
						last updated {{updated_at}}
					{{/if}}
				</div>
			</div>

			<!-- Actions -->
			{{#if logged_in}}

				{{#if edit_time}}
					<div class="edit-countdown">You may edit or delete your post for <span class="number">{{edit_time}}</span> more seconds</div>
				{{/if}}

				<ul class="actions">
					{{#if edit}}

						<li class="action-delete">
							<a href="" class="btn button button-delete button-delete-post" rel="{{id}}">
								<div class="icon icon-remove"></div><span><?=Lang::get('open-forum::labels.delete')?></span>
							</a>
						</li>

						<li class="action-edit">
							<a href="" class="btn button button-edit button-edit-post" rel="{{id}}">
								<div class="icon icon-edit"></div><span><?=Lang::get('open-forum::labels.edit')?></span>
							</a>
						</li>

					{{/if}}

					<li class="action-reply">
						<a href="" class="btn button button-reply button-reply-thread" rel="{{id}}">
							<div class="icon icon-share-alt"></div><span><?=Lang::get('open-forum::labels.reply')?></span>
						</a>
					</li>

				</ul>

			{{/if}}

			{{#if edit}}

				<div class="clear"></div>
				<div id="edit-forum-post{{id}}" class="add-post edit-post hidden" id="">

					<!-- Success Message -->
					<div class="message success hidden"></div>

					<!-- Error Message -->
					<div class="message error hidden"></div>

					<!-- General Info Message -->
					<div class="message info hidden"></div>

					<!-- Comment Form - Edit -->
					<?=Form::open('forum/post', 'post', array('class' => 'form-post'))?>

						<label for="post-edit{{id}}"><?=Lang::get('open-forum::labels.editPost')?>:</label>
						<textarea name="post" class="field-post wysiwyg" id="post-edit{{id}}">{{post}}</textarea>

						<input type="hidden" name="thread_id" class="thread-id" value="{{thread_id}}" />
						<input type="hidden" name="post_id" class="post-id" value="{{id}}" />

						<input type="submit" name="add_post" class="left" value="<?=Lang::get('open-forum::labels.editPost')?>" />

					<?=Form::close()?>

				</div><!-- /add-post -->

			{{/if}}

			<div class="clear"></div>
		</li>

	{{/each}}
</script>