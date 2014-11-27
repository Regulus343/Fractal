					</div><!-- /.col-md-(x) -->

					@yield('rightColumn')

				</div><!-- /.row -->

			</div><!-- /.container-pad -->
		</div><!-- /#container-content -->
	</div><!-- /#container -->

	<div id="footer">
		<div class="pad">
			<a href="" class="return-to-top pull-left show-tooltip" title="{{ Fractal::lang('labels.returnToTop') }}">
				<span class="glyphicon glyphicon-chevron-up"></span>
			</a>

			<p>&copy;{{ date('Y') }} {{ Site::name() }}</p>

			@if (Config::get('fractal::displayVersion'))
				<p class="fractal">
					powered by Fractal v{{ Fractal::lang('labels.version') }}

					@if (Site::developer())
						<span class="developer-mode">/ <a href="{{ Fractal::url('developer/off') }}">Disable Developer Mode</a></span>
					@endif
				</p>
			@endif

			<ul class="menu">
				{{ Fractal::getMenuMarkup('Footer', ['listItemsOnly' => true]) }}
			</ul>
		</div><!-- /.pad -->
	</div><!-- /#footer -->

	@if (Config::get('fractal::exterminator'))
		{{ Dbg::display() }}
	@endif
</body>
</html>