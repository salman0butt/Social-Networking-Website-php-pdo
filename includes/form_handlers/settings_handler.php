<?php  
if(isset($_POST['update_details'])) {

	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	$username = $_POST['username'];


	$email_check = $pdo->prepare("SELECT * FROM users WHERE username=:username");
	$email_check->bindValue(':username', $username);
	$email_check->execute();	
	$row = $email_check->fetch(PDO::FETCH_ASSOC);

	$matched_user = $row['username'];


	if($matched_user && $matched_user == $userLoggedIn) {
		$message = "Details updated!<br><br>";

		$email_check = $pdo->prepare("UPDATE users SET first_name=:first_name, last_name=:last_name, email=:email WHERE username=:userLoggedIn");
		$email_check->bindValue(':first_name', $first_name);
		$email_check->bindValue(':last_name', $last_name);
		$email_check->bindValue(':email', $email);
		$email_check->bindValue(':userLoggedIn', $userLoggedIn);
		$email_check->execute();

	}
	else 
		$message = "That email is already in use!<br><br>";
}
else 
	$message = "";


//**************************************************

if(isset($_POST['update_password'])) {

	$old_password = strip_tags($_POST['old_password']);
	$new_password_1 = strip_tags($_POST['new_password_1']);
	$new_password_2 = strip_tags($_POST['new_password_2']);

	$password_query = $pdo->prepare("SELECT password FROM users WHERE username=:userLoggedIn");
	$password_query->bindValue(':userLoggedIn', $userLoggedIn);
	$password_query->execute();
	$row = $password_query->fetch(PDO::FETCH_ASSOC);
	$db_password = $row['password'];

	if(md5($old_password) == $db_password) {

		if($new_password_1 == $new_password_2) {


			if(strlen($new_password_1) <= 4) {
				$password_message = "Sorry, your password must be greater than 4 characters<br><br>";
			}	
			else {
				$new_password_md5 = md5($new_password_1);
				$password_query = $pdo->prepare("UPDATE users SET password=:new_password_md5 WHERE username=:userLoggedIn");
				$password_query->bindValue(':new_password_md5', $new_password_md5);
				$password_query->bindValue(':userLoggedIn', $userLoggedIn);
				$password_query->execute();
				$password_message = "Password has been changed!<br><br>";
			}


		}
		else {
			$password_message = "Your two new passwords need to match!<br><br>";
		}

	}
	else {
			$password_message = "The old password is incorrect! <br><br>";
	}

}
else {
	$password_message = "";
}


if(isset($_POST['close_account'])) {
	header("Location: close_account.php");
}


?>