/*
|------------------------------------------------------------------------------
| Fractal JS
|------------------------------------------------------------------------------
|
| Last Updated: March 14, 2015
|
*/

var Fractal = {

	baseUrl:                    null,
	mediaUrl:                   null,
	blogUrl:                    null,
	currentUrl:                 null,

	messageShowTime:            5000,
	searching:                  false,
	messageTimer:               null,

	labels:                     {},
	messages:                   {},

	contentType:                null,
	contentId:                  null,

	page:                       1,
	lastPage:                   1,
	previousLastPage:           null,

	sortField:                  'id',
	sortOrder:                  'asc',

	itemAction:                 null,
	itemActionType:             null,
	itemActionMessage:          null,
	itemActionUrl:              null,
	itemActionFunction:         null,

	activeActions:              [],

	converter:                  Markdown.getSanitizingConverter(),
	markdownContentField:       null,
	markdownPreviewField:       null,
	markdownContentUpdateTimer: null,

	autoSaveRate:               (1000 * 60), // auto-save every every minute

	init: function()
	{
		$('a.show-tooltip[title]').tooltip();

		// initialize AJAX alert messages
		$('.alert-dismissable-hide .close').click(function(){
			$(this).parents('div.alert-dismissable-hide').addClass('hidden');
		});

		// initialize auto-hide alert messages
		setTimeout(function(){
			$('.alert-auto-hide').slideUp('fast');
		}, this.messageShowTime);

		// initialize file fields
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

		// initialize number fields
		$('input[type="number"], input.number').keyup(function(){
			if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
		}).change(function(){
			if (isNaN($(this).val()) || $(this).val() == "") $(this).val('');
		});

		// initialize select fields
		$('select').select2();

		// initialize embedded audio
		audiojs.events.ready(function() {
			audiojs.createAll();
		});

		// initialize search, content, and table sorting
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

		$('#form-search input, #form-search select, input.search-filter, select.search-filter').change(function(){
			$('#field-changing-page').val(0);
			searchContent();
		});

		this.initContentTable();

		this.initPagination();

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

		// allow tab characters for some textareas
		$('textarea.tab').keydown(function(e){
			if (e.keyCode == 9) {
				var value     = "\t";
				var startPos  = this.selectionStart;
				var endPos    = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value    = this.value.substring(0, startPos) + value + this.value.substring(endPos,this.value.length);
				this.focus();

				this.selectionStart = startPos + value.length;
				this.selectionEnd   = startPos + value.length;
				this.scrollTop      = scrollTop;

				e.preventDefault();
			}
		});

		// initialize date / datetime pickers
		$('.date-time-picker').datetimepicker({
			language:         'en',
			pick12HourFormat: true,
		});

		$('.date-picker').datetimepicker({
			language: 'en',
			pickTime: false,
		});

		// initialize checkbox show / hide actions
		$('input[type="checkbox"][data-checked-show]').click(function()
		{
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

		// initialize tooltips
		$('[data-toggle="tooltip"]').tooltip({html: true});

		// initialize AJAX-based modal windows
		this.initModalTriggers();

		// initialize "Return to Top" buttons
		$('a.return-to-top').click(function(e)
		{
			e.preventDefault();

			$('html, body').animate({
				scrollTop: '0px'
			}, 500);
		});

		// initialize markdown fields
		this.initMarkdownFields();

		(function ($, undefined) {
			$.fn.getCursorPosition = function() {
				var el = $(this).get(0);
				var pos = 0;
				if('selectionStart' in el) {
					pos = el.selectionStart;
				} else if('selection' in document) {
					el.focus();
					var Sel = document.selection.createRange();
					var SelLength = document.selection.createRange().text.length;
					Sel.moveStart('character', -el.value.length);
					pos = Sel.text.length - SelLength;
				}
				return pos;
			}
		})(jQuery);
	},

	createUrl: function(uri, type)
	{
		if (type === undefined)
			type = "base";

		return this[type+'Url'] + '/' + uri;
	},

	setMainMessage: function(message, type)
	{
		clearTimeout(this.messageTimer);

		$('#message-'+type+' div').html(message);
		$('#message-'+type).hide().removeClass('hidden').css('z-index', 10000).slideDown('medium');

		this.messageTimer = setTimeout(function(){
			$('.alert-dismissable-hide').slideUp('fast', function()
			{
				$(this).css('z-index', 1000);
			});
		}, this.messageShowTime);
	},

	modalConfirm: function(title, message, action, modalId)
	{
		if (modalId === undefined)
			modalId = "modal";

		$('#'+modalId+' .modal-title').html(title);
		$('#'+modalId+' .modal-body').html('<p>' + message + '</p>');
		$('#'+modalId+' .modal-footer').show();

		$('#'+modalId).modal('show');

		if (action !== undefined && action !== null)
			$('#'+modalId+' .btn-primary').off('click').on('click', action);
	},

	modalAjax: function(url, type, data, callbackFunction, modalId)
	{
		if (modalId === undefined)
			modalId = "modal";

		if (data === undefined)
			data = [];

		$.ajax({
			type:     type,
			url:      url,
			data:     SolidSite.prepData(data),
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
	},

	capitalizeFirstLetter: function(string)
	{
		return string.charAt(0).toUpperCase() + string.slice(1);
	},

	setUserState: function(name, state)
	{
		$.ajax({
			url:  Fractal.createUrl('api/set-user-state'),
			type: 'post',
			data: SolidSite.prepData({name: name, state: state}),

			success: function(result){
				console.log('User State Saved: '+name+' = '+state);
			},
			error: function(){
				console.log('User State Change Failed: '+name+' = '+state);
			}
		});
	},

	removeUserState: function(name, state)
	{
		$.ajax({
			url:  Fractal.createUrl('api/remove-user-state'),
			type: 'post',
			data: SolidSite.prepData({name: name, state: state}),

			success: function(result){
				console.log('User State Removed: '+name+' = '+state);
			},
			error: function(){
				console.log('User State Removal Failed: '+name+' = '+state);
			}
		});
	},

	/* Setup Search and Pagination Functions */
	searchContent: function()
	{
		if (this.contentType !== undefined && !this.searching)
		{
			this.searching = true;

			var postData = SolidSite.prepData($('#form-search').add('.search-filter').serialize());

			$('.search-filter').each(function(){
				postData[$(this).attr('name')] = $(this).val();
			});

			$('.alert-dismissable').addClass('hidden');

			$.ajax({
				url:      currentUrl+'/search',
				type:     'post',
				data:     postData,
				dataType: 'json',

				success: function(result)
				{
					if (result.message !== undefined) {
						if (result.resultType == "Success")
							setMainMessage(result.message, 'success');
						else
							setMainMessage(result.message, 'error');
					}

					createPaginationMenu(result.pages);

					$('table.table tbody').html(result.tableBody);

					Fractal.initContentTable();

					Fractal.searching = false;
				},

				error: function()
				{
					Fractal.setMainMessage(fractalMessages.errorGeneral, 'error');

					Fractal.searching = false;
				}
			});
		}
	},

	createPaginationMenu: function(pages)
	{
		this.lastPage = pages;

		if (this.lastPage > 1)
		{
			var pagination = '<li'+(page == 1 ? ' class="disabled"' : '')+'><a href="" data-page="1">&laquo;</a></li>' + "\n";

			for (p = (page - 3); p <= (page + 3); p++)
			{
				if (p > 0 && p <= this.lastPage)
					pagination += '<li'+(page == p ? ' class="active"' : '')+'><a href="" data-page="'+p+'">'+p+'</a></li>' + "\n";
			}

			pagination += '<li'+(page == this.lastPage ? ' class="disabled"' : '')+'>';
			pagination += '<a href="" data-page="'+this.lastPage+'">&raquo;</a></li>' + "\n";

			$('.pagination').html(pagination);

			this.initPagination();

			$('.pagination').fadeIn('fast');
		} else {
			$('.pagination').fadeOut('fast');
		}

		this.previousLastPage = this.lastPage;
	},

	initPagination: function()
	{
		$('.pagination li a[href=""]').off('click').on('click', function(e)
		{
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
	},

	formErrorCallback: function(fieldContainer)
	{
		fieldContainer.find('[data-toggle="tooltip"]').tooltip({html: true});
	},

	initContentTable: function()
	{
		if (this.contentType !== undefined)
		{
			$('.action-item').click(function(e)
			{
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

			$('table td.actions a[title]').tooltip();
		}
	},

	actionItem: function()
	{
		$('#modal').modal('hide');

		var url = Fractal.createUrl(contentType.pluralize() + '/' + contentId);

		if (itemActionType != "delete")
			url += '/' + itemAction;

		if (itemActionUrl !== undefined && itemActionUrl != "")
			url = itemActionUrl;

		$.ajax({
			url:      url,
			type:     itemActionType,
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success") {
					$('#'+contentType+'-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	},

	initPagesTable: function()
	{
		$('.delete-page').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-page-id');
			modalConfirm(fractalLabels.deletePage+': <strong>'+$(this).parents('tr').children('td.title').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.page), actionDeletePage);
		});

		$('table td.actions a[title]').tooltip();
	},

	actionDeletePage: function()
	{
		$('#modal').modal('hide');

		$.ajax({
			url:      Fractal.createUrl('pages/' + contentId),
			type:     'delete',
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					$('#page-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	},

	initFilesTable: function()
	{
		$('.delete-file').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-file-id');
			modalConfirm(fractalLabels.deleteFile+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.file), actionDeleteFile);
		});

		$('table td.actions a[title]').tooltip();
	},

	actionDeleteFile: function()
	{
		$('#modal').modal('hide');
		$.ajax({
			url:      Fractal.createUrl('files/' + contentId),
			type:     'delete',
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					$('#file-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	},

	initUsersTable: function()
	{
		$('.ban-user').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-user-id');
			modalConfirm(fractalLabels.banUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmBanUser, actionBanUser);
		});

		$('.unban-user').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-user-id');
			modalConfirm(fractalLabels.unbanUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmUnbanUser, actionUnbanUser);
		});

		$('.delete-user').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-user-id');
			modalConfirm(fractalLabels.deleteUser+': <strong>'+$(this).parents('tr').children('td.username').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.user), actionDeleteUser);
		});

		$('table td.actions a[title]').tooltip();
	},

	actionBanUser: function()
	{
		$('#modal').modal('hide');

		$.ajax({
			url:      Fractal.createUrl('/users/' + contentId + '/ban'),
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					$('#user-'+contentId).addClass('danger');
					$('#user-'+contentId+' td.actions a.ban-user').addClass('hidden');
					$('#user-'+contentId+' td.actions a.unban-user').removeClass('hidden');

					$('#user-'+contentId+' td.banned').html('<span class="boolean-true">Yes</span>');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(fractalMessages.errorGeneral, 'error');
			}
		});
	},

	actionUnbanUser: function()
	{
		$('#modal').modal('hide');

		$.ajax({
			url:      Fractal.createUrl('users/' + contentId + '/unban'),
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success") {
					$('#user-'+contentId).removeClass('danger');
					$('#user-'+contentId+' td.actions a.unban-user').addClass('hidden');
					$('#user-'+contentId+' td.actions a.ban-user').removeClass('hidden');

					$('#user-'+contentId+' td.banned').html('<span class="boolean-false">No</span>');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(fractalMessages.errors.general, 'error');
			}
		});
	},

	actionDeleteUser: function()
	{
		$('#modal').modal('hide');
		$.ajax({
			url:      Fractal.createUrl('users/' + contentId),
			type:     'delete',
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					$('#user-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(fractalMessages.errors.general, 'error');
			}
		});
	},

	initUserRolesTable: function()
	{
		$('.delete-user-role').click(function(e)
		{
			e.preventDefault();

			contentId = $(this).attr('data-role-id');
			modalConfirm(fractalLabels.deleteRole+': <strong>'+$(this).parents('tr').children('td.name').text()+'</strong>', fractalMessages.confirmDelete.replace(':item', fractalLabels.role), actionDeleteUserRole);
		});

		$('table td.actions a[title]').tooltip();
	},

	actionDeleteUserRole: function()
	{
		$('#modal').modal('hide');

		$.ajax({
			url:      Fractal.createUrl('user-roles/' + contentId),
			type:     'delete',
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					$('#role-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(fractalMessages.errors.general, 'error');
			}
		});
	},

	initModalTriggers: function()
	{
		$('.trigger-modal[data-modal-ajax-uri]').off('click').on('click', function(e)
		{
			e.preventDefault();

			var url              = Fractal.createUrl($(this).attr('data-modal-ajax-uri'));
			var type             = $(this).attr('data-modal-ajax-action') == "get" ? "get" : "post";
			var dataVars         = $(this).attr('data-modal-ajax-data-variables') !== undefined ? $(this).attr('data-modal-ajax-data-variables').split(',') : [];
			var callbackFunction = $(this).attr('data-modal-callback-function');

			var data = {};
			for (v in dataVars)
			{
				dataVar = window[dataVars[v]];

				if (dataVar === undefined)
					dataVar = "";

				data[dataVars[v]] = dataVar;
			}

			Fractal.modalAjax(url, type, data, callbackFunction);
		});
	},

	initMarkdownField: function(field)
	{
		if (field)
		{
			field.off('focus').on('focus', function()
			{
				Fractal.renderMarkdownPreview($(this));
				$('#markdown-preview').fadeIn();

			}).off('keydown').on('keydown', function(e)
			{
				if (e.keyCode == 9)
				{
					var value     = "\t";
					var startPos  = this.selectionStart;
					var endPos    = this.selectionEnd;
					var scrollTop = this.scrollTop;
					this.value    = this.value.substring(0, startPos) + value + this.value.substring(endPos,this.value.length);
					this.focus();

					this.selectionStart = startPos + value.length;
					this.selectionEnd   = startPos + value.length;
					this.scrollTop      = scrollTop;

					e.preventDefault();
				}
			}).off('keyup').on('keyup', function()
			{
				Fractal.renderMarkdownPreview($(this));
				Fractal.checkForSelectFileMediaItem($(this));

			}).off('blur').on('blur', function(){
				$('#markdown-preview').fadeOut();
			});

			this.renderMarkdownPreview(field);
		}
	},

	initMarkdownFields: function()
	{
		$('textarea.markdown').each(function(){
			Fractal.initMarkdownField($(this));
		});
	},

	renderMarkdownPreview: function(field)
	{
		var markdownContent = this.converter.makeHtml(field.val());

		$('#markdown-preview-content').html(markdownContent);
		$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);

		field.parents('.row').find('.markdown-preview-content').html(markdownContent);

		this.markdownContentField = field;
		this.markdownPreviewField = this.markdownContentField.parents('.row').find('.markdown-preview-content');

		if (field.getCursorPosition() >= field.val().length - 10)
			this.markdownPreviewField.scrollTop(this.markdownPreviewField.height() * 3);

		clearTimeout(this.markdownContentUpdateTimer);

		this.markdownContentUpdateTimer = setTimeout(this.incrementMarkdownContentUpdateTimer, 3000);
	},

	incrementMarkdownContentUpdateTimer: function()
	{
		clearTimeout(this.markdownContentUpdateTimer);

		$.ajax({
			type: 'post',
			url:  Fractal.createUrl('pages/render-markdown-content'),
			data: SolidSite.prepData({content: Fractal.markdownContentField.val()}),

			success: function(content)
			{
				$('#markdown-preview-content').html(content);
				$('#markdown-preview-content').animate({scrollTop: $('#markdown-preview-content').height()}, 500);

				Fractal.markdownContentField.parents('.row').find('.markdown-preview-content').html(content);
			}
		});
	},

	checkForSelectFileMediaItem: function(field)
	{
		var wysiwyg = field.hasClass('field-content-html');

		if (wysiwyg)
			var text = CKEDITOR.instances[field.attr('id')].getData();
		else
			var text = field.val();

		if (text.substr(-5) == "</p>\n")
			text = text.substr(0, (text.length - 5));

		var url = this.createUrl('api/select-file-media-item');

		if (text.substr(-5) == "file:")
		{
			var type = "File";
			this.modalAjax(url, 'post', {type: type}, this.selectFileMediaItem(field, type));
		} else {
			if (this.activeActions['selectingFile'] === true)
				$('#modal').modal('hide');
		}

		if (text.substr(-7) == "[image:")
		{
			var type = "File";
			this.modalAjax(url, 'post', {type: type}, this.selectFileMediaItem(field, type, true));
		} else {
			if (this.activeActions['selectingFile'] === true)
				$('#modal').modal('hide');
		}

		if (text.substr(-7) == "[media:")
		{
			var type = "Media Item";
			this.modalAjax(url, 'post', {type: type}, this.selectFileMediaItem(field, type, true));
		} else {
			if (this.activeActions['selectingMediaItem'] === true)
				$('#modal').modal('hide');
		}
	},

	selectFileMediaItem: function(field, type, addClosingBracket)
	{
		var wysiwyg = field.hasClass('field-content-html');
		var text    = wysiwyg ? CKEDITOR.instances[field.attr('id')].getData() : field.val();

		if (wysiwyg && text.substr(-5) == "</p>\n")
			text= text.substr(0, (text.length - 5));

		if (type == "File")
		{
			this.activeActions['selectingFile'] = true;

			setTimeout(function()
			{
				$('#select-file li').off('click').on('click', function()
				{
					text += $(this).attr('data-file-id') + (addClosingBracket === true ? ']' : '');

					if (wysiwyg)
						CKEDITOR.instances[field.attr('id')].setData(text);
					else
						field.val(text);

					Fractal.activeActions['selectingFile'] = false;
					$('#modal').modal('hide');

					if (wysiwyg)
						setTimeout(function(){ focusWysiwyg(field); }, 100);
					else
						focusField(field);
				});
			}, 2000);

		} else {
			this.activeActions['selectingMediaItem'] = true;

			setTimeout(function(){
				$('#select-media-item li').off('click').on('click', function()
				{
					text += $(this).attr('data-media-item-id') + (addClosingBracket === true ? ']' : '');

					if (wysiwyg)
						CKEDITOR.instances[field.attr('id')].setData(text);
					else
						field.val(text);

					Fractal.activeActions['selectingMediaItem'] = false;
					$('#modal').modal('hide');

					if (wysiwyg)
						setTimeout(function(){ focusWysiwyg(field); }, 100);
					else
						focusField(field);
				});
			}, 2000);
		}
	},

	initAutoSave: function()
	{
		setInterval(function()
		{
			Fractal.saveContent();

		}, this.autoSaveRate);

		// allow manual save with CTRL / CMD + S
		$(window).bind('keydown', function(e)
		{
			if (e.ctrlKey || e.metaKey)
			{
				switch (String.fromCharCode(e.which).toLowerCase())
				{
					case 's':
						e.preventDefault();
						Fractal.saveContent();
						break;
				}
			}
		});
	},

	saveContent: function()
	{
		var data = $('form').serializeArray();

		data.push({
			name:  'content_type',
			value: this.contentType,
		});

		SolidSite.post(Fractal.createUrl('api/auto-save'), data, function(result)
		{
			if (result)
			{
				Fractal.setMainMessage(Fractal.messages.success.form_content_saved, 'success');
			}
		});
	},

	focusField: function(field)
	{
		field.focus();

		var value = field.val();
		field.val('');
		field.val(value);
	},

	focusWysiwyg: function(field)
	{
		var editor = CKEDITOR.instances[field.attr('id')];

		editor.focus();

		var range = editor.createRange();

		range.moveToElementEditEnd(range.root);

		editor.getSelection().selectRanges([range]);
	},

	upperCaseWords: function(str)
	{
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
	},

	strToSlug: function(string)
	{
		var slug = string.toLowerCase()
			.replace(/!/g, '').replace(/\?/g, '').replace(/@/g, '')
			.replace(/#/g, '').replace(/\$/g, '').replace(/%/g, '')
			.replace(/&/g, '').replace(/\*/g, '').replace(/\+/g, '')
			.replace(/=/g, '').replace(/:/g, '').replace(/;/g, '')
			.replace(/\./g, '').replace(/,/g, '').replace(/'/g, '')
			.replace(/"/g, '').replace(/\//g, '-').replace(/\\/g, '-')
			.replace(/\(/g, '-').replace(/\)/g, '-').replace(/\[/g, '-')
			.replace(/\]/g, '-').replace(/ /g, '-').replace(/_/g, '-')
			.replace(/--/g, '-').replace(/--/g, '-');

		return slug;
	},

}