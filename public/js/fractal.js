var userID;

$(document).ready(function(){

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();

	$('a.show-tooltip[title]').tooltip();

});

var messageTimer;
function setMainMessage(message, type) {
	clearTimeout(messageTimer);

	$('#message-'+type).html(message);
	$('#message-'+type).removeClass('hidden').show();
	messageTimer = setTimeout("$('#message-"+type+"').slideUp();", 4000);
}

function modalConfirm(title, message, action) {
	$('#modal .modal-title').html(title);
	$('#modal .modal-body p').html(message);

	$('#modal').modal('show');

	$('#modal .btn-primary').off('click').on('click', action);
}

function setupUsersTable() {
	/* Set "Ban User" Action */
	$('.ban-user').click(function(e){
		e.preventDefault();

		userID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.banUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmBanUser, actionBanUser);
	});

	/* Set "Unban User" Action */
	$('.unban-user').click(function(e){
		e.preventDefault();

		userID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.unbanUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmUnbanUser, actionUnbanUser);
	});

	/* Set "Delete User" Action */
	$('.delete-user').click(function(e){
		e.preventDefault();

		userID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.deleteUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', 'user'), actionDeleteUser);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionBanUser = function(){
	$('#modal').modal('hide');
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
}

var actionUnbanUser = function(){
	$('#modal').modal('hide');
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
}

var actionDeleteUser = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseURL + '/users/delete/' + userID,
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#user-'+userID).addClass('hidden');

				setMainMessage(result.message, 'success');
			} else {
				setMainMessage(result.message, 'error');
			}
		},
		error: function(){
			setMainMessage(fractalMessages.errorGeneral, 'error');
		}
	});
}