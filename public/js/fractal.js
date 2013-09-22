$(document).ready(function(){

	/* Set Tooltip for Table Actions */
	$('table td.actions a[title]').tooltip();

	/* Set "Ban User" Action */
	$('.ban-user').click(function(e){
		e.preventDefault();

		var userID = $(this).attr('data-user-id');
		$.ajax({
			url: baseURL + '/users/ban/' + userID,
			dataType: 'json',
			success: function(){
				console.log('Test...');
			}
		});
	});

});