<?php
require_once 'config/config.php';
require_once 'includes/form_handlers/register_handler.php';
require_once 'includes/form_handlers/login_handler.php';

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Social</title>
    <link rel="stylesheet" href="assets/css/register.css">
   
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

    <script src="./assets/js/scripts.js"></script>
</head>

<body>
	<?php 
		if (isset($_POST['register_button'])) {
			echo '<script>
			$(document).ready(function() {
			$("#first").hide();
			$("#second").show();
			});
			});
			</script>';
		}

	 ?>
    <div class="wrapper">
        <div class="login_box col-12">
            <!-- login form -->
            <div class="login_header">
                <h1>Social!</h1>
                Login or Signup below
            </div><br>
            <div class="pl-5 pr-5">
            	<div id="first">
                <form action="register.php" method="POST">
                    <?=csrf_field();?>
                    <input type="email" name="login_email" class="form-control" placeholder="Email Address"><br />
                    <input type="password" name="login_password" class="form-control" placeholder="password"><br />
                    <?php if (in_array("Email or Password is Incorrect", $error_array)) {
	echo "Email or Password is Incorrect<br>";
}
?>
                    <input type="submit" class="btn btn-success" name="login_button" value="Login"><br>
                    <a href="javascript:void(0)" id="signup" class="signup">Need an account? Register here</a>
                </form>
                </div>
                <br>
                <div id="second">
                <form action="register.php" method="POST">
                    <?php csrf_field();?>
                    <input type="text" name="reg_fname" class="form-control" placeholder="First Name" value="<?php if (isset($_SESSION['reg_fname'])) {
	echo $_SESSION['reg_fname'];
}
?>" required><br />
                    <?php if (in_array("Your first name must be between 2 and 25 characters", $error_array)) {
	echo "Your first name must be between 2 and 25 characters<br>";
}
?>
                    <input type="text" name="reg_lname" class="form-control" placeholder="last Name" value="<?php if (isset($_SESSION['reg_lname'])) {
	echo $_SESSION['reg_lname'];
}
?>" required><br />
                    <?php if (in_array("Your last name must be between 2 and 25 characters", $error_array)) {
	echo "Your last name must be between 2 and 25 characters<br>";
}
?>
                    <input type="email" name="reg_email" class="form-control" placeholder="Email" value="<?php if (isset($_SESSION['reg_email'])) {
	echo $_SESSION['reg_email'];
}
?>" required><br />
                    <?php
if (in_array("email already exists!", $error_array)) {
	echo "email already exists!<br>";
} else if (in_array("Invalid Emal Formate", $error_array)) {
	echo "Invalid Emal Formate<br>";
} else if (in_array("Email don't not match!", $error_array)) {
	echo "Email don't not match!<br>";
}
?>
                    <input type="email" name="reg_confirm_email" class="form-control" placeholder="Confirm Email" value="<?php if (isset($_SESSION['reg_confirm_email'])) {
	echo $_SESSION['reg_confirm_email'];
}
?>" required><br />
                    <input type="password" name="reg_password" class="form-control" placeholder="Password" required><br />
                    <input type="password" name="reg_confirm_password" class="form-control" placeholder="Confirm Password" required><br />
                    <?php
if (in_array("Your password must be between 5 and 30 characters", $error_array)) {
	echo "Your password must be between 5 and 30 characters<br>";
} else if (in_array("Your Password can only contain english characters or numbers", $error_array)) {
	echo "Your Password can only contain english characters or numbers<br>";
} else if (in_array("You password do not match", $error_array)) {
	echo "You password do not match<br>";

} else if (in_array("<span>You're all Set!.Go Ahead and login</span>", $error_array)) {
	echo "<span style='color:#14C800;'>You're all Set!.Go Ahead and login</span><br>";
}
?>
                    <input type="submit" class="btn btn-info" name="register_button" value="Register"><br>
                    <a href="javascript:void(0)" id="signin" class="signin">Already have an account? Sign in here</a>
                </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$_SESSION['reg_fname'] = "";
$_SESSION['reg_lname'] = "";
$_SESSION['reg_email'] = "";
$_SESSION['reg_confirm_email'] = "";
?>