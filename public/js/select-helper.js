/*
	ajaxForSelect
	-------------

	Created By:   Cody Jassman
	Last Updated: July 25, 2013

	Populate a select box with an options array provided by the result of an ajax post.

	Example:

	ajaxForSelect({
		url:                  baseURL + 'ajax/select-options',
		postData:             { category_id: 1 },
		targetSelect:         '#select-option',
		optionValue:          'id',
		optionLabel:          'name',
		nullOption:           'Select an option',
		optionsToggleElement: '#select-option-area',
		callbackSuccess:      'callbackFunction()'
	});

	You may return a JSON array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionLabel
	or you can just return a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be shown
	if there are select options and hidden if there are none.
*/
function ajaxForSelect(settings) {
	if (settings.nullOption === undefined) settings.nullOption = "Select an option";
	if (settings.optionLabel === undefined) settings.optionLabel = settings.optionValue;

	return $.ajax({
		url: settings.url,
		type: 'post',
		data: settings.postData,
		dataType: 'json',
		success: function(data) {
			var currentValue = $(settings.targetSelect).val();
			if (settings.nullOption !== false) {
				$(settings.targetSelect).html('<option value="">'+settings.nullOption+'</option>');
			}
			for (c=0; c < data.length; c++) {
				if (settings.optionValue === undefined) {
					var option = '<option value="'+data[c]+'">'+data[c]+'</option>';
				} else {
					var option = '<option value="'+data[c][settings.optionValue]+'">'+data[c][settings.optionLabel]+'</option>';
				}
				$(settings.targetSelect).append(option);
			}
			$(settings.targetSelect).val(currentValue); //attempt to change to selected value (if it still exists)

			//show or hide an element depending on whether options are available in select box
			if (settings.optionsToggleElement !== undefined) {
				if (data.length > 0) {
					$(settings.optionsToggleElement).removeClass('hidden');
				} else {
					$(settings.optionsToggleElement).addClass('hidden');
				}
			}

			//success callback function
			if (settings.callbackSuccess != "" && settings.callbackSuccess != undefined) {
				eval(settings.callbackSuccess);
			}
		},
		error: function() {
			//error callback function
			if (settings.callbackError != "" && settings.callbackSuccess != undefined) {
				eval(settings.callbackError);
			}

			console.log('Ajax For Select Failed');
		}
	});
}

/*
	populateSelect
	--------------

	Created By:   Cody Jassman
	Last Updated: May 29, 2013

	Populate a select box with a supplied options array.

	Example:

	populateSelect({
		targetSelect:         '#select-option',
		options:              [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}],
		optionValue:          'id',
		optionLabel:          'name',
		nullOption:           'Select an option',
		optionsToggleElement: '#select-option-area'
	});

	You may use an array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionValue
	or you can just use a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be
	shown if there are select options and hidden if there are none.
*/
function populateSelect(settings) {
	if (settings.nullOption === undefined) settings.nullOption = "Select an option";
	if (settings.optionLabel === undefined) settings.optionLabel = settings.optionValue;

	var currentValue = $(settings.targetSelect).val();
	if (settings.nullOption !== false) {
		$(settings.targetSelect).html('<option value="">'+settings.nullOption+'</option>');
	}
	for (c=0; c < settings.options.length; c++) {
		if (settings.optionValue === undefined) {
			var option = '<option value="'+settings.options[c]+'">'+settings.options[c]+'</option>';
		} else {
			var option = '<option value="'+settings.options[c][settings.optionValue]+'">'+settings.options[c][settings.optionLabel]+'</option>';
		}
		$(settings.targetSelect).append(option);
	}
	$(settings.targetSelect).val(currentValue); //attempt to change to selected value (if it still exists)

	//show or hide an element depending on whether options are available in select box
	if (settings.optionsToggleElement !== undefined) {
		if (data.length > 0) {
			$(settings.optionsToggleElement).removeClass('hidden');
		} else {
			$(settings.optionsToggleElement).addClass('hidden');
		}
	}
}