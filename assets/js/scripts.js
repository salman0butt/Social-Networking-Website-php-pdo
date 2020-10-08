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

	//Button for profile post
	$('#submit_profile_post').click(function(){
		
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_submit_profile_post.php",
			data: $('form.profile_post').serialize(),
			success: function(msg) {
				$("#post_form").modal('hide');
				location.reload();
			},
			error: function(error) {
				console.log('Failure');
			}
		});

	});

});
	function getUsers(value, user) {
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_friend_search.php",
			data: {query: value, userLoggedIn:user },
			success: function(data) {
					$(".results").html(data);
			},
			error: function(error) {
				console.log('Failure');
			}
		});
	}


	
function getDropdownData(user, type) {

	if($(".dropdown_data_window").css("height") == "0px") {

		var pageName;

		if(type == 'notification') {

		}
		else if (type == 'message') {
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_message");
		}

		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName,
			type: "POST",
			data: "page=1&userLoggedIn=" + user,
			cache: false,

			success: function(response) {
				$(".dropdown_data_window").html(response);
				$(".dropdown_data_window").css({"padding" : "0px", "height": "280px", "border" : "1px solid #DADADA"});
				$("#dropdown_data_type").val(type);
			}

		});

	}
	else {
		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding" : "0px", "height": "0px", "border" : "none"});
	}

}