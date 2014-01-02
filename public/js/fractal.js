var contentID;

$(document).ready(function(){

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();

	$('a.show-tooltip[title]').tooltip();

	/* Setup Ajax Alert Messages */
	$('.alert-dismissable-hide .close').click(function(){
		$(this).parents('div.alert-dismissable-hide').addClass('hidden');
	});

	/* Setup File Fields */
	$('input[type="file"].file-upload-button').each(function(){
		$(this).addClass('hidden');

		var fileType   = $(this).attr('data-file-type') !== undefined ? $(this).attr('data-file-type') : "File";
		var buttonText = "Upload "+fileType;
		var button     = $('<button class="btn btn-default block">'+buttonText+'</button>').click(function(e){
			e.preventDefault();
			$(this).parents('div').children('input[type="file"]').click();
		});

		var dummyInput = $('<input type="text" name="'+$(this).attr('name')+'_dummy" id="'+$(this).attr('id')+'-dummy" class="form-control file-dummy" placeholder="'+fileType+'" readonly="readonly" />').click(function(){
			$(this).parents('div').children('input[type="file"]').click();
		});

		$(this)
			.after(button)
			.after(dummyInput)
			.after('<div class="clear"></div>');

	}).change(function(){
		var path     = $(this).val().split('\\');
		var filename = path[(path.length - 1)];
		$('#'+$(this).attr('id')+'-dummy').val(filename);
	});

	/* Setup Number Fields */
	$('input[type="number"], input.number').keyup(function(){
		console.log($(this).val());
		if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
	}).change(function(){
		if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
	});

});

var messageTimer;
function setMainMessage(message, type) {
	clearTimeout(messageTimer);

	$('#message-'+type+' div').html(message);
	$('#message-'+type).removeClass('hidden').show();
	messageTimer = setTimeout("$('.alert-dismissable-hide').slideUp();", 5000);
}

function modalConfirm(title, message, action) {
	$('#modal .modal-title').html(title);
	$('#modal .modal-body p').html(message);

	$('#modal').modal('show');

	$('#modal .btn-primary').off('click').on('click', action);
}

/* Setup Users */
function setupUsersTable() {
	/* Set "Ban User" Action */
	$('.ban-user').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.banUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmBanUser, actionBanUser);
	});

	/* Set "Unban User" Action */
	$('.unban-user').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.unbanUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmUnbanUser, actionUnbanUser);
	});

	/* Set "Delete User" Action */
	$('.delete-user').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.deleteUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.user), actionDeleteUser);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionBanUser = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseURL + '/users/ban/' + contentID,
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentID).addClass('danger');
				$('#user-'+contentID+' td.actions a.ban-user').addClass('hidden');
				$('#user-'+contentID+' td.actions a.unban-user').removeClass('hidden');

				$('#user-'+contentID+' td.banned').text('Yes');

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
		url: baseURL + '/users/unban/' + contentID,
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentID).removeClass('danger');
				$('#user-'+contentID+' td.actions a.unban-user').addClass('hidden');
				$('#user-'+contentID+' td.actions a.ban-user').removeClass('hidden');

				$('#user-'+contentID+' td.banned').text('No');

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
		url: baseURL + '/users/' + contentID,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentID).addClass('hidden');

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

/* Setup User Roles */
function setupUserRolesTable() {
	/* Set "Delete User Role" Action */
	$('.delete-user-role').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-role-id');
		modalConfirm(fractalLabels.deleteRole+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.role), actionDeleteUserRole);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeleteUserRole = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseURL + '/user-roles/' + contentID,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#role-'+contentID).addClass('hidden');

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

/* Setup Pages */
function setupPagesTable() {
	/* Set "Delete Page" Action */
	$('.delete-page').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-page-id');
		modalConfirm(fractalLabels.deletePage+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.page), actionDeletePage);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeletePage = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseURL + '/pages/' + contentID,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#page-'+contentID).addClass('hidden');

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

/* Setup Files */
function setupFilesTable() {
	/* Set "Delete Page" Action */
	$('.delete-file').click(function(e){
		e.preventDefault();

		contentID = $(this).attr('data-file-id');
		modalConfirm(fractalLabels.deleteFile+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.file), actionDeleteFile);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeleteFile = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseURL + '/files/' + contentID,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#file-'+contentID).addClass('hidden');

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

/* Formatting Functions */
function upperCaseWords(str) {
	// From: http://phpjs.org/functions
	// +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +   improved by: Waldo Malqui Silva
	// +   bugfixed by: Onno Marsman
	// +   improved by: Robin
	// +      input by: James (http://www.james-bell.co.uk/)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	return (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
		return $1.toUpperCase();
	});
}