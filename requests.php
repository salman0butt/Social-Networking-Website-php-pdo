<?php
include("includes/header.php"); //Header 
?>


<div class="user_details column">
    <a href="#">
        <img src="<?php echo $user->profile_pic; ?>" alt="profile image">
    </a>
    <div class="user_details_left_right">
        <a href="<?php echo $userLoggedIn; ?>">
            <?php
echo $user->first_name . " " . $user->last_name;
?>
        </a><br />
        <?php echo 'Posts: ' . ' ' . $user->num_posts . '<br/>';
echo 'Likes: ' . ' ' . $user->num_likes;
?>
    </div>
</div>
<div class="main_column column" id="main_column">

	<h4>Friend Requests</h4>

	<?php  
		$query = $pdo->prepare("SELECT * FROM friend_requests WHERE user_to=:userLoggedIn");
		$query->bindParam(':userLoggedIn', $userLoggedIn);
		 $query->execute();
	
	if($query->rowCount() == 0)
		echo "You have no friend requests at this time!";
	else {

		while($row = $query->fetch(PDO::FETCH_OBJ)) {
			$user_from = $row->user_from;
			$user_from_obj = new User($pdo, $user_from);

			 echo '<img src="'.$user_from_obj->getRequestProfilePic($user_from).'" alt="img" style="width:80px;margin-right:10px;" class="rounded-circle">';
			echo $user_from_obj->getFirstAndLastName() . " sent you a friend request!";


			$user_from_friend_array = $user_from_obj->getFriendArray();

			if(isset($_POST['accept_request' . $user_from ])) {
					$add_friend_query = $pdo->prepare("UPDATE users SET friend_array=CONCAT(friend_array, :user_from) WHERE username= :userLoggedIn");
					$add_friend_query->bindValue(':user_from', $user_from.',');
					$add_friend_query->bindParam(':userLoggedIn', $userLoggedIn);
					$add_friend_query->execute();

					$add_friend_query = $pdo->prepare("UPDATE users SET friend_array=CONCAT(friend_array, :userLoggedIn) WHERE username=:user_from");
					$add_friend_query->bindParam(':userLoggedIn', $userLoggedIn);
					$add_friend_query->bindParam(':user_from', $user_from);
					$add_friend_query->execute();
			
					$delete_query = $pdo->prepare("DELETE FROM friend_requests WHERE user_to=:userLoggedIn AND user_from=:user_from");
					$delete_query->bindParam(':userLoggedIn', $userLoggedIn);
					$delete_query->bindParam(':user_from', $user_from);
					$delete_query->execute();

				echo "You are now friends!";
				header("Location: requests.php");
			}

			if(isset($_POST['ignore_request' . $user_from ])) {
					$delete_query = $pdo->prepare("DELETE FROM friend_requests WHERE user_to=:userLoggedIn AND user_from=:user_from");
					$delete_query->bindParam(':userLoggedIn', $userLoggedIn);
					$delete_query->bindParam(':user_from', $user_from);
					$delete_query->execute();

				echo "Request ignored!";
				header("Location: requests.php");
			}

			?>
			<form action="requests.php" method="POST">
				<input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
				<input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">
			</form>
			<?php

	echo '<hr>';
		}

	}

	?>


</div>