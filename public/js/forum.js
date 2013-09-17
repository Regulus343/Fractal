/*function searchEvents() {
	var data = getFormDataForPost('#form-search');

	$('#events').hide();
	$('#loading-events').show();

	$.ajax({
		url: baseURL + 'ajax/events/search',
		type: 'post',
		data: data,
		dataType: 'json',
		success: function(data){
			$('.square-select li').removeClass('selected');
			$('.square-select li.'+$('#search-event-type').val().toLowerCase()+'-events').addClass('selected');

			if (data.messages.info != undefined) {
				var message = '<div>'+data.messages.info+'</div>';
				if (data.messages.infoSub != undefined) message += '<div class="sub">'+data.messages.infoSub+'</div>';
				$('.message.info').fadeIn('fast').html(message);
			}

			events = data.events;
			loadEvents();
		},
		error: function(result){
			Boxy.alert('Something went wrong with your attempt to search. Please try again.', null, {
				title: 'Search - Error', closeable: true, closeText: 'X'
			});

			$('#events').fadeIn('fast');

			console.log('Search Events Failed');
		}
	});
}

function selectEventType() {
	var eventType = $('#search-event-type').val();
	$('#search-event-type').val(eventType);
	searchEvents(subSection);

	$('.square-select li').removeClass('selected');
	$('.square-select li.'+eventType.toLowerCase()+'-events').addClass('selected');

	if ($('#breadcrumb-trail li:last-child a').text() == "Events") {
		$('#breadcrumb-trail').append('<li> &raquo; <a href="' + baseURL + 'events">All</a></li>');
	}
	if (eventType == "All") {
		var uri = "";
		$('#breadcrumb-trail li:last-child').fadeOut('fast');
	} else {
		var uri = '/'+eventType.toLowerCase();
		$('#breadcrumb-trail li:last-child').fadeIn('fast');
	}
	$('#breadcrumb-trail li:last-child a').attr('href', baseURL + 'events/type' + uri).text(eventType);
}

function eventAction(type, id) {
	switch(type) {
		case "activate":
			message = 'Are you sure you want to '+type+' this event? Upon activation, an event can no longer be deactivated ';
			message += 'or deleted. An activated event may only be cancelled.'; break;
		case "delete":
			message = 'Are you sure you want to '+type+' this event? You can not undo this action.'; break;
		case "cancel":
			message = 'Are you sure you want to '+type+' this event?'; break;
	}
	Boxy.ask(message, ["Yes", "No"],
		function(val) {
			if (val == "Yes") {
				$.ajax({
					url: baseURL + 'ajax/events/' + id + '/action/' + type,
					success: function(data){
						if (data == "Success") {
							var typePastTense;
							switch(type) {
								case "activate":
									typePastTense = "activated";
									$('#event'+id+' .status').html('<span class="green"><strong>Active</strong></span>');
									$('#event'+id+' .number-attending-info').fadeIn('fast');
									$('#event'+id+' .attending-info').fadeIn('fast');
									$('#event'+id+' .action-attendance-status').fadeIn('fast');
									$('#event'+id+' .action-activate').fadeOut('fast');
									$('#event'+id+' .action-delete').fadeOut('fast');
									$('#event'+id+' .action-cancel').fadeIn('fast');
									break;
								case "delete":
									typePastTense = "deleted";
									if (contentID > 0) {
										document.location.href = baseURL + 'events/deleted';
									} else {
										$('#event'+id).fadeOut('fast').remove();
									}
									break;
								case "cancel":
									typePastTense = "cancelled";
									$('#event'+id+' .status').html('<span class="red"><strong>Cancelled</strong></span>');
									$('#event'+id+' .number-attending-info').fadeOut('fast');
									$('#event'+id+' .attending-info').fadeOut('fast');
									$('#event'+id+' .action-edit').fadeOut('fast');
									$('#event'+id+' .action-cancel').fadeOut('fast');
									break;
							}
							Boxy.alert('You have successfully '+typePastTense+' this event.', null,
									   {title: ucFirst(type)+' Event', closeable: true, closeText: 'X'}
							);
						} else if (data == "Error: Past Date") {
							var message;
							switch(type) {
								case "activate":
									message = 'The event\'s date is already past. Please set a future date before activating event.'; break;
								case "delete":
									message = 'The event\'s date is already past. Please set a future date before deleting event.'; break;
								case "cancel":
									message = 'The event\'s date is already past. You may no longer cancel this event.'; break;
							}
							Boxy.alert(message, null, {title: ucFirst(type)+' Event - Error', closeable: true, closeText: 'X'});
						} else {
							Boxy.alert('Something went wrong with your attempt to '+type+' the event. Please try again.', null,
									   {title: ucFirst(type)+' Event - Error', closeable: true, closeText: 'X'}
							);
						}
					},
					error: function(data){
						Boxy.alert('Something went wrong with your attempt to '+type+' the event. Please try again.', null,
								   {title: ucFirst(type)+' Event - Error', closeable: true, closeText: 'X'}
						);
						console.log(ucFirst(type)+' Event Failed');
					}
				});
			}
		},
		{title: ucFirst(type)+' Event', closeable: true, closeText: 'X'}
	);
}

function eventAttendanceStatus(id) {
	var options = ["Attending", "Maybe Attending", "Not Attending"];
	var status = $('#event'+id+' .attending').html();
	if (status != "Unspecified") options[3] = "Remove Status";
	Boxy.ask('Please select a status for this event below.', options,
		function(val) {
			switch (val) {
				case "Attending":
					var uri = "yes";
					break;
				case "Maybe Attending":
					var uri = "maybe";
					break;
				case "Not Attending":
					var uri = "no";
					break;
				case "Remove Status":
					var uri = "remove";
					break;
			}
			$.ajax({
				url: baseURL + 'ajax/events/' + id + '/attendance/' + uri,
				dataType: 'json',
				success: function(data){
					if (data.result == "Success") {
						switch (val) {
							case "Attending":
								$('#event'+id+' .attending').addClass('green')
															.removeClass('orange')
															.removeClass('red')
															.html('<strong>Yes</strong>');
								break;
							case "Maybe Attending":
								$('#event'+id+' .attending').removeClass('green')
															.addClass('orange')
															.removeClass('red')
															.html('<strong>Maybe</strong>');
								break;
							case "Not Attending":
								$('#event'+id+' .attending').removeClass('green')
															.removeClass('orange')
															.addClass('red')
															.html('<strong>No</strong>');
								break;
							case "Remove Status":
								$('#event'+id+' .attending').removeClass('green')
															.removeClass('orange')
															.removeClass('red')
															.html('Unspecified');
								break;
						}
						$('#event'+id+' .number-attending').html('<strong>'+data.number+'</strong>')
					} else if (data.result == "Error: Attendance Required") {
						Boxy.alert('You must attend your own event. You cannot change your attendance status for events that you create.', null,
							   {title: 'Change Attendance Status for Event - Error', closeable: true, closeText: 'X'}
						);
					} else {
						Boxy.alert('Something went wrong with your attempt to change your attendance status for this event. Please try again.', null,
							   {title: 'Change Attendance Status for Event - Error', closeable: true, closeText: 'X'}
						);
					}
				},
				error: function(data){
					Boxy.alert('Something went wrong with your attempt to change your attendance status for this event. Please try again.', null,
							   {title: 'Change Attendance Status for Event - Error', closeable: true, closeText: 'X'}
					);
					console.log('Attendance Status Failed');
				}
			});
		},
		{title: 'Change Attendance Status for Event', closeable: true, closeText: 'X'}
	);
}*/

if (threads === undefined) var threads;
if (posts === undefined)   var posts;

var forumMessage;
var forumMessageTimeout;
var forumMessageTimeLimit = 6000;
var forumScroll           = 0;
var forumScrollTime       = 500;
var forumSlideTime        = 250;

function scrollToElement(element) {
	$('html, body').animate({ scrollTop: $(element).offset().top - 7 }, forumScrollTime);
}

function showThreads() {
	if (threads != undefined && threads.length > 0) {
		var source   = $('#forum-threads-template').html();
		var template = Handlebars.compile(source);
		var context  = { threads: threads };
		var html     = template(context);

		$('#loading-forum-threads').hide();
		$('#forum-threads').html(html).slideDown('fast');
	} else {
		$('#loading-forum-threads').fadeOut('fast');
	}
}

function searchThreads() {
	var data = getFormDataForPost('#form-search');

	$('#forum-threads').hide();
	$('#loading-forum-threads').css('height', $('#forum-threads').height()).fadeIn('fast');

	$.ajax({
		url: baseURL + 'forum/ajax/search',
		type: 'post',
		data: data,
		dataType: 'json',
		success: function(result){
			showForumMessage('#message-forum-threads', 'info', result.message, result.messageSub, false);

			if (result.totalPages > 0) {

				/* Create and Set Up Pagination */
				var threadsPagination = buildForumPagination('threads', result.totalPages, result.currentPage);
				$('ul.forum-threads-pagination').html(threadsPagination).removeClass('hidden');
				setupForumPagination('threads');
			} else {
				$('ul.forum-threads-pagination').fadeOut();
			}

			threads = result.threads;
			if (threads != undefined && threads.length > 0) {
				var source   = $('#threads-template').html();
				var template = Handlebars.compile(source);
				var context  = { threads: threads };
				var html     = template(context);

				hideForumMessage('#add-post', 'success');

				$('#forum-threads').html(html).removeClass('hidden').show();
				$('#loading-forum-threads').hide();
			} else {
				$('#loading-forum-threads').fadeOut('fast');
			}

			/* Load WYSIHTML5 */
			setupWysiwygEditors();

			/* Set Up Thread Form */
			setupThreadForm();

			/* Set Up Post Actions */
			setupPostActions();

			/* Set Up Post Edit Countdown */
			setupEditCountdown();
		},
		error: function(){
			showForumMessage('#message-threads', 'info', forumMessages.noPosts, false, true);
			$('#loading-forum-threads').fadeOut('fast');
			console.log('Search Threads Failed');
		}
	});
}

function loadPosts() {
	$('#loading-posts').css('height', $('#forum-posts').height()).fadeIn('fast');
	$('#forum-posts').hide();

	clearTimeout(editPostCountdown);

	var page = $('#forum-posts-page').val();

	$.ajax({
		url: baseURL + 'forum/ajax/thread',
		type: 'post',
		data: { 'content_id': contentID, 'content_type': contentType, 'page': page },
		dataType: 'json',
		success: function(result){
			showForumMessage('#message-forum-posts', 'info', result.message, result.messageSub, false);

			if (result.totalPages > 0) {

				/* Create and Set Up Pagination */
				var postsPagination = buildForumPagination('posts', result.totalPages, result.currentPage);
				$('ul.forum-posts-pagination').html(postsPagination).removeClass('hidden');
				setupForumPagination('posts');
			} else {
				$('ul.forum-posts-pagination').fadeOut();
			}

			posts = result.posts;
			showPosts();

			/* Load WYSIHTML5 */
			setupWysiwygEditors();

			/* Set Up Thread Form */
			setupThreadForm();

			/* Set Up Post Actions */
			setupPostActions();

			/* Set Up Post Edit Countdown */
			setupEditCountdown();

			/* Scroll to Post */
			if (postScroll > 0) {
				setTimeout("scrollToElement('#forum-post"+postScroll+"');", 250);

				setTimeout("showForumMessage('#forum-post"+postScroll+" .top-messages', 'success', '"+postMessage+"', false, true);", 1000);
				postScroll  = false;
				postMessage = "";
			}
		},
		error: function(){
			showForumMessage('#message-posts', 'info', forumMessages.noPosts, false, true);
			$('#loading-posts').fadeOut('fast');
			console.log('Load Posts Failed');
		}
	});
}

function showPosts() {
	if (posts != undefined && posts.length > 0) {
		var source   = $('#forum-posts-template').html();
		var template = Handlebars.compile(source);
		var context  = { posts: posts };
		var html     = template(context);

		hideForumMessage('#add-post', 'success');

		$('#forum-posts').html(html).removeClass('hidden').show();
		$('#loading-forum-posts').hide();

		setupPostActions();
	} else {
		$('#loading-forum-posts').fadeOut('fast');
	}
}

function buildForumPagination(type, totalPages, currentPage) {
	var html = "";
	if (totalPages == 1) return html;
	if (currentPage == null || currentPage == "") currentPage = 1;
	if (totalPages > 5) {
		var startPage = currentPage - 4;
		if (startPage > 1) {
			var halfwayPage = 1 + Math.floor(startPage / 2);
			html += '<li><a href="" rel="1">1</a></li>';
			if (halfwayPage > 2) {
				html += '<li><a href="" rel="'+halfwayPage+'">...</a></li>';
			}
		} else {
			startPage = 1;
		}

		var endPage   = currentPage + 4;
		if (endPage > totalPages) endPage = totalPages;

		for (p = startPage; p <= endPage; p++) {
			if (p == currentPage) {
				html += '<li class="selected">';
			} else {
				html += '<li>';
			}
			html += '<a href="" rel="'+p+'">'+p+'</a></li>';
		}
		if (endPage < totalPages) {
			var halfwayPage = endPage + Math.round((totalPages - endPage) / 2);
			if (halfwayPage < totalPages) {
				html += '<li><a href="" rel="'+halfwayPage+'">...</a></li>';
			}
			html += '<li><a href="" rel="'+totalPages+'">'+totalPages+'</a></li>';
		}
	} else {
		for (p=1; p <= totalPages; p++) {
			if (p == currentPage) {
				html += '<li class="selected">';
			} else {
				html += '<li>';
			}
			html += '<a href="" rel="'+p+'">'+p+'</a></li>';
		}
	}
	return html;
}

function setupForumPagination(type) {
	if (type == "threads") {
		$('ul.forum-threads-pagination li a').each(function(){
			$(this).on('click', function(e){
				e.preventDefault();
				$('ul.forum-threads-pagination li').removeClass('selected');
				$(this).parents('li').addClass('selected');
				$('#threads-page').val($(this).attr('rel'));

				if ($(this).parents('ul').attr('id') == "forum-threads-pagination-top") {
					setTimeout("scrollToElement('#"+$(this).parents('ul').attr('id')+"');", 250);
				}

				loadThreads();
			});
		});
	} else if (type == "posts") {
		$('ul.forum-posts-pagination li a').each(function(){
			$(this).on('click', function(e){
				e.preventDefault();
				$('ul.forum-posts-pagination li').removeClass('selected');
				$(this).parents('li').addClass('selected');
				$('#posts-page').val($(this).attr('rel'));

				if ($(this).parents('ul').attr('id') == "forum-posts-pagination-top") {
					setTimeout("scrollToElement('#"+$(this).parents('ul').attr('id')+"');", 250);
				}

				loadPosts();
			});
		});
	}
}

function showForumMessage(elementID, type, message, messageSub, timeLimit) {
	$(elementID+' .message.'+type+' .main').html(message);
	if (messageSub) {
		$(elementID+' .message.'+type+' .sub').html(messageSub).show();
	} else {
		$(elementID+' .message.'+type+' .sub').html('').hide();
	}
	$(elementID+' .message.'+type).hide().removeClass('hidden').fadeIn('fast');

	if (timeLimit) {
		forumMessageTimeout = setTimeout("$('"+elementID+" .message."+type+"').fadeOut();", forumMessageTimeLimit);
	}
}

function hideForumMessage(elementID, type) {
	$(elementID+' .message.'+type).fadeOut();
}

function setupWysiwygEditors() {
	$('.wysihtml5-toolbar').remove();
	$('iframe.wysihtml5-sandbox').remove();
	$('textarea.wysiwyg').val('').show();
	$('textarea.wysiwyg').each(function(){
		$(this).wysihtml5({
			'stylesheets': baseURL + "assets/css/styles.css",
			'parserRules': wysihtml5ParserRules,
			'font-styles': false,
			'emphasis'   : true,
			'lists'      : true,
			'html'       : false,
			'link'       : true,
			'image'      : true
		});
	});
}

function setupThreadForm() {

	$('#btn-preview-thread').click(function(e){
		e.preventDefault();
		$('#preview').val(1);

		createThread();
	});

	$('#btn-create-thread').click(function(e){
		e.preventDefault();
		$('#preview').val(0);

		createThread();
	});
}

function createThread() {
	if ($('#title').val() == $('#title').attr('placeholder'))
		$('#title').val('');

	if ($('#post-content').val() == $('#post-content').attr('placeholder'))
		$('#post-content').val('');

	var data = $('#create-forum-thread form').serialize();
	$.ajax({
		url: baseURL + 'forum/thread/create/' + forumSectionSlug,
		type: 'post',
		data: data,
		dataType: 'json',
		success: function(result){
			if (result.resultType == "Success") {
				document.location.href = baseURL + result.redirectURI;
			} else if (result.resultType == "Success: Preview") {
				showForumMessage('#message-forum-thread', 'info', result.message, result.messageSub, true);
			} else {
				showForumMessage('#message-forum-thread', 'error', result.message, result.messageSub, true);
			}
		},
		error: function(result){
			console.log('Create Thread Failed');
		}
	});
}

function setupPostForm() {
	$('.form-post').off('submit');
	$('.form-post').on('submit', function(e){
		e.preventDefault();

		var url         = $(this).attr('action');
		var data        = $(this).serialize();
		if ($(this).parents('li').hasClass('add-reply')) {
			var containerID = "#"+$(this).parents('li').attr('id');
		} else if ($(this).parents('div').hasClass('edit-post')) {
			var containerID = "#"+$(this).parents('div').attr('id');
		} else {
			var containerID = "#add-post";
		}

		$.ajax({
			url: url,
			type: 'post',
			data: data,
			dataType: 'json',
			success: function(result) {
				if (result.resultType == "Success") {
					showPostMessage(containerID, 'success', forumMessages.posting, true);

					forumScroll  = result.postID;
					forumMessage = result.message;
					loadPosts();
				} else {
					showPostMessage(containerID, 'error', result.message, true);
				}
			},
			error: function(){
				console.log('Add/Edit Post Failed');
			}
		});
	});
}

function setupPostActions() {

	$('#forum-posts .button-edit').on('click', function(e){
		e.preventDefault();

		var postID = $(this).attr('rel');
		var label  = $(this).text().trim();

		if (label == forumLabels.cancelEdit) {
			$(this).children('span').text(forumLabels.edit);

			$('#forum-post'+postID+' .edit-post').slideUp(forumSlideTime);
		} else {
			$('#forum-posts .button-edit span').text(forumLabels.edit);
			$('#forum-posts .edit-forum-post').slideUp(forumSlideTime);

			$(this).children('span').text(forumLabels.cancelEdit);

			//set edit forum-post text field to forum-post text
			var text = $('#forum-post'+postID+' .forum-post .text').html();
			$('#forum-post-edit'+postID).val(text);
			$('#forum-post'+postID).find('iframe').contents().find('.wysihtml5-editor').html(text);

			$('#forum-post'+postID+' .edit-post').hide().removeClass('hidden').css('min-height', 0).slideDown(forumSlideTime);

			setTimeout("scrollToElement('#forum-post"+postID+"');", 250);
		}
	});

	$('#forum-posts .button-delete').on('click', function(e){
		e.preventDefault();
		var postID = $(this).attr('rel');

		Boxy.confirm(forumMessages.confirmDelete, function(){
			$.ajax({
				url: baseURL + 'forum-posts/delete/' + postID,
				dataType: 'json',
				success: function(result){
					if (result.resultType == "Success") {
						showForumMessage('#forum-post'+postID+' .top-messages', 'success', result.message, result.messageSub, true);
						setTimeout("$('#forum-post"+postID+"').slideUp("+forumSlideTime+");", 1500);
						setTimeout("$('#forum-post"+postID+"').remove();", 3000);
						$('#forum-posts li').each(function(){
							if ($(this).attr('data-parent-id') == postID) {
								$('#forum-post'+$(this).attr('data-parent-id')).remove();
							}
						});
					} else {
						showForumMessage('#forum-post'+postID+' .top-messages', 'error', result.message, result.messageSub, true);
					}
				},
				error: function(result){
					showForumMessage('#forum-post'+postID+' .top-messages', 'error', result.message, result.messageSub, true);
					console.log('Delete Post Failed');
				}
			});
		},
		{title: 'Delete Post', closeable: true, closeText: 'X'});
	});

	$('.button-reply-thread').click(function(e){
		e.preventDefault();

		$('#reply-forum-thread').hide().removeClass('hidden').css('min-height', 0).slideDown(forumSlideTime);

		setTimeout("scrollToElement('#reply-forum-thread');", 250);
	});

	$('#forum-posts .button-approve').on('click', function(e){
		e.preventDefault();

		var postID = $(this).attr('rel');
		var label  = $(this).text().trim();

		if (label == forumLabels.approve) {
			var title   = forumMessages.confirmApproveTitle;
			var message = forumMessages.confirmApprove;
		} else {
			var title   = forumMessages.confirmUnapproveTitle;
			var message = forumMessages.confirmUnapprove;
		}

		Boxy.confirm(message, function(){
			$.ajax({
				url: baseURL + 'forum/approve-post/' + postID,
				dataType: 'json',
				success: function(result){
					if (result.resultType == "Success") {
						if (result.approved) {
							$('#forum-post'+postID).removeClass('unapproved');
							$('#forum-post'+postID+' .button-approve .icon')
								.removeClass('icon-plus-sign')
								.addClass('icon-minus-sign');
							$('#forum-post'+postID+' .button-approve').text(forumLabels.unapprove);
						} else {
							$('#forum-post'+postID).addClass('unapproved');
							$('#forum-post'+postID+' .button-approve .icon')
								.removeClass('icon-minus-sign')
								.addClass('icon-plus-sign');
							$('#forum-post'+postID+' .button-approve').text(forumLabels.approve);
						}
						showForumMessage('#forum-post'+postID+' .top-messages', 'success', result.message, result.messageSub, true);
					} else {
						showForumMessage('#forum-post'+postID+' .top-messages', 'error', result.message, result.messageSub, true);
					}
				},
				error: function(result){
					showForumMessage('#forum-post'+postID+' .top-messages', 'error', result.message, result.messageSub, true);
					console.log('Approve Post Failed');
				}
			});
		},
		{title: title, closeable: true, closeText: 'X'});
	});

}

var editPostCountdown;
function setupEditCountdown() {
	$('#forum-posts .edit-countdown span.number').each(function(){
		editPostCountdown = setTimeout("postCountdown('#"+$(this).parents('li').attr('id')+" .edit-countdown span')", 1000);
	});
}

function postCountdown(element) {
	var newCount = parseInt($(element).text()) - 1;
	if (newCount <= 0) {
		clearTimeout(editPostCountdown);
		$(element).parents('.edit-countdown').fadeOut();
		$(element).parents('li').removeClass('editable');
		$(element).parents('li').children('ul.actions').children('li.action-edit').fadeOut('fast');
		$(element).parents('li').children('ul.actions').children('li.action-delete').fadeOut('fast');
		$(element).parents('li').children('div.edit-forum-post').slideUp();
	} else {
		if (newCount > 1) {
			$(element).text(newCount);
		} else {
			var singularText = $(element).parents('.edit-countdown').html().replace('seconds', 'second').replace((newCount + 1), newCount);
			$(element).parents('.edit-countdown').html(singularText);
		}
		editPostCountdown = setTimeout("postCountdown('"+element+"')", 1000);
	}
}

$(document).ready(function(){

	/* Show Threads  */
	showThreads();
	setupThreadForm();

	/* Show Posts */
	showPosts();
	setupPostForm();

	/* Set Up Wysiwyg Editors */
	setupWysiwygEditors();

	$('#select-forum-section').click(function(){
		if ($(this).val() == "") {
			document.location.href = baseURL + 'forum';
		} else {
			document.location.href = baseURL + 'forum/' + $(this).val();
		}
	});

});