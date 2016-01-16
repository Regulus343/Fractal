var sidebarToggleSpeed    = 300;
var sidebarCollapsedWidth = 8;

$(document).ready(function(){
	//set up open width for collapsed sidebar and set inline width to collapsed width
	if ($('#nav-side').hasClass('collapsed'))
	{
		$('#nav-side')
			.removeClass('collapsed')
			.attr('data-width', $('#nav-side').width())
			.addClass('collapsed')
			.css('width', sidebarCollapsedWidth);

		$('#container-content')
			.addClass('sidebar-offset')
			.attr('data-margin-left', $('#container-content').css('margin-left'))
			.removeClass('sidebar-offset');
	}

	//set up sidebar toggling
	$('#nav-side-toggle').on('click', function()
	{
		if ($('#nav-side').hasClass('collapsed'))
		{
			$('#nav-side').removeClass('collapsed').css('width', sidebarCollapsedWidth);

			$('#nav-side').animate({
				width: $('#nav-side').attr('data-width')
			}, {
				duration: sidebarToggleSpeed,
				queue:    false,
				complete: function(){
					$('#nav-side').css('width', '');
					$('#container-content').css('margin-left', '');

					Fractal.setUserState('sidebarOpen', true);
				}
			});

			$('#container-content').animate({
				marginLeft: $('#container-content').attr('data-margin-left')
			}, {
				duration: sidebarToggleSpeed,
				queue:    false
			});
		}
		else
		{
			$('#nav-side').attr('data-width', $('#nav-side').width());
			$('#container-content').attr('data-margin-left', $('#container-content').css('margin-left'));

			$('#nav-side').animate({
				width: sidebarCollapsedWidth
			}, {
				duration: sidebarToggleSpeed,
				queue:    false,
				complete: function(){
					$('#nav-side').addClass('collapsed');

					Fractal.setUserState('sidebarOpen', false);
				}
			});

			$('#container-content').animate({
				marginLeft: sidebarCollapsedWidth
			}, {
				duration: sidebarToggleSpeed,
				queue:    false
			});
		}
	});

	//set up sidebar accordion menu
	$('#nav-side ul li.active ul').css('display', 'block').addClass('open');
	$('#nav-side ul li a.dropdown-toggle').on('click', function(e)
	{
		e.preventDefault();

		if (!$(this).parents('li').hasClass('open'))
		{
			$(this).parents('li').find('ul').slideDown(250);
			$(this).parents('li').addClass('open');

			Fractal.setUserState('menuOpen[]', $(this).parents('li').attr('data-menu-item-id'));
		}
		else
		{
			$(this).parents('li').find('ul').css('display', 'block').slideUp(250);
			$(this).parents('li').removeClass('open');

			Fractal.removeUserState('menuOpen[]', $(this).parents('li').attr('data-menu-item-id'));
		}
	});
});