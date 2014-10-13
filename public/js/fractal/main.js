$(document).ready(function(){
	$('#nav-side ul li a.dropdown-toggle').on('click', function(e){
		e.preventDefault();

		if (!$(this).hasClass('open')) {
			$(this).addClass('open');
			$(this).parents('li').find('ul').slideDown(250);
		} else {
			$(this).removeClass('open');
			$(this).parents('li').find('ul').slideUp(250);
		}
	});
});