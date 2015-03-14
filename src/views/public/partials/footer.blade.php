					</div><!-- /.col-md-(x) -->

					@yield('rightColumn')

				</div><!-- /.row -->

			</div><!-- /.container-pad -->
		</div><!-- /#container-content -->
	</div><!-- /#container -->

	<div id="footer">
		<div class="pad">
			<a href="" class="return-to-top pull-left show-tooltip" title="{{ Fractal::trans('labels.return_to_top') }}">
				<span class="glyphicon glyphicon-chevron-up"></span>
			</a>

			<p>&copy;{{ date('Y') }} {{ Site::name() }}</p>

			@if (config('cms.display_version'))

				<p class="fractal">
					powered by Fractal v{{ Fractal::trans('labels.version') }}

					@if (Site::developer())
						<span class="developer-mode">/ <a href="{{ Fractal::url('developer/off') }}">Disable Developer Mode</a></span>
					@endif
				</p>

			@endif

			<ul class="menu">
				{!! Fractal::getMenuMarkup('Footer', ['listItemsOnly' => true]) !!}
			</ul>
		</div><!-- /.pad -->
	</div><!-- /#footer -->

</body>
</html>