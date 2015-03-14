@if (config('social.comments_enabled') && config('social.comments_type') == "Disqus" && !is_null(config('social.disqus_shortname')))

	<div id="disqus_thread"></div>

	<script type="text/javascript">
		var disqus_shortname  = "{{ config('social.disqus_shortname') }}";

		@if (Site::get('pageIdentifier', false))

			var disqus_identifier = "{{ Site::get('pageIdentifier') }}";

		@endif

		(function() {
		var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
		dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
		})();
	</script>

	<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>

@endif