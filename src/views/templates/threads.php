<script id="forum-threads-template" type="text/x-handlebars-template">
	{{#each threads}}

		<li class="full-link">
			<a href="<?=URL::to('forum')?>/thread/{{slug}}" class="full-link"></a>

			<ul class="info">
				<li>
					<label>Creator:</label>
					<span><a href="" class="profile-popup" rel="{{user_id}}">{{user}}</a></span>
				</li><li>
					<label>Replies:</label>
					<span>{{replies}}</span>
				</li><li>
					<label>Views:</label>
					<span>{{views}}</span>
				</li><li>
					<label>Latest Post:</label>
					<span>
						<a href="<?=URL::to('forum')?>/thread/{{slug}}#post{{latest_post_id}}">
							{{date_latest_post}}
						</a> by <a href="" class="profile-popup" rel="{{latest_post_user_id}}">{{latest_post_user}}</a>
					</span>
				</li>
			</ul>

			<h1>{{title}}</h1>

			<div>{{{content}}}</div>

	{{/each}}
</script>