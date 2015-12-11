			</div><!-- /.container-pad -->
		</div><!-- /#container-content -->
	</div><!-- /#container -->

	<div id="footer">
		<div class="pad">
			<div class="pull-right">
				<a href="" class="return-to-top pull-left show-tooltip" title="{{ Fractal::trans('labels.return_to_top') }}">
					<span class="glyphicon glyphicon-chevron-up"></span>
				</a>

				<div>&copy;{{ date('Y') }} {{ Site::name() }}</div>

				@if (config('cms.display_version'))

					<div class="fractal">
						powered by Fractal v{{ Fractal::trans('labels.version') }}

						@if (Site::developer())
							<span class="developer-mode">/ <a href="{{ Fractal::url('developer/off') }}">Disable Developer Mode</a></span>
						@endif
					</div>

				@endif
			</div>
		</div><!-- /.pad -->
	</div><!-- /#footer -->

</body>
</html>