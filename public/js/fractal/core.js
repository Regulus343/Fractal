var contentId;
var messageShowTime = 5000;
var searching       = false;

var itemAction;
var itemActionType;
var itemActionMessage;
var itemActionUrl;
var itemActionFunction;

var activeActions = [];

$(document).ready(function(){

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();

	$('a.show-tooltip[title]').tooltip();

	/* Setup Ajax Alert Messages */
	$('.alert-dismissable-hide .close').click(function(){
		$(this).parents('div.alert-dismissable-hide').addClass('hidden');
	});

	/* Setup Auto-Hide Alert Messages */
	setTimeout(function(){
		$('.alert-auto-hide').slideUp('fast');
	}, messageShowTime);

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
		if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
	}).change(function(){
		if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
	});

	/* Setup Select Fields */
	$('select').select2();

	/* Setup Embedded Audio */
	audiojs.events.ready(function() {
		audiojs.createAll();
	});

	/* Setup Search, Pagination, and Table Sorting */
	$('#form-search').submit(function(e){
		e.preventDefault();
		$('#field-changing-page').val(0);
		searchContent();
	});

	$('#field-search').focus(function(){
		$(this).animate({
			width: '280px'
		}, 200);
	}).blur(function(){
		if ($(this).val().length < 16) {
			$(this).animate({
				width: '164px'
			}, 200);
		}
	});

	$('#form-search input, #form-search select').change(function(){
		$('#field-changing-page').val(0);
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

				$('#field-sort-field').val(sortField);

				if ($(this).hasClass('sort-asc')) {
					$(this)
						.addClass('sort-desc')
						.removeClass('sort-asc');

					$(this).children('span.sort-icon')
						.addClass('glyphicon-download')
						.removeClass('glyphicon-upload');

					$('#field-sort-order').val('desc');
				} else {
					$(this)
						.addClass('sort-asc')
						.removeClass('sort-desc');

					$(this).children('span.sort-icon')
						.addClass('glyphicon-upload')
						.removeClass('glyphicon-download');

					$('#field-sort-order').val('asc');
				}

				searchContent();
			});
		}
	});

	/* Allow Tab Characters For Certain Textarea Boxes */
	$('textarea.tab').keydown(function(e){
		if (e.keyCode == 9) {
			var myValue = "\t";
			var startPos = this.selectionStart;
			var endPos = this.selectionEnd;
			var scrollTop = this.scrollTop;
			this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos,this.value.length);
			this.focus();
			this.selectionStart = startPos + myValue.length;
			this.selectionEnd = startPos + myValue.length;
			this.scrollTop = scrollTop;

			e.preventDefault();
		}
	});

	/* Set Up Date Time Pickers */
	$('.date-time-picker').datetimepicker({
		language:         'en',
		pick12HourFormat: true,
	});

	$('.date-picker').datetimepicker({
		language: 'en',
		pickTime: false,
	});

	/* Set Up Checkbox Show / Hide Actions */
	$('input[type="checkbox"][data-checked-show]').click(function(){
		var selector = $(this).attr('data-checked-show');
		var type     = $(this).attr('data-show-hide-type') == "visibility" ? $(this).attr('data-show-hide-type') : "display";
		var callback = $(this).attr('data-callback-function');
		var checked  = $(this).prop('checked');

		if (checked) {
			if (type == "visibility")
				$(selector).css('opacity', 0).removeClass('invisible').animate({'opacity': 1});
			else
				$(selector).hide().removeClass('hidden').fadeIn();
		} else {
			if (type == "visibility")
				$(selector).animate({'opacity': 0});
			else
				$(selector).fadeOut();
		}

		if (callback !== null)
			window[callback](checked);
	});

	/* Set Up Tooltips */
	$('[data-toggle="tooltip"]').tooltip({html: true});

	/* Set Up Ajax-Based Modal Windows */
	setupModalTriggers();

	/* Set Up Return To Top */
	$('a.return-to-top').click(function(e){
		e.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 500);
	});

	//set up Markdown content field action preview window
	setupMarkdownFields();

});

var messageTimer;
function setMainMessage(message, type) {
	clearTimeout(messageTimer);

	$('#message-'+type+' div').html(message);
	$('#message-'+type).hide().removeClass('hidden').css('z-index', 10000).slideDown('medium');

	messageTimer = setTimeout(function(){
		$('.alert-dismissable-hide').slideUp('fast').css('z-index', 1000);
	}, messageShowTime);
}

function modalConfirm(title, message, action, modalId) {
	if (modalId === undefined)
		modalId = "modal";

	$('#'+modalId+' .modal-title').html(title);
	$('#'+modalId+' .modal-body').html('<p>' + message + '</p>');
	$('#'+modalId+' .modal-footer').show();

	$('#'+modalId).modal('show');

	if (action !== undefined && action !== null)
		$('#'+modalId+' .btn-primary').off('click').on('click', action);
}

function modalAjax(url, type, data, callbackFunction, modalId) {
	if (modalId === undefined)
		modalId = "modal";

	if (data === undefined)
		data = [];

	$.ajax({
		type:     type,
		url:      url,
		data:     data,
		dataType: 'json',
		success: function(result) {
			$('#'+modalId+' .modal-title').html(result.title);
			$('#'+modalId+' .modal-body').html(result.content);

			if (result.buttons)
				$('#'+modalId+' .modal-footer').show();
			else
				$('#'+modalId+' .modal-footer').hide();

			if (callbackFunction !== undefined && callbackFunction !== null)
				window[callbackFunction]();

			$('#'+modalId).modal('show');
		}
	});
}

function capitalizeFirstLetter(string)
{
	return string.charAt(0).toUpperCase() + string.slice(1);
}

function setUserState(name, state) {
	$.ajax({
		url: baseUrl+'/api/set-user-state',
		type: 'post',
		data: {name: name, state: state},
		success: function(result){
			console.log('User State Saved: '+name+' = '+state);
		},
		error: function(){
			console.log('User State Change Failed: '+name+' = '+state);
		}
	});
}

function removeUserState(name, state) {
	$.ajax({
		url: baseUrl+'/api/remove-user-state',
		type: 'post',
		data: {name: name, state: state},
		success: function(result){
			console.log('User State Removed: '+name+' = '+state);
		},
		error: function(){
			console.log('User State Removal Failed: '+name+' = '+state);
		}
	});
}

/* Setup Search and Pagination Functions */
function searchContent() {
	if (!searching) {
		searching = true;

		var postData = $('#form-search').serialize();

		$('.alert-dismissable').addClass('hidden');

		$.ajax({
			url: currentUrl+'/search',
			type: 'post',
			data: postData,
			dataType: 'json',
			success: function(result){
				if (result.message !== undefined) {
					if (result.resultType == "Success")
						setMainMessage(result.message, 'success');
					else
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

	if (lastPage > 1)
	{
		var pagination = '<li'+(page == 1 ? ' class="disabled"' : '')+'><a href="" data-page="1">&laquo;</a></li>' + "\n";
		for (p = (page - 3); p <= (page + 3); p++) {
			if (p > 0 && p <= lastPage)
				pagination += '<li'+(page == p ? ' class="active"' : '')+'><a href="" data-page="'+p+'">'+p+'</a></li>' + "\n";
		}
		pagination += '<li'+(page == lastPage ? ' class="disabled"' : '')+'><a href="" data-page="'+lastPage+'">&raquo;</a></li>' + "\n";

		$('.pagination').html(pagination);

		setupPagination();

		$('.pagination').fadeIn('fast');
	} else {
		$('.pagination').fadeOut('fast');
	}

	previousLastPage = lastPage;
}

function setupPagination() {
	$('.pagination li a[href=""]').off('click').on('click', function(e){
		e.preventDefault();

		if (!$(this).hasClass('disabled') && !$(this).hasClass('active'))
		{
			page = parseInt($(this).attr('data-page'));

			$('#field-page').val(page);
			$('#field-changing-page').val(1);

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

function formErrorCallback(fieldContainer) {
	fieldContainer.find('[data-toggle="tooltip"]').tooltip({html: true});
}

/* Setup Content */
function setupContentTable() {
	/* Setup Actions */
	$('.action-item').click(function(e){
		e.preventDefault();

		contentId = $(this).attr('data-item-id');

		var itemType = fractalLabels[contentType.replace(/\-/g, '_').camelize(true)].toLowerCase();
		var itemName = $(this).attr('data-item-name');

		itemAction         = $(this).attr('data-action');
		itemActionType     = $(this).attr('data-action-type') !== undefined ? $(this).attr('data-action-type') : 'post';
		itemActionMessage  = $(this).attr('data-action-message');
		itemActionUrl      = $(this).attr('data-action-url');
		itemActionFunction = $(this).attr('data-action-function');

		if (itemName !== undefined && itemName != "" && fractalMessages[itemActionMessage+'WithName'] !== undefined)
			itemActionMessage += 'WithName';

		var confirmTitle   = fractalLabels[itemAction+capitalizeFirstLetter(contentType)];
		var confirmMessage = fractalMessages[itemActionMessage].replace(':item', itemType);

		if (itemName !== undefined)
			confirmMessage = confirmMessage.replace(':name', itemName);

		if (itemActionFunction !== undefined)
			modalConfirm(confirmTitle, confirmMessage, window[itemActionFunction]);
		else
			modalConfirm(confirmTitle, confirmMessage, actionItem);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionItem = function(){
	$('#modal').modal('hide');

	var url = baseUrl + '/'+contentType.pluralize()+'/' + contentId;

	if (itemActionType != "delete")
		url += '/' + itemAction;

	if (itemActionUrl !== undefined && itemActionUrl != "")
		url = itemActionUrl;

	$.ajax({
		url:      url,
		type:     itemActionType,
		dataType: 'json',
		success:  function(result){
			if (result.resultType == "Success") {
				$('#'+contentType+'-'+contentId).addClass('hidden');

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

		contentId = $(this).attr('data-page-id');
		modalConfirm(fractalLabels.deletePage+': <strong>'+$(this).parents('tr').children('td.title').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.page), actionDeletePage);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeletePage = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseUrl + '/pages/' + contentId,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#page-'+contentId).addClass('hidden');

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

		contentId = $(this).attr('data-file-id');
		modalConfirm(fractalLabels.deleteFile+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.file), actionDeleteFile);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeleteFile = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseUrl + '/files/' + contentId,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#file-'+contentId).addClass('hidden');

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

		contentId = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.banUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmBanUser, actionBanUser);
	});

	/* Set "Unban User" Action */
	$('.unban-user').click(function(e){
		e.preventDefault();

		contentId = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.unbanUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmUnbanUser, actionUnbanUser);
	});

	/* Set "Delete User" Action */
	$('.delete-user').click(function(e){
		e.preventDefault();

		contentId = $(this).attr('data-user-id');
		modalConfirm(fractalLabels.deleteUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.user), actionDeleteUser);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionBanUser = function(){
	$('#modal').modal('hide');

	$.ajax({
		url:      baseUrl + '/users/' + contentId + '/ban',
		dataType: 'json',
		success:  function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentId).addClass('danger');
				$('#user-'+contentId+' td.actions a.ban-user').addClass('hidden');
				$('#user-'+contentId+' td.actions a.unban-user').removeClass('hidden');

				$('#user-'+contentId+' td.banned').html('<span class="boolean-true">Yes</span>');

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
		url:      baseUrl + '/users/' + contentId + '/unban',
		dataType: 'json',
		success:  function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentId).removeClass('danger');
				$('#user-'+contentId+' td.actions a.unban-user').addClass('hidden');
				$('#user-'+contentId+' td.actions a.ban-user').removeClass('hidden');

				$('#user-'+contentId+' td.banned').html('<span class="boolean-false">No</span>');

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
		url: baseUrl + '/users/' + contentId,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#user-'+contentId).addClass('hidden');

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

		contentId = $(this).attr('data-role-id');
		modalConfirm(fractalLabels.deleteRole+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.role), actionDeleteUserRole);
	});

	/* Setup Tooltips */
	$('table td.actions a[title]').tooltip();
}

var actionDeleteUserRole = function(){
	$('#modal').modal('hide');
	$.ajax({
		url: baseUrl + '/user-roles/' + contentId,
		type: 'delete',
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				$('#role-'+contentId).addClass('hidden');

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

function setupModalTriggers() {
	$('.trigger-modal[data-modal-ajax-uri]').off('click').on('click', function(e){
		e.preventDefault();

		var url              = baseUrl + '/' + $(this).attr('data-modal-ajax-uri');
		var type             = $(this).attr('data-modal-ajax-action') == "get" ? "get" : "post";
		var dataVars         = $(this).attr('data-modal-ajax-data-variables') !== undefined ? $(this).attr('data-modal-ajax-data-variables').split(',') : [];
		var callbackFunction = $(this).attr('data-modal-callback-function');

		var data = {};
		for (v in dataVars) {
			dataVar = window[dataVars[v]];

			if (dataVar === undefined)
				dataVar = "";

			data[dataVars[v]] = dataVar;
		}

		modalAjax(url, type, data, callbackFunction);
	});
}

/* Markdown Functions */
var converter = Markdown.getSanitizingConverter();

var markdownContentField;
var markdownContentUpdateTimer;

function setupMarkdownField(field) {
	if (field) {
		field.off('focus').on('focus', function()
		{
			renderMarkdownPreview($(this));
			$('#markdown-preview').fadeIn();

		}).off('keydown').on('keydown', function(e)
		{
			if (e.keyCode == 9)
			{
				var myValue   = "\t";
				var startPos  = this.selectionStart;
				var endPos    = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value    = this.value.substring(0, startPos) + myValue + this.value.substring(endPos,this.value.length);
				this.focus();

				this.selectionStart = startPos + myValue.length;
				this.selectionEnd   = startPos + myValue.length;
				this.scrollTop      = scrollTop;

				e.preventDefault();
			}
		}).off('keyup').on('keyup', function()
		{
			renderMarkdownPreview($(this));
			checkForSelectFileMediaItem($(this));

		}).off('blur').on('blur', function(){
			$('#markdown-preview').fadeOut();
		});

		renderMarkdownPreview(field);
	}
}

function setupMarkdownFields() {
	$('textarea.markdown').each(function(){
		setupMarkdownField($(this));
	});
}

function renderMarkdownPreview(field) {
	var markdownContent = converter.makeHtml(field.val());

	$('#markdown-preview-content').html(markdownContent);
	$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);

	field.parents('.row').find('.markdown-preview-content').html(markdownContent);

	markdownContentField       = field;
	markdownContentUpdateTimer = setTimeout(incrementMarkdownContentUpdateTimer, 3000);
}

function incrementMarkdownContentUpdateTimer() {
	clearTimeout(markdownContentUpdateTimer);

	$.ajax({
		type:    'post',
		url:     baseUrl + '/pages/render-markdown-content',
		data:    {content: markdownContentField.val()},
		success: function(content) {
			$('#markdown-preview-content').html(content);
			$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);

			markdownContentField.parents('.row').find('.markdown-preview-content').html(content);
		}
	});
}

function checkForSelectFileMediaItem(field) {
	var wysiwyg = field.hasClass('field-content-html');

	if (wysiwyg)
		var text = CKEDITOR.instances[field.attr('id')].getData();
	else
		var text = field.val();

	if (text.substr(-5) == "</p>\n")
	{
		text = text.substr(0, (text.length - 5));
	}

	if (text.substr(-5) == "file:")
	{
		var type = "File";
		modalAjax(baseUrl + '/api/select-file-media-item', 'post', {type: type}, selectFileMediaItem(field, type));
	} else {
		if (activeActions['selectingFile'] === true)
			$('#modal').modal('hide');
	}

	if (text.substr(-7) == "[image:")
	{
		var type = "File";
		modalAjax(baseUrl + '/api/select-file-media-item', 'post', {type: type}, selectFileMediaItem(field, type, true));
	} else {
		if (activeActions['selectingFile'] === true)
			$('#modal').modal('hide');
	}

	if (text.substr(-7) == "[media:")
	{
		var type = "Media Item";
		modalAjax(baseUrl + '/api/select-file-media-item', 'post', {type: type}, selectFileMediaItem(field, type, true));
	} else {
		if (activeActions['selectingMediaItem'] === true)
			$('#modal').modal('hide');
	}
}

function selectFileMediaItem(field, type, addClosingBracket) {
	var wysiwyg = field.hasClass('field-content-html');
	var text    = wysiwyg ? CKEDITOR.instances[field.attr('id')].getData() : field.val();

	if (wysiwyg && text.substr(-5) == "</p>\n")
		text= text.substr(0, (text.length - 5));

	if (type == "File") {
		activeActions['selectingFile'] = true;

		setTimeout(function(){
			$('#select-file li').off('click').on('click', function()
			{
				text += $(this).attr('data-file-id') + (addClosingBracket === true ? ']' : '');

				if (wysiwyg)
					CKEDITOR.instances[field.attr('id')].setData(text);
				else
					field.val(text);

				activeActions['selectingFile'] = false;
				$('#modal').modal('hide');

				if (wysiwyg)
					setTimeout(function(){ focusWysiwyg(field); }, 100);
				else
					focusField(field);
			});
		}, 500);
	} else {
		activeActions['selectingMediaItem'] = true;

		setTimeout(function(){
			$('#select-media-item li').off('click').on('click', function()
			{
				text += $(this).attr('data-media-item-id') + (addClosingBracket === true ? ']' : '');

				if (wysiwyg)
					CKEDITOR.instances[field.attr('id')].setData(text);
				else
					field.val(text);

				activeActions['selectingMediaItem'] = false;
				$('#modal').modal('hide');

				if (wysiwyg)
					setTimeout(function(){ focusWysiwyg(field); }, 100);
				else
					focusField(field);
			});
		}, 500);
	}
}

function focusField(field) {
	field.focus();

	var value = field.val();
	field.val('');
	field.val(value);
}

function focusWysiwyg(field) {
	var editor = CKEDITOR.instances[field.attr('id')];
	editor.focus();

	var range = editor.createRange();
	range.moveToElementEditEnd( range.root );
	editor.getSelection().selectRanges([range]);
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