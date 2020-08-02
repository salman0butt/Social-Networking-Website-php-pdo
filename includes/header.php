<?php 
	require_once 'config/config.php';

	if (isset($_SESSION['username'])) {
		$userLoggedIn = $_SESSION['username'];
		$user_details_query = $pdo->prepare("SELECT * FROM `users` WHERE `username`=:username");
		$user_details_query->bindParam(':username',$userLoggedIn);
		 $user_details_query->execute();
		$user = $user_details_query->fetch(PDO::FETCH_OBJ);
		if (!$user_details_query->rowCount() > 0) {
			echo '<script>alert("First name not found");</script>';
		}
	}else {
		header('Location: register.php');
	}
 ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Social</title>
    <!-- styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
    <!-- scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body>
    <div class="top-bar">

        <div class="logo">
            <a href="index.php">Social!</a>
        </div>
        <nav class="nav">
        	<a href="#">
              <?php echo $user->first_name; ?>
            </a>
            <a href="#">
                <i class="fas fa-home"></i>
            </a>
            <a href="#">
                <i class="fas fa-envelope"></i>
            </a>
            <a href="#">
               <i class="fas fa-bell"></i>
            </a>
            <a href="#">
              <i class="fas fa-users"></i>
            </a>
            <a href="#">
               <i class="fas fa-cog"></i>
            </a>
              <a href="includes/handlers/logout.php">
               <i class="fas fa-sign-out-alt"></i>
            </a>
        </nav>

    </div>
    <div class="wrapper">
    	