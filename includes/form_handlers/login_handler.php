<?php 
	if (isset($_POST['login_button'])) {

		$email = filter_var($_POST['login_email'],FILTER_SANITIZE_EMAIL);
		$_SESSION['lgoin_email'] = $email;
		$password = md5($_POST['login_password']);

		
		$check_database_query = $pdo->prepare("SELECT * FROM `users` WHERE `email`=:email AND `password` = :password");
		$check_database_query->bindParam(':email',$email);
		$check_database_query->bindParam(':password',$password);
		  $check_database_query->execute();
		  $obj = $check_database_query->fetch(PDO::FETCH_OBJ);

		  //checking account is closed
		$check_user_account = $pdo->prepare("SELECT * FROM `users` WHERE `email`=:email AND `user_closed` = :user_closed");
		$check_user_account->bindParam(':email',$email);
		$check_user_account->bindValue(':user_closed','yes');
		  $check_user_account->execute();

		  //reopning closed account
		$check_user_account = $pdo->prepare("UPDATE `users` SET `user_closed`=:user_closed WHERE `email`=:email");
		$check_user_account->bindParam(':email',$email);
		$check_user_account->bindValue(':user_closed','no');
		  $check_user_account->execute();

		if ($check_database_query->rowCount() > 0) {
			$username = $obj->username;

			$_SESSION['username'] = $username;
			header('Location: index.php');
		}else {
			array_push($error_array,"Email or Password is Incorrect");
		}

	}
	

 ?>