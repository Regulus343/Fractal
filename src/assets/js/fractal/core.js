/*
|------------------------------------------------------------------------------
| Fractal JS
|------------------------------------------------------------------------------
|
| Last Updated: November 3, 2015
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
	contentChanged:             false,

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

	autoSaveRate:               (1000 * 60), // auto-save every minute

	caretPos:                   null,

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
		$('input[type="file"].file-upload-button').each(function()
		{
			$(this).addClass('hidden');

			var fileType   = $(this).attr('data-file-type') !== undefined ? $(this).attr('data-file-type') : "File";
			var buttonText = "Select "+fileType;
			var button     = $('<button class="btn btn-default icon block"><i class="fa fa-file"></i> '+buttonText+'</button>').click(function(e){
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

		}).change(function()
		{
			var path     = $(this).val().split('\\');
			var filename = path[(path.length - 1)];

			$('#'+$(this).attr('id')+'-dummy').val(filename);
		});

		// initialize number fields
		$('input[type="number"], input.number').keyup(function()
		{
			if (isNaN($(this).val()) || $(this).val() == "")
				$(this).val('');

		}).change(function(){
			if (isNaN($(this).val()) || $(this).val() == "")
				$(this).val('');
		});

		// initialize select fields
		$('select').select2();

		// initialize embedded audio
		audiojs.events.ready(function()
		{
			audiojs.createAll();
		});

		// initialize button dropdown fields
		this.initButtonDropdownFields();

		// initialize search, content, and table sorting
		$('#form-search').submit(function(e)
		{
			e.preventDefault();

			$('#field-changing-page').val(0);

			Fractal.searchContent();
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

		$('#form-search input, #form-search select, input.search-filter, select.search-filter').change(function()
		{
			$('#field-changing-page').val(0);
			Fractal.searchContent();
		});

		this.initContentTable();

		this.initPagination();

		$('table.table-sortable thead tr th').each(function()
		{
			if ($(this).attr('data-sort-field') !== undefined)
			{
				$(this).addClass('sortable');

				var icon = "record";

				if ($(this).attr('data-sort-field') == Fractal.sortField)
				{
					if (Fractal.sortOrder == "desc")
					{
						$(this).addClass('sort-desc');
						icon = "upload";
					} else {
						$(this).addClass('sort-asc');
						icon = "download";
					}
				}

				$(this).html($(this).html()+' <span class="sort-icon glyphicon glyphicon-'+icon+'"></span>');

				$(this).mouseenter(function()
				{
					if (!$(this).hasClass('sort-changed'))
					{
						if ($(this).hasClass('sort-asc'))
						{
							$(this).children('span.sort-icon')
								.addClass('glyphicon-upload')
								.removeClass('glyphicon-download');
						}
						else
						{
							$(this).children('span.sort-icon')
								.addClass('glyphicon-download')
								.removeClass('glyphicon-upload')
								.removeClass('glyphicon-record');
						}
					}
				}).mouseleave(function()
				{
					$(this).removeClass('sort-changed');

					if ($(this).hasClass('sort-asc'))
					{
						$(this).children('span.sort-icon')
							.addClass('glyphicon-download')
							.removeClass('glyphicon-upload');
					}
					else if ($(this).hasClass('sort-desc'))
					{
						$(this).children('span.sort-icon')
							.addClass('glyphicon-upload')
							.removeClass('glyphicon-download');
					}
					else
					{
						$(this).children('span.sort-icon')
							.addClass('glyphicon-record')
							.removeClass('glyphicon-download')
							.removeClass('glyphicon-upload');
					}
				}).click(function()
				{
					Fractal.sortField = $(this).attr('data-sort-field');

					$('table.table-sortable thead tr th.sortable').each(function(){
						if ($(this).attr('data-sort-field') != Fractal.sortField)
							$(this)
								.removeClass('sort-asc')
								.removeClass('sort-desc')
								.removeClass('sort-changed');
					});

					$(this).addClass('sort-changed');

					$('#field-sort-field').val(Fractal.sortField);

					if ($(this).hasClass('sort-asc'))
					{
						$(this)
							.addClass('sort-desc')
							.removeClass('sort-asc');

						$(this).children('span.sort-icon')
							.addClass('glyphicon-download')
							.removeClass('glyphicon-upload');

						$('#field-sort-order').val('desc');
					}
					else
					{
						$(this)
							.addClass('sort-asc')
							.removeClass('sort-desc');

						$(this).children('span.sort-icon')
							.addClass('glyphicon-upload')
							.removeClass('glyphicon-download');

						$('#field-sort-order').val('asc');
					}

					Fractal.searchContent();
				});
			}
		});

		// allow tab characters for some textareas
		$('textarea.tab').keydown(function(e)
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

			$('html, body, #container').animate({
				scrollTop: '0px'
			}, 500);
		});

		// create function to get cursor position in field
		(function ($, undefined)
		{
			$.fn.getCursorPosition = function()
			{
				var el  = $(this).get(0);
				var pos = 0;

				if('selectionStart' in el)
				{
					pos = el.selectionStart;
				}
				else if ('selection' in document)
				{
					el.focus();
					var sel       = document.selection.createRange();
					var selLength = document.selection.createRange().text.length;

					sel.moveStart('character', -el.value.length);

					pos = sel.text.length - selLength;
				}

				return pos;
			}
		})(jQuery);

		// initialize markdown fields
		this.initMarkdownFields();

		// initialize tabs
		$('.nav-tabs a').click(function(e)
		{
			e.preventDefault();

			$(this).tab('show');
		});

		// initialize trees
		$('ul.tree>li>a.tree-expand-collapse').click(function(e)
		{
			e.preventDefault();

			var listItem = $(this).parent('li');

			if (listItem.data('expanded'))
			{
				listItem.find('>ul').slideUp();

				listItem.data('expanded', 0);

				$(this).find('i').addClass('fa-plus').removeClass('fa-minus');
			}
			else
			{
				listItem.find('>ul').slideDown();

				listItem.data('expanded', 1);

				$(this).find('i').addClass('fa-minus').removeClass('fa-plus');
			}
		});
	},

	setLabels: function(labels)
	{
		this.setTranslations('labels', labels);
	},

	setMessages: function(messages)
	{
		this.setTranslations('messages', messages);
	},

	setTranslations: function(prefix, translations)
	{
		if (typeof translations == "object")
		{
			$.extend(String.prototype,
			{
				camelize: function ()
				{
					var string = this.replace (/(?:^|[-_])(\w)/g, function (_, c)
					{
						return c ? c.toUpperCase () : '';
					});

					return string.substr(0, 1).toLowerCase() + string.substr(1);
				}
			});

			var prefixSplit = prefix.split('.');

			if (prefixSplit.length >= 1 && this[prefixSplit[0]] === undefined)
				this[prefixSplit[0]] = {};

			if (prefixSplit.length >= 2 && this[prefixSplit[0]][prefixSplit[1]] === undefined)
				this[prefixSplit[0]][prefixSplit[1]] = {};

			if (prefixSplit.length >= 3 && this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]] === undefined)
				this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]] = {};

			if (prefixSplit.length >= 4 && this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]][prefixSplit[3]] === undefined)
				this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]][prefixSplit[3]] = {};

			for (t in translations)
			{
				var translation = translations[t];

				var key = t.camelize(true);

				if (typeof translation == "object")
				{
					var newPrefix = prefix + '.' + key;
					this.setTranslations(newPrefix, translation);
				}
				else
				{
					if (prefixSplit.length == 1)
						this[prefixSplit[0]][key] = translation;

					else if (prefixSplit.length == 2)
						this[prefixSplit[0]][prefixSplit[1]][key] = translation;

					else if (prefixSplit.length == 3)
						this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]][key] = translation;

					else if (prefixSplit.length >= 3)
						this[prefixSplit[0]][prefixSplit[1]][prefixSplit[2]][prefixSplit[3]][key] = translation;
				}
			}
		}
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

	trans: function(key, replacements)
	{
		if (key === undefined)
			key = "";

		if (key.substr(0, 8) != "messages" && key.substr(0, 6) != "labels")
			key = "labels." + key;

		key = key.split('.');

		var trans = "";

		if (key.length == 1)
			trans = Fractal[key[0]];

		else if (key.length == 2)
			trans = Fractal[key[0]][key[1]];

		else if (key.length == 3)
			trans = Fractal[key[0]][key[1]][key[2]];

		else if (key.length == 4)
			trans = Fractal[key[0]][key[1]][key[2]][key[3]];

		if (trans === undefined)
			trans = "";

		if (typeof replacements == "object")
		{
			for (r in replacements)
			{
				trans = trans.replace(':'+r, replacements[r]);
			}
		}

		return trans;
	},

	transChoice: function(key, number)
	{
		var trans = this.trans(key);

		if (number === undefined)
			number = 1;

		trans = trans.split('|');

		return number == 1 ? trans[0] : trans[1];
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
				url:      Fractal.currentUrl + '/search',
				type:     'post',
				data:     postData,
				dataType: 'json',

				success: function(result)
				{
					if (result.message !== undefined) {
						if (result.resultType == "Success")
							Fractal.setMainMessage(result.message, 'success');
						else
							Fractal.setMainMessage(result.message, 'error');
					}

					Fractal.createPaginationMenu(result.pages);

					$('table.table tbody').html(result.tableBody);

					Fractal.initContentTable();

					Fractal.searching = false;
				},

				error: function()
				{
					Fractal.setMainMessage(Fractal.messages.errors.general, 'error');

					Fractal.searching = false;
				}
			});
		}
	},

	createPaginationMenu: function(pages)
	{
		this.lastPage = pages;

		var page = this.page;

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
				var page = parseInt($(this).attr('data-page'));

				$('#field-page').val(page);
				$('#field-changing-page').val(1);

				$('.pagination li').removeClass('active');
				$('.pagination li a').each(function()
				{
					if ($(this).text() == page)
						$(this).parents('li').addClass('active');
				});

				if (page == 1)
					$('.pagination li:first-child').addClass('disabled');
				else
					$('.pagination li:first-child').removeClass('disabled');

				if (page == Fractal.lastPage)
					$('.pagination li:last-child').addClass('disabled');
				else
					$('.pagination li:last-child').removeClass('disabled');

				Fractal.page = page;

				Fractal.searchContent();
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

				contentType = Fractal.contentType;
				contentId   = $(this).data('item-id');

				var itemType = Fractal.transChoice(contentType.replace(/\-/g, '_').camelize(true)).toLowerCase();
				var itemName = $(this).data('item-name');

				itemAction         = $(this).data('action');
				itemActionType     = $(this).data('action-type') !== undefined ? $(this).data('action-type') : 'post';
				itemActionMessage  = $(this).data('action-message');
				itemActionUrl      = $(this).data('action-url');
				itemActionFunction = $(this).data('action-function');

				if (itemName !== undefined && itemName != "" && Fractal.trans('messages.' + itemActionMessage + 'WithName') !== undefined)
					itemActionMessage += 'WithName';

				var confirmTitle = Fractal.trans('labels.' + $(this).data('action-title'), {item: Fractal.transChoice(contentType)});

				if (confirmTitle == "")
					confirmTitle = Fractal.trans(itemAction, {item: Fractal.transChoice(contentType)});

				if (confirmTitle == "")
					confirmTitle = "Complete Action";

				var replacements = {item: itemType};
				if (itemName !== undefined)
					replacements['name'] = itemName;

				var confirmMessage = Fractal.trans('messages.' + itemActionMessage, replacements);

				if (itemActionFunction !== undefined)
					Fractal.modalConfirm(confirmTitle, confirmMessage, Fractal[itemActionFunction]);
				else
					Fractal.modalConfirm(confirmTitle, confirmMessage, Fractal.actionItem);
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

		var data = {};
		if (itemActionType != "get")
			data = SolidSite.prepData(data);

		$.ajax({
			url:      url,
			type:     itemActionType,
			data:     data,
			dataType: 'json',

			success: function(result)
			{
				if (result.resultType == "Success")
				{
					if (itemActionType == "delete")
						$('#'+contentType+'-'+contentId).addClass('hidden');

					Fractal.setMainMessage(result.message, 'success');
				} else {
					Fractal.setMainMessage(result.message, 'error');
				}
			},

			error: function()
			{
				Fractal.setMainMessage(Fractal.messages.errors.general, 'error');
			}
		});
	},

	actionBanUser: function()
	{
		$('#modal').modal('hide');

		$.ajax({
			url:      Fractal.createUrl('users/' + contentId + '/ban'),
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
				Fractal.setMainMessage(Fractal.messages.errors.general, 'error');
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
				if (result.resultType == "Success")
				{
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
				Fractal.setMainMessage(Fractal.messages.errors.general, 'error');
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

			setTimeout(function()
			{
				Fractal.renderMarkdownPreview(field);
			}, 250);
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

	initButtonDropdownFields: function(container)
	{
		if (container == undefined)
			var fieldMenu = $('.dropdown-menu-field');
		else
			var fieldMenu = container.find('.dropdown-menu-field');

		fieldMenu.each(function()
		{
			var buttonGroup = $(this).parent('.btn-group');

			var displayValue = buttonGroup.find('input').val();

			if ($(this).data('null-option') != "" && $(this).data('null-option') != null)
				var displayValueNull = $(this).data('null-option');
			else
				var displayValueNull = "Select an Option";

			if (displayValue == "" || displayValue == null)
			{
				displayValue = displayValueNull;
			}
			else
			{
				var option = $(this).find('li a[data-value="'+displayValue+'"]');

				if (option.length)
					displayValue = option.html();
			}

			buttonGroup.find('.dropdown-menu-field-value').html(displayValue);

			buttonGroup.find('li.null-option a').html(displayValueNull);
		});

		fieldMenu.find('li a').off('click').on('click', function(e)
		{
			e.preventDefault();

			var buttonGroup = $(this).parents('.btn-group');

			buttonGroup.find('.dropdown-menu-field-value').html($(this).html());

			buttonGroup.find('input').val($(this).data('value')).trigger('change');
		});
	},

	checkForSelectFileMediaItem: function(field)
	{
		var wysiwyg = field.hasClass('field-content-html');

		if (wysiwyg)
		{
			var text      = CKEDITOR.instances[field.attr('id')].getData();
			this.caretPos = text.length;
		}
		else
		{
			var text      = field.val();
			this.caretPos = field.caret().begin;
		}

		if (text.substr(-5) == "</p>\n")
			text = text.substr(0, (text.length - 5));

		var url = this.createUrl('api/select-file-media-item');

		if (text.substr((this.caretPos - 5), 5) == "file:")
		{
			var type = "File";
			this.modalAjax(url, 'post', {type: type}, this.selectFileMediaItem(field, type));
		} else {
			if (this.activeActions['selectingFile'] === true)
				$('#modal').modal('hide');
		}

		if (text.substr((this.caretPos - 7), 7) == "[image:")
		{
			var type = "File";
			this.modalAjax(url, 'post', {type: type}, this.selectFileMediaItem(field, type, true));
		} else {
			if (this.activeActions['selectingFile'] === true)
				$('#modal').modal('hide');
		}

		if (text.substr((this.caretPos - 7), 7) == "[media:")
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
		var delay   = 1200;

		if (wysiwyg && text.substr(-5) == "</p>\n")
			text = text.substr(0, (text.length - 5));

		if (type == "File")
		{
			this.activeActions['selectingFile'] = true;

			setTimeout(function()
			{
				$('#select-file li').off('click').on('click', function()
				{
					var addedText = $(this).attr('data-file-id') + (addClosingBracket === true ? ']' : '');

					if (wysiwyg)
					{
						text += addedText;
						CKEDITOR.instances[field.attr('id')].setData(text);
					}
					else
					{
						text = text.substr(0, Fractal.caretPos) + addedText + text.substr(Fractal.caretPos);
						field.val(text);

						Fractal.caretPos += addedText.length;
					}

					Fractal.activeActions['selectingFile'] = false;
					$('#modal').modal('hide');

					if (wysiwyg)
						setTimeout(function(){
							focusWysiwyg(field);
						}, 100);

					else
						Fractal.setCaretPosition(field);
				});
			}, delay);
		}
		else
		{
			this.activeActions['selectingMediaItem'] = true;

			setTimeout(function()
			{
				$('#select-media-item li').off('click').on('click', function()
				{
					var addedText = $(this).attr('data-media-item-id') + (addClosingBracket === true ? ']' : '');

					if (wysiwyg)
					{
						text += addedText;
						CKEDITOR.instances[field.attr('id')].setData(text);
					}
					else
					{
						text = text.substr(0, Fractal.caretPos) + addedText + text.substr(Fractal.caretPos);
						field.val(text);

						Fractal.caretPos += addedText.length;
					}

					Fractal.activeActions['selectingMediaItem'] = false;
					$('#modal').modal('hide');

					if (wysiwyg)
						setTimeout(function(){
							focusWysiwyg(field);
						}, 100);

					else
						Fractal.setCaretPosition(field);
				});
			}, delay);
		}
	},

	initAutoSave: function()
	{
		$('input, textarea, select').keypress(function()
		{
			Fractal.contentChanged = true;
		});

		setInterval(function()
		{
			if (Fractal.contentChanged)
				Fractal.saveContent();

			Fractal.contentChanged = false;

		}, this.autoSaveRate);

		// allow saving by clicking "Save" button
		$('.btn-save-content').off('click').on('click', function(e)
		{
			e.preventDefault();

			Fractal.saveContent(true);
		});

		// allow manual save with CTRL / CMD + S
		$(window).bind('keydown', function(e)
		{
			if (e.ctrlKey || e.metaKey)
			{
				switch (String.fromCharCode(e.which).toLowerCase())
				{
					case 's':
						e.preventDefault();
						Fractal.saveContent(true);
						break;
				}
			}
		});
	},

	saveContent: function(manuallySaved)
	{
		if (manuallySaved === undefined)
			manuallySaved = false;

		var data = $('form').serializeArray();

		for (d in data)
		{
			if (data[d].name == "_method")
				data[d].value = "POST";
		}

		data.push({
			name:  'content_type',
			value: this.contentType,
		});

		SolidSite.post(Fractal.createUrl('api/save-content'), data, function(result)
		{
			if (parseInt(result))
			{
				Fractal.setMainMessage(Fractal.trans('messages.success.formContentSaved'), 'success');
			} else {
				if (manuallySaved)
					Fractal.setMainMessage(Fractal.trans('messages.errors.saveContent'), 'error');
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

	preventBackspaceNavigation: function()
	{
		$(document).keydown(function(e)
		{
			if (e.keyCode == 8 && e.target.tagName == "BODY")
				e.preventDefault();
		});
	},

	setSelectionRange: function(field, start, end)
	{
		if (typeof field.attr == "function")
			field = document.getElementById(field.attr('id'));

		if (field.setSelectionRange) {
			field.focus();
			field.setSelectionRange(start, end);
		}
		else if (field.createTextRange)
		{
			field.focus();
			var range = field.createTextRange();

			range.collapse(true);
			range.moveEnd('character', end);
			range.moveStart('character', start);
			range.select();
		}
	},

	setCaretPosition: function(field, position)
	{
		if (position === undefined)
			position = this.caretPos;

		this.setSelectionRange(field, position, position);
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
		if (string === undefined)
			string = "";

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

	initSlugField: function(id)
	{
		if (id === undefined)
			id = "field-slug";

		$('#'+id).keyup(function(e)
		{
			if (e.keyCode == 9)
				return;

			var slug = Fractal.strToSlug($('#'+id).val());
			$('#'+id).val(slug);
		});
	},

};