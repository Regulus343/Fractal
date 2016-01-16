@if (config('social.share'))

	<div class="social-media-share">

		@foreach (config('social.share_items') as $shareItem)

			@if ($shareItem == "Twitter")

				{{-- Twitter --}}

				<div class="share-item share-twitter">
					<script type="text/javascript">
						window.twttr=(function(d,s,id){var t,js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)){return}js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);return window.twttr||(t={_e:[],ready:function(f){t._e.push(f)}})}(document,"script","twitter-wjs"));
					</script>

					<a class="twitter-share-button" href="{{ Fractal::getTwitterShareUrl() }}">Tweet</a>
				</div>

			@endif

			@if ($shareItem == "Facebook")

				{{-- Facebook --}}

				<div class="share-item share-facebook">
					<div id="fb-root"></div>

					<script type="text/javascript">
						(function(d, s, id) {
							var js, fjs = d.getElementsByTagName(s)[0];
							if (d.getElementById(id)) return;
							js = d.createElement(s); js.id = id;
							js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=185538998182646&version=v2.0";
							fjs.parentNode.insertBefore(js, fjs);
						}(document, 'script', 'facebook-jssdk'));
					</script>

					<div class="fb-share-button" data-href="{{ Site::get('contentUrl', Request::url()) }}" data-layout="button_count"></div>
				</div>

			@endif

			@if ($shareItem == "Google+")

				{{-- Google+ --}}

				<div class="share-item share-google-plus">
					<script src="https://apis.google.com/js/platform.js" async defer></script>

					<div class="g-plus" data-action="share" data-width="120"></div>
				</div>

			@endif

			@if ($shareItem == "Pinterest")

				{{-- Pinterest --}}

				<div class="share-item share-pinterest">
					<a href="//gb.pinterest.com/pin/create/button/?url={{ urlencode(Site::get('contentUrl', Request::url())) }}&amp;media={{ urlencode(Site::get('contentImage')) }}&amp;description={{ urlencode(Site::get('contentDescription')) }}" data-pin-do="buttonPin" data-pin-config="beside" data-pin-color="red"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" alt="Pinterest" /></a>

					<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
				</div>

			@endif

		@endforeach

		<div class="clear"></div>
	</div>

@endif