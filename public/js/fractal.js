var contentID;
var messageShowTime = 5000;
var searching       = false;

$(document).ready(function(){

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();

	$('a.show-tooltip[title]').tooltip();

	/* Setup Ajax Alert Messages */
	$('.alert-dismissable-hide .close').click(function(){
		$(this).parents('div.alert-dismissable-hide').addClass('hidden');
	});

	/* Setup Auto-Hide Alert Messages */
	setTimeout("$('.alert-auto-hide').fadeOut();", messageShowTime);

	/* Setup File Fields */
	$('input[type="file"].file-upload-button').each(function(){
		$(this).addClass('hidden');

		var fileType   = $(this).attr('data-file-type') !== undefined ? $(this).attr('data-file-type') : "File";
		var buttonText = "Select "+fileType;
		var button     = $('<button class="btn btn-default block"><span class="glyphicon glyphicon-file"></span>&nbsp; '+buttonText+'</button>').click(function(e){
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

	/* Setup Search, Pagination, and Table Sorting */
	$('#form-search').submit(function(e){
		e.preventDefault();
		$('#changing-page').val(0);
		searchContent();
	});

	$('#search').focus(function(){
		$(this).animate({
			width: '360px'
		});
	}).blur(function(){
		if ($(this).val().length < 16) {
			$(this).animate({
				width: '200px'
			});
		}
	}).change(function(){
		$('#changing-page').val(0);
		searchContent();
	});

	setupPagination();

	$('table.table-sortable thead tr th').each(function(){
		if ($(this).attr('data-sort-field') !== undefined) {
			$(this).addClass('sortable');

			var icon = "record";
			if ($(this).attr('data-sort-field') == sortField) {
				if (sortOrder == "desc") {
					$(this).addClass('sort-desc');
					icon = "upload";
				} else {
					$(this).addClass('sort-asc');
					icon = "download";
				}
			}

			$(this).html($(this).html()+' <span class="sort-icon glyphicon glyphicon-'+icon+'"></span>');

			$(this).mouseenter(function(){
				if (!$(this).hasClass('sort-changed')) {
					if ($(this).hasClass('sort-asc')) {
						$(this).children('span.sort-icon')
							.addClass('glyphicon-upload')
							.removeClass('glyphicon-download');
					} else {
						$(this).children('span.sort-icon')
							.addClass('glyphicon-download')
							.removeClass('glyphicon-upload')
							.removeClass('glyphicon-record');
					}
				}
			}).mouseleave(function(){
				$(this).removeClass('sort-changed');

				if ($(this).hasClass('sort-asc')) {
					$(this).children('span.sort-icon')
						.addClass('glyphicon-download')
						.removeClass('glyphicon-upload');
				} else if ($(this).hasClass('sort-desc')) {
					$(this).children('span.sort-icon')
						.addClass('glyphicon-upload')
						.removeClass('glyphicon-download');
				} else {
					$(this).children('span.sort-icon')
						.addClass('glyphicon-record')
						.removeClass('glyphicon-download')
						.removeClass('glyphicon-upload');
				}
			}).click(function(){
				sortField = $(this).attr('data-sort-field');
				$('table.table-sortable thead tr th.sortable').each(function(){
					if ($(this).attr('data-sort-field') != sortField)
						$(this)
							.removeClass('sort-asc')
							.removeClass('sort-desc')
							.removeClass('sort-changed');
				});

				$(this).addClass('sort-changed');

				$('#sort-field').val(sortField);

				if ($(this).hasClass('sort-asc')) {
					$(this)
						.addClass('sort-desc')
						.removeClass('sort-asc');

					$(this).children('span.sort-icon')
						.addClass('glyphicon-download')
						.removeClass('glyphicon-upload');

					$('#sort-order').val('desc');
				} else {
					$(this)
						.addClass('sort-asc')
						.removeClass('sort-desc');

					$(this).children('span.sort-icon')
						.addClass('glyphicon-upload')
						.removeClass('glyphicon-download');

					$('#sort-order').val('asc');
				}

				searchContent();
			});
		}
	});

});

var messageTimer;
function setMainMessage(message, type) {
	clearTimeout(messageTimer);

	$('#message-'+type+' div').html(message);
	$('#message-'+type).hide().removeClass('hidden').fadeIn('fast');
	messageTimer = setTimeout("$('.alert-dismissable-hide').fadeOut();", messageShowTime);
}

function modalConfirm(title, message, action) {
	$('#modal .modal-title').html(title);
	$('#modal .modal-body p').html(message);

	$('#modal').modal('show');

	$('#modal .btn-primary').off('click').on('click', action);
}

/* Setup Search and Pagination Functions */
function searchContent() {
	if (!searching) {
		searching = true;

		var postData = $('#form-search').serialize();

		$('.alert-dismissable').addClass('hidden');

		$.ajax({
			url: baseURL + '/'+contentType+'/search',
			type: 'post',
			data: postData,
			dataType: 'json',
			success: function(result){
				if (result.resultType == "Success") {
					setMainMessage(result.message, 'success');
				} else {
					setMainMessage(result.message, 'error');
				}

				createPaginationMenu(result.pages);

				$('table.table tbody').html(result.tableBody);
				setupContentTable();
				searching = false;
			},
			error: function(){
				setMainMessage(fractalMessages.errorGeneral, 'error');
				searching = false;
			}
		});
	}
}

function createPaginationMenu(pages) {
	lastPage = pages;
	if (lastPage > 1) {
		if (lastPage != previousLastPage) {
			$('.pagination').fadeOut('fast');

			var pagination = '<li'+(page == 1 ? ' class="disabled"' : '')+'><a href="" data-page="1">&laquo;</a></li>' + "\n";
			for (p = page- 2; p <= page + 3; p++) {
				if (p > 0 && p <= lastPage)
					pagination += '<li'+(page == p ? ' class="active"' : '')+'><a href="" data-page="'+p+'">'+p+'</a></li>' + "\n";
			}
			pagination += '<li'+(page == lastPage ? ' class="disabled"' : '')+'><a href="" data-page="'+lastPage+'">&raquo;</a></li>' + "\n";

			$('.pagination').html(pagination);

			setupPagination();
		}

		$('.pagination').fadeIn('fast');
	} else {
		$('.pagination').fadeOut('fast');
	}

	previousLastPage = lastPage;
}

function setupPagination() {
	$('.pagination li a').off('click').on('click', function(e){
		e.preventDefault();
		if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
			page = $(this).attr('data-page');

			$('#page').val(page);
			$('#changing-page').val(1);

			$('.pagination li').removeClass('active');
			$('.pagination li a').each(function(){
				if ($(this).text() == page) $(this).parents('li').addClass('active');
			});

			if (page == 1) {
				$('.pagination li:first-child').addClass('disabled');
			} else {
				$('.pagination li:first-child').removeClass('disabled');
			}

			if (page == lastPage) {
				$('.pagination li:last-child').addClass('disabled');
			} else {
				$('.pagination li:last-child').removeClass('disabled');
			}

			searchContent();
		}
	});
}

function setupContentTable() {
	switch (contentType) {
		case "pages":      setupPagesTable();     break;
		case "files":      setupFilesTable();     break;
		case "users":      setupUsersTable();     break;
		case "user-roles": setupUserRolesTable(); break;
	}
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

				$('#user-'+contentID+' td.banned').html('<span class="boolean-true">Yes</span>');

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

				$('#user-'+contentID+' td.banned').html('<span class="boolean-false">No</span>');

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