<?php
//Declare variable to prevent errors
$fname = "";
$lname = "";
$email = "";
$confirm_email = "";
$password = '';
$confirm_password = "";
$date = "";
$error_array = array();

if (isset($_POST['register_button']) && isset($_POST['_token']) && token_check($_POST['_token'])) {
	//Registration from values
	
	$fname = strip_tags($_POST['reg_fname']);
	$fname = str_replace(' ', '', $fname);
	$fname = ucfirst(strtolower($fname));
	$_SESSION['reg_fname'] = $fname;

	$lname = strip_tags($_POST['reg_lname']);
	$lname = str_replace(' ', '', $lname);
	$lname = ucfirst(strtolower($lname));
	$_SESSION['reg_lname'] = $lname;

	$email = strip_tags($_POST['reg_email']);
	$email = str_replace(' ', '', $email);
	$email = strtolower($email);
	$_SESSION['reg_email'] = $email;

	$confirm_email = strip_tags($_POST['reg_confirm_email']);
	$confirm_email = str_replace(' ', '', $confirm_email);
	$confirm_email = ($confirm_email);
	$_SESSION['reg_confirm_email'] = $confirm_email;

	$password = strip_tags($_POST['reg_password']);


	$confirm_password = strip_tags($_POST['reg_confirm_password']);


	$date = date('Y-m-d');

	if ($email == $confirm_email) {
		//check email if its in validate formate
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			$check_email_query = "SELECT * FROM `users` WHERE `email` = :email";
			$check_email = $pdo->prepare($check_email_query);
			$check_email->bindParam(':email', $email);
			$check_email->execute();
			if ($check_email->rowCount() > 0) {
				array_push($error_array, "email already exists!");
			}
		} else {
			array_push($error_array, "Invalid Emal Formate");
		}
	} else {
		array_push($error_array, "Email don't not match!");
	}

	if (strlen($fname) > 25 && strlen($fname) < 2) {
		array_push($error_array, "Your first name must be between 2 and 25 characters");
	}
	if (strlen($lname) > 25 && strlen($lname) < 2) {
		array_push($error_array, "Your last name must be between 2 and 25 characters");
	}
	if (strlen($password) > 30 && strlen($password) < 5) {
		array_push($error_array, "Your password must be between 5 and 30 characters");
	}
	if ($password != $confirm_password) {
		array_push($error_array, "You password do not match");
	} else {
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			array_push($error_array, "Your Password can only contain english characters or numbers");
		}
	}

	if (empty($error_array)) {
		$password = md5($password); //encrypting password

		//Genarte Username By concatenateing first name and last name
		$username = strtolower($fname . '_' . $lname);

		$check_username = $pdo->prepare("SELECT * FROM `users` WHERE `username`=:username");
		$check_username->bindParam(':username', $username);
		$check_username->execute();

		$i = 0;
		while ($check_username->rowCount()) {
			$i++;
			$username = $username . "_" . $i;
			$check_username_query = $pdo("SELECT * FROM `users` WHERE `username`=:username");
			$check_username_query->bindParam(':username', $username);
			$check_username_query->execute();
		}
		//profile picture assignment
		$rand = rand(1, 2);

		if ($rand == 1) {
			$profile_pic = "assets/images/profile_pics/defaults/head_alizarin.png";
		} else if ($rand == 2) {
			$profile_pic = "assets/images/profile_pics/defaults/head_deap_blue.png";
		}
		try {
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->beginTransaction();

			$insert_query = $pdo->prepare("INSERT INTO `users`(`first_name`, `last_name`, `username`, `email`, `password`, `signup_date`, `profile_pic`, `num_posts`, `num_likes`, `user_closed`, `friend_array`) VALUES (:fname, :lname, :username, :email, :password, :signup_date, :profile_pic, :num_posts, :num_likes, :user_closed, :friend_array)");
			$insert_query->bindValue(':fname', $fname);
			$insert_query->bindValue(':lname', $lname);
			$insert_query->bindValue(':username', $username);
			$insert_query->bindValue(':email', $email);
			$insert_query->bindValue(':password', $password);
			$insert_query->bindValue(':signup_date', $date);
			$insert_query->bindValue(':profile_pic', $profile_pic);
			$insert_query->bindValue(':num_posts', '0');
			$insert_query->bindValue(':num_likes', '0');
			$insert_query->bindValue(':user_closed', 'no');
			$insert_query->bindValue(':friend_array', ' ');
			$insert_query->execute();
			 //$insert_query->debugDumpParams();
			$pdo->commit();
			array_push($error_array,"<span>You're all Set!.Go Ahead and login</span>");


		} catch (Exception $e) {
			echo $e->getMessage();
			$pdo->rollback();
		}

	}

}


?>