<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Social</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
    	*{
			font-size: 12px;
			font-family: Arial, Helvetica, sans-serif;
		}
    	body {
    		background-color: #fff;
    	}
    	.newsFeedPostOptions form {
    		position: absolute;
    		top: 0;
    	}
    </style>
</head>

<body>
    <?php
require 'config/config.php';
include "includes/classes/User.php";
include "includes/classes/Post.php";
include "includes/classes/Notification.php";

if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = $pdo->prepare("SELECT * FROM `users` WHERE `username`=:username");
	$user_details_query->bindParam(':username', $userLoggedIn);
	$user_details_query->execute();
	$user = $user_details_query->fetch(PDO::FETCH_OBJ);
	if (!$user_details_query->rowCount() > 0) {
		echo '<script>alert("First name not found");</script>';
	}
} else {
	header('Location: register.php');
}

//Get id of post
if (isset($_GET['post_id'])) {
	$post_id = $_GET['post_id'];
}

$get_likes = $pdo->prepare("SELECT likes, added_by FROM posts WHERE id=:post_id");
$get_likes->bindParam(':post_id', $post_id);
$get_likes->execute();
$row = $get_likes->fetch(PDO::FETCH_OBJ);

$total_likes = $row->likes;
$user_liked = $row->added_by;

//get user details
$user_details = $pdo->prepare("SELECT * FROM users WHERE username=:username");
$user_details->bindParam(':username', $user_liked);
$user_details->execute();
$row = $user_details->fetch(PDO::FETCH_OBJ);
$total_user_likes = $row->num_likes;


//Like button
if (isset($_POST['like_button'])) {
	//total Posts likes
	try {
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$pdo->beginTransaction();
		$total_likes++;

		$posts_likes = $pdo->prepare("UPDATE `posts` SET `likes`=:total_likes WHERE `id`=:id");
		$posts_likes->bindParam(':total_likes', $total_likes);
		$posts_likes->bindParam(':id', $post_id);
		$posts_likes->execute();



		$total_user_likes++;

		//total User likes
		$user_likes = $pdo->prepare("UPDATE `users` SET `num_likes`=:total_user_likes WHERE `username`=:user_liked");
		$user_likes->bindParam(':total_user_likes', $total_user_likes);
		$user_likes->bindParam(':user_liked', $user_liked);
		$user_likes->execute();
		//insert user likes

		$insert_user_likes = $pdo->prepare("INSERT INTO `likes`(`username`, `post_id`) VALUES (:username, :post_id)");
		$insert_user_likes->bindParam(':username', $userLoggedIn);
		$insert_user_likes->bindParam(':post_id', $post_id);
		$insert_user_likes->execute();


		$pdo->commit();

	} catch (\Exception $e) {
		$pdo->rollBack();
		echo "Failed: " . $e->getMessage();
	}

	//Insert notification
		if ($user_liked != $userLoggedIn) {
			$notification = new Notification($pdo, $userLoggedIn);
			$notification->insertNotification($post_id, $user_liked, "like");
		}
}

//Unlike Button
if (isset($_POST['unlike_button'])) {
	try {
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$pdo->beginTransaction();
		// total Posts likes
		$total_likes--;
		$posts_likes = $pdo->prepare("UPDATE `posts` SET `likes`=:total_likes WHERE `id`=:id");
		$posts_likes->bindParam(':total_likes', $total_likes);
		$posts_likes->bindParam(':id', $post_id);
		$posts_likes->execute();
		$total_user_likes--;

		//total User likes
		$user_likes = $pdo->prepare("UPDATE `users` SET `num_likes`=:total_user_likes WHERE `username`=:user_liked");
		$user_likes->bindParam(':total_user_likes', $total_user_likes);
		$user_likes->bindParam(':user_liked', $user_liked);
		$user_likes->execute();

		//insert user likes
		$del_user_likes = $pdo->prepare("DELETE FROM `likes` WHERE `username`=:userLoggedIn AND `post_id`=:post_id");
		$del_user_likes->bindParam(':userLoggedIn', $userLoggedIn);
		$del_user_likes->bindParam(':post_id', $post_id);
		$del_user_likes->execute();
		$pdo->commit();

	} catch (Exception $e) {
		$pdo->rollBack();
		echo "Failed: " . $e->getMessage();
	}

	//Insert notification
}

//Check for previous likes
$check_previous_like = $pdo->prepare("SELECT * FROM likes WHERE username=:username AND post_id=:post_id");
$check_previous_like->bindParam(':username', $userLoggedIn);
$check_previous_like->bindParam(':post_id', $post_id);
$check_previous_like->execute();

if ($check_previous_like->rowCount()) {
	echo '<form action="like.php?post_id=' . $post_id . '" method="POST">
		<input type="submit" class="comment_like" name="unlike_button" value="UnLike">
		<div class="like_value">
			' . $total_likes . ' Likes
		</div>

		</form>';
} else {
	echo '<form action="like.php?post_id=' . $post_id . '" method="POST">
		<input type="submit" class="comment_like" name="like_button" value="Like">
		<div class="like_value">
			' . $total_likes . ' Likes
		</div>

		</form>';
}

?>
</body>

</html>