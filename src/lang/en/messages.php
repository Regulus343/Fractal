<?php

return [

	'errors' => [
		'general'                    => 'Something went wrong. Please correct any errors and try again.',
		'not_found'                  => 'The :item you selected was not found.',
		'no_items'                   => 'The :item you selected contained no :items.',
		'log_in'                     => 'Something went wrong. Please check your username and password and try again.',
		'already_logged_in'          => 'You are already logged in.',
		'log_in_required'            => 'You must be logged in to access the requested page.',
		'unauthorized'               => 'You are not authorized to access the requested page.',
		'delete_items_exist'         => 'The :item could not be deleted because it is assigned to <strong>:total</strong> existing :relatedItem.',
		'reset_password_invalid_uri' => 'To reset password, please click on the link that was sent to your email address.',
		'hosted_content_not_found'   => 'The hosted content you specified was not found.',
		'account_activation'         => 'Something went wrong with your attempt to activate your account.',
		'save_content'               => 'Your form content was unable to be saved. Please check for validation errors.',
	],

	'success' => [
		'created'                 => 'You have successfully created :item.',
		'updated'                 => 'You have successfully updated :item.',
		'activated'               => 'You have successfully activated :item.',
		'banned'                  => 'You have successfully banned :item.',
		'unbanned'                => 'You have successfully unbanned :item.',
		'deleted'                 => 'You have successfully deleted :item.',
		'undeleted'               => 'You have successfully undeleted :item.',
		'logged_in'               => 'Welcome back to :website, <strong>:user</strong>.',
		'logged_out'              => 'You have successfully logged out.',
		'forgot_password_emailed' => 'An email has been sent to your email address with further instructions on resetting your password.',
		'reset_password'          => 'You have successfully reset your password.',
		'account_activated'       => 'Your account has been successfully activated. You may now log in.',
		'form_content_saved'      => 'Your form content has been saved.',
	],

	'info' => [
		'account_already_activated' => 'Your account has already been activated. You may log in below.',
	],

	'confirm' => [
		'activate'         => 'Are you sure you want to activate this :item?',
		'delete'           => 'Are you sure you want to delete this :item?',
		'delete_with_name' => 'Are you sure you want to delete the :item entitled &ldquo;<strong>:name</strong>&rdquo;?',
		'delete_permanent' => 'This action cannot be undone.',
		'undelete'         => 'Are you sure you want to undelete this :item?',
		'ban_user'         => 'Are you sure you want to ban the user named &ldquo;<strong>:name</strong>&rdquo;?',
		'unban_user'       => 'Are you sure you want to unban the user named &ldquo;<strong>:name</strong>&rdquo;?',
	],

	'displaying' => [
		'items'          => 'Displaying <strong>:total</strong> :items.',
		'items_of_total' => 'Displaying <strong>:start - :end</strong> of <strong>:total</strong> :items.',
	],

	'no_items'    => 'There are currently no :items.',
	'select_item' => 'Select :item',

	'search_no_results'          => 'Your search for &ldquo;<strong>:terms</strong>&rdquo; yielded no results.',
	'search_no_results_no_terms' => 'Your search yielded no results.',
	'search_results'             => 'Your search for &ldquo;<strong>:terms</strong>&rdquo; yielded <strong>:total</strong> :items.',
	'search_results_no_terms'    => 'Your search yielded <strong>:total</strong> :items.',
	'search_no_terms'            => 'You did not enter any search terms.',

	'developer_mode_enabled'  => '<strong>Developer Mode</strong> enabled.',
	'developer_mode_disabled' => '<strong>Developer Mode</strong> disabled.',

	'not_published'       => 'This :item is not published. This means the page cannot be seen by regular users.',
	'not_published_until' => 'This :item is not published. This means the page cannot be seen by regular users. It is currently set to be published on :dateTime',

];