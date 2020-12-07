jQuery(document).ready(function($){

	jQuery('.breakingsnews-section').appendTo('.header-inner');

	$('.color-field').wpColorPicker();
 
	if( $('.set-exp-date').is(':checked') ) {
		$('.expiry-date').parent().parent().show();
	} else {
		$('.expiry-date').parent().parent().hide();
	}

	$('.set-exp-date').change( function() {

		if( $(this).is(':checked') ) {
			$('.expiry-date').parent().parent().show();
		} else {
			$('.expiry-date').parent().parent().hide();
			$('.expiry-date').val('');
		}
	});
});
