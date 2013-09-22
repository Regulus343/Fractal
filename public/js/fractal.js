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
			success: function(result){
				if (result.resultType == "Success") {
					$('#user-'+userID).addClass('danger');
					$('#user-'+userID+' td.actions a.ban-user').addClass('hidden');
					$('#user-'+userID+' td.actions a.unban-user').removeClass('hidden');

					$('#user-'+userID+' td.banned').text('Yes');

					setMainMessage(result.message, 'success');
				} else {
					setMainMessage(result.message, 'error');
				}
			},
			error: function(){
				setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	});

	/* Set "Unban User" Action */
	$('.unban-user').click(function(e){
		e.preventDefault();

		var userID = $(this).attr('data-user-id');
		$.ajax({
			url: baseURL + '/users/unban/' + userID,
			dataType: 'json',
			success: function(result){
				if (result.resultType == "Success") {
					$('#user-'+userID).removeClass('danger');
					$('#user-'+userID+' td.actions a.unban-user').addClass('hidden');
					$('#user-'+userID+' td.actions a.ban-user').removeClass('hidden');

					$('#user-'+userID+' td.banned').text('No');

					setMainMessage(result.message, 'success');
				} else {
					setMainMessage(result.message, 'error');
				}
			},
			error: function(){
				setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	});

});

var messageTimer;
function setMainMessage(message, type) {
	clearTimeout(messageTimer);

	$('#message-'+type).html(message);
	$('#message-'+type).removeClass('hidden');
	messageTimer = setTimeout("$('#message-"+type+"').slideUp();", 4000);
}