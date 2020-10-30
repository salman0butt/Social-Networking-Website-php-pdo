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
?>
    <script>
        function toggle() {
			let element = document.getElementById("comment_section");

			if(element.style.display == "block")
				element.style.display = "none";
			else
				element.style.display = "block";
		}
	</script>
    <?php
//Get id of post
if (isset($_GET['post_id'])) {
	$post_id = $_GET['post_id'];
}

$user_query = $pdo->prepare("SELECT added_by, user_to FROM posts WHERE id=:post_id");
$user_query->bindParam(':post_id', $post_id);
$user_query->execute();
$row = $user_query->fetch(PDO::FETCH_OBJ);

$posted_to = $row->added_by;
$user_to = $row->user_to;
$posted_by = '';
if (isset($_POST['postComment' . $post_id])) {
	$post_body = $_POST['post_body'];
	$post_body = strip_tags($post_body);
	$date_time_now = date("Y-m-d H:i:s");
	$insert_post = $pdo->prepare("INSERT INTO `comments`(`post_body`, `posted_by`, `posted_to`,`date_time_now`, `removed`, `post_id`) VALUES (:post_body,:posted_by, :posted_to,:date_time_now, :removed, :post_id)");
	$insert_post->bindValue(':post_body', $post_body);
	$insert_post->bindValue(':posted_by', $userLoggedIn);
	$insert_post->bindValue(':posted_to', $posted_to);
	$insert_post->bindValue(':date_time_now', $date_time_now);
	$insert_post->bindValue(':removed', 'no');
	$insert_post->bindValue(':post_id', $post_id);
	if ($insert_post->execute()) {
		echo "<p>Comment Posted! </p>";
		if ($posted_to != $userLoggedIn) {
			$notification = new Notification($pdo, $userLoggedIn);
			$notification->insertNotification($post_id, $posted_to, "comment");
		}
		if ($user_to != 'none' && $user_to != $userLoggedIn) {
			$notification = new Notification($pdo, $userLoggedIn);
			$notification->insertNotification($post_id, $user_to, "profile_comment");
		}

		$get_commenters = $pdo->prepare("SELECT * FROM comments WHERE post_id=:post_id");
		$get_commenters->bindValue(':post_id', $post_id);
		$get_commenters->execute();
		$notified_users = array();

		foreach($get_commenters->fetchAll(PDO::FETCH_OBJ) as $row) {
			if($row->posted_by != $posted_to && $row->posted_by != $user_to && $row->posted_by != $userLoggedIn && !in_array($row->posted_by, $notified_users)) {

				$notification = new Notification($pdo, $userLoggedIn);
				$notification->insertNotification($post_id, $row->posted_by, "comment_non_owner");

				array_push($notified_users, $row->posted_by);
			}

		}
	} else {
		echo "<p>Something went wrong!</p>";
	}

}
?>
    <form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">
        <textarea name="post_body"></textarea>
        <input type="submit" name="postComment<?php echo $post_id; ?>" value="Post">
    </form>
    <!-- Load comments -->
    <?php

$get_comments = $pdo->prepare("SELECT * FROM `comments` WHERE `post_id`=:post_id ORDER BY `id` DESC");
$get_comments->bindParam(':post_id', $post_id);
$get_comments->execute();
if ($get_comments->rowCount() > 0) {
	while ($comment = $get_comments->fetch(PDO::FETCH_OBJ)) {
		$comment_body = $comment->post_body;
		$posted_to = $comment->posted_to;
		$posted_by = $comment->posted_by;
		$date_added = $comment->date_time_now;
		$removed = $comment->removed;

		//Timeframe
		$date_time_now = date('Y-m-d H:i:s');
		$start_date = new DateTime($date_added); //time of the post
		$end_date = new DateTime($date_time_now); // current time
		$interval = $start_date->diff($end_date); // differnece between dates
		if ($interval->y >= 1) {
			if ($interval->y == 1) {
				$time_message = $interval->y . " year ago";
			} else {
				$time_message = $interval->y . " years ago";
			}
		} else if ($interval->m >= 1) {
			if ($interval->d == 0) {
				$days = "ago";
			} else if ($interval->d == 1) {
				$days = $interval->d . " day ago";
			} else {
				$days = $interval->d . " days ago";
			}
			if ($interval->m == 1) {
				$time_message = $interval->m . " month" . $days;
			} else {
				$time_message = $interval->m . " months" . $days;
			}
		} else if ($interval->d >= 1) {
			if ($interval->d == 1) {
				$time_message = "Yesterday";
			} else {
				$time_message = $interval->d . " days ago";
			}
		} else if ($interval->h >= 1) {
			if ($interval->h == 1) {
				$time_message = $interval->h . " hour ago";
			} else {
				$time_message = $interval->h . " hours ago";
			}
		} else if ($interval->i >= 1) {
			if ($interval->i == 1) {
				$time_message = $interval->i . " minute ago";
			} else {
				$time_message = $interval->i . " minutes ago";
			}
		} else {
			if ($interval->s < 30) {
				$time_message = "just now";
			} else {
				$time_message = $interval->s . " seconds ago";
			}

		}

		$user_obj = new User($pdo, $posted_by);
		?>
    <div class="comment_section">
        <a href="<?php echo $posted_by; ?>" target="_parent"><img src="<?php echo $user_obj->getProfilePic(); ?>" alt="profile pic" title="<?php echo $posted_by; ?>" style="float:left;height:30px;"></a>
        <a href="<?php echo $posted_by; ?>" target="_parent"></><b>
                <?php echo $user_obj->getFirstAndLastName();?></b></a>
        &emsp;
        <?php echo $time_message . "<br>" .$comment_body; ?>
        <hr>
    </div>
    <?php

	}

}else {
		echo '<center><br>No Comments to Show!</center>';
}

?>
</body>

</html>