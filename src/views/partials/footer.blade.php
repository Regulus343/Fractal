	</div><!-- /#container -->

	<div id="footer">
		<div class="pad">
			<a href="#" class="return-to-top pull-left show-tooltip" title="{{ Lang::get('fractal::labels.returnToTop') }}">
				<span class="glyphicon glyphicon-chevron-up"></span>
			</a>

			<p>&copy;{{ date('Y') }} {{ Site::name() }}</p>

			@if (Config::get('fractal::displayVersion'))
				<p class="fractal">powered by Fractal v{{ Lang::get('fractal::labels.version') }}</p>
			@endif
		</div><!-- /.pad -->
	</div><!-- /#footer -->
</body>
</html>