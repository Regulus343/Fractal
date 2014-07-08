$(document).ready(function(){

	$('#username').keyup(function(){
		$('#username').val($('#username').val().replace(/ /g, '-'));
	});

	$('#country').change(function(){
		if ($(this).val() == "Canada") {
			$('#region-area').removeClass('hidden');
			$('label[for="region"]').text('Province');

		} else if ($(this).val() == "United States") {
			$('#region-area').removeClass('hidden');
			$('label[for="region"]').text('State');
		} else {
			$('#region-area').addClass('hidden');
			$('label[for="region"]').text('Region');
		}
	});

	$('#password').val(''); //prevent browser from automatically inserting a password

	$('#password, #password-confirmation').change(function(){
		checkPasswords();
	}).keyup(function(){
		checkPasswords();
	});

});

function checkPasswords() {
	var password             = $('#password').val();
	var passwordConfirmation = $('#password-confirmation').val();

	if (password != "") {
		if (password == passwordConfirmation && password.length >= minimumPasswordLength) {
			$('.passwords-check .passwords-mismatch').addClass('hidden');
			$('.passwords-check .passwords-match').removeClass('hidden');
		} else {
			$('.passwords-check .passwords-match').addClass('hidden');
			$('.passwords-check .passwords-mismatch').removeClass('hidden');
		}
	} else {
		$('.passwords-check span').addClass('hidden');
	}
}