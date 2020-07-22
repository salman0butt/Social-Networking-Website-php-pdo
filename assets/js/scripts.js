$(document).ready(function() {
	//on click signup, hide login and show reg page
	$('#signup').click(function(){
		$('#first').slideUp('slow',function() {
			$('#second').slideDown('slow');
		});
	});
		//on click signin, hide reg and show login page
		$('#signin').click(function(){
		$('#second').slideUp('slow',function() {
			$('#first').slideDown('slow');
		});
	});
});