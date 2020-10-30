<?php
/**
 * Post Class
 */
class Post {
	private $user_obj;
	private $pdo;

	public function __construct($pdo, $user) {
		$this->pdo = $pdo;
		$this->user_obj = new User($pdo, $user);
	}

	public function submitPost($body, $user_to) {
		$body = strip_tags($body);
		$body = htmlspecialchars($body);

		$check_empty = preg_replace('/\s+/', '', $body);

		if ($check_empty != "") {
			try {
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				$this->pdo->beginTransaction();

				//Current Date
				$date_added = date('Y-m-d H:i:s');

				//Get Username
				$added_by = $this->user_obj->getUserName();

				//if user is on profile set added by to none
				if ($user_to == $added_by) {
					$user_to = "none";
				}

				//insert Post
				$add_post_query = $this->pdo->prepare("INSERT INTO `posts`(`body`, `added_by`, `user_to`, `date_added`, `user_closed`, `deleted`, `likes`) VALUES (:body, :added_by, :user_to, :date_added, :user_closed, :deleted, :likes)");
				$add_post_query->bindValue(':body', $body);
				$add_post_query->bindValue(':added_by', $added_by);
				$add_post_query->bindValue(':user_to', $user_to);
				$add_post_query->bindValue(':date_added', $date_added);
				$add_post_query->bindValue(':user_closed', 'no');
				$add_post_query->bindValue(':deleted', 'no');
				$add_post_query->bindValue(':likes', '0');
				$add_post_query->execute();

				$returned_id = $this->pdo->lastInsertId();

				//Insert Notification
				if ($user_to != 'none') {
					$notification = new Notification($this->pdo, $added_by);
					$notification->insertNotification($returned_id, $user_to, "profile_post");
				}

				//Update Post count for user

				$num_posts = $this->user_obj->getNumPosts();
				$num_posts++;
				$update_query = $this->pdo->prepare("UPDATE `users` SET `num_posts`=:num_posts WHERE `username`=:added_by");
				$update_query->bindValue(':num_posts', $num_posts);
				$update_query->bindValue(':added_by', $added_by);
				$update_query->execute();
				$this->pdo->commit();

			} catch (Exception $e) {
				$this->pdo->rollBack();
				echo "Failed: " . $e->getMessage();
			}

		}

	}

	public function loadPostsFriends($data, $limit) {

		$page = $data['page'];

		$userLoggedIn = $this->user_obj->getUserName();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) + $limit;
		}


		$str = "";
		$data = $this->pdo->prepare("SELECT * FROM `posts` WHERE `deleted` = :deleted ORDER BY `id` DESC");
		$data->bindValue(':deleted', 'no');
		$data->execute();
		if ($data->rowCount() > 0) {

			$num_iterations = 0; //Numbers of resultes checked
			$count = 1;

			while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];

			if($userLoggedIn == $added_by)
				$delete_button = "<button class='delete_button' id='post$id'>&times;</button>";
			else 
				$delete_button = "";

				if ($row['user_to'] == 'none') {
					$user_to = "";
				} else {
					$user_to_obj = new User($this->pdo, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
				}

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->pdo, $added_by);
				if ($added_by_obj->isClosed()) {
					continue;
				}
				$user_logged_obj = new User($this->pdo, $userLoggedIn);
				if ($user_logged_obj->isFriend($added_by)) {

					if ($num_iterations++ < $start) {
						continue;
					}
					//once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					$user_details_query = $this->pdo->prepare("SELECT `first_name`, `last_name`, `profile_pic` FROM `users` WHERE `username` = :added_by");
					$user_details_query->bindParam(':added_by', $added_by);
					$user_details_query->execute();
					$row = $user_details_query->fetch(PDO::FETCH_OBJ);

					$first_name = $row->first_name;
					$last_name = $row->last_name;
					$profile_pic = $row->profile_pic;

					?>
<script>
function toggle<?php echo $id;?>() {
    let target = $(event.target);
    if (!target.is("a")) {
        let element = document.getElementById("toggleComment<?php echo $id; ?>");

        if (element.style.display == "block")
            element.style.display = "none";
        else
            element.style.display = "block";
    }
}
</script>
<?php

						$comments_check = $this->pdo->prepare("SELECT * FROM `comments` WHERE `post_id` = :id");
						$comments_check->bindParam(':id', $id);
						$comments_check->execute();
						$comment_count = $comments_check->rowCount();

					//Timeframe
					$date_time_now = date('Y-m-d H:i:s');
					$start_date = new DateTime($date_time); //time of the post
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
							$days = " ago";
						} else if ($interval->d == 1) {
							$days = $interval->d . " day ago";
						} else {
							$days = $interval->d . " days ago";
						}
						if ($interval->m == 1) {
							$time_message = $interval->m . " month " . $days;
						} else {
							$time_message = $interval->m . " months " . $days;
						}
					} else if ($interval->d >= 1) {
						if ($interval->d == 1) {
							$time_message = " Yesterday";
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
					$str .= "<div class='status_post'>
				<div class='post_profile_pic'>
					<img src='$profile_pic' class='rounded-circle' alt='profile pic' width='50'>
				</div>
				<div class='posted_by' style='#ACACAC'>
				<a href='$added_by'>$first_name $last_name</a> $user_to &emsp; $time_message 
				$delete_button
				</div>
					<div id='post_body'>
						$body
						<br/>
						<br/>
						<br/>
					</div>
					<div class='newsFeedPostOptions' onClick='javascript:toggle$id()'>
						Comments ($comment_count) &emsp;
						<iframe src='like.php?post_id=$id' scrolling='no' id='like-btn'></iframe>
					</div>
				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
					 <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";
				}
				?>
<script>
$(document).ready(function() {

    $('#post<?php echo $id; ?>').on('click', function() {
        bootbox.confirm("Are you sure you want to delete this post?", function(result) {

            $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", { result: result,post_id: <?php echo $id; ?> });

            if (result)
                location.reload();

        });
    });


});
</script>
<?php

			} //End while loop

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
					<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> no more posts to show! </p>";
			}
		}
		echo $str;
	}
	public function loadProfilePosts($data, $limit) {

		$page = $data['page'];
		$profileUser = $data['profileUsername'] ?? '';

		$userLoggedIn = $this->user_obj->getUserName();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) + $limit;
		}


		$str = "";
		$data = $this->pdo->prepare("SELECT * FROM `posts` WHERE `deleted` = :deleted AND ((added_by=:profileUser AND user_to=:user_to) OR user_to=:profileUser) ORDER BY `id` DESC");
		$data->bindValue(':deleted', 'no');
		$data->bindValue(':profileUser', $profileUser);
		$data->bindValue(':user_to', 'none');
		$data->execute();
		if ($data->rowCount() > 0) {

			$num_iterations = 0; //Numbers of resultes checked
			$count = 1;

			while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];

				if ($row['user_to'] == 'none') {
					$user_to = "";
				} else {
					$user_to_obj = new User($this->pdo, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
				}

			if($userLoggedIn == $added_by)
				$delete_button = "<button class='delete_button' id='post$id'>&times;</button>";
			else 
				$delete_button = "";

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->pdo, $added_by);
				if ($added_by_obj->isClosed()) {
					continue;
				}
				$user_logged_obj = new User($this->pdo, $userLoggedIn);
				if ($user_logged_obj->isFriend($added_by)) {

					if ($num_iterations++ < $start) {
						continue;
					}
					//once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					$user_details_query = $this->pdo->prepare("SELECT `first_name`, `last_name`, `profile_pic` FROM `users` WHERE `username` = :added_by");
					$user_details_query->bindParam(':added_by', $added_by);
					$user_details_query->execute();
					$row = $user_details_query->fetch(PDO::FETCH_OBJ);

					$first_name = $row->first_name;
					$last_name = $row->last_name;
					$profile_pic = $row->profile_pic;

					?>
<script>
function toggle<?php echo $id;?>() {
    let target = $(event.target);
    if (!target.is("a")) {
        let element = document.getElementById("toggleComment<?php echo $id; ?>");

        if (element.style.display == "block")
            element.style.display = "none";
        else
            element.style.display = "block";
    }
}
</script>
<?php

						$comments_check = $this->pdo->prepare("SELECT * FROM `comments` WHERE `post_id` = :id");
						$comments_check->bindParam(':id', $id);
						$comments_check->execute();
						$comment_count = $comments_check->rowCount();

					//Timeframe
					$date_time_now = date('Y-m-d H:i:s');
					$start_date = new DateTime($date_time); //time of the post
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
							$days = " ago";
						} else if ($interval->d == 1) {
							$days = $interval->d . " day ago";
						} else {
							$days = $interval->d . " days ago";
						}
						if ($interval->m == 1) {
							$time_message = $interval->m . " month " . $days;
						} else {
							$time_message = $interval->m . " months " . $days;
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
					$str .= "<div class='status_post'>
				<div class='post_profile_pic'>
					<img src='$profile_pic' class='rounded-circle' alt='profile pic' width='50'>
				</div>
				<div class='posted_by' style='#ACACAC'>
				<a href='$added_by'>$first_name $last_name</a> $user_to &emsp; $time_message 
				$delete_button
				</div>
					<div id='post_body'>
						$body
						<br/>
						<br/>
						<br/>
					</div>
					<div class='newsFeedPostOptions' onClick='javascript:toggle$id()'>
						Comments ($comment_count) &emsp;
						<iframe src='like.php?post_id=$id' scrolling='no' id='like-btn'></iframe>
					</div>
				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
					 <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";
				}
				?>
<script>
$(document).ready(function() {

    $('#post<?php echo $id; ?>').on('click', function() {
        bootbox.confirm("Are you sure you want to delete this post?", function(result) {

            $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", { result: result,post_id: <?php echo $id; ?>});

            if (result)
                location.reload();

        });
    });


});
</script>
<?php

			} //End while loop

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
					<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> no more posts to show! </p>";
			}
		}
		echo $str;
	}

	public function getSinglePost($post_id) {

		$userLoggedIn = $this->user_obj->getUsername();

		$opened_query = $this->pdo->prepare("UPDATE notifications SET opened=:opened WHERE user_to=:userLoggedIn AND link LIKE :link");
		$opened_query->bindValue(':opened', 'yes');
		$opened_query->bindValue(':userLoggedIn', $userLoggedIn);
		$opened_query->bindValue(':link', '%=$post_id');
		$opened_query->execute();

		$str = ""; //String to return 
		$data_query = $this->pdo->prepare("SELECT * FROM posts WHERE deleted=:deleted AND id=:post_id");
		$data_query->bindValue(':deleted', 'no');
		$data_query->bindValue(':post_id', $post_id);
		$data_query->execute();

		if($data_query->rowCount() > 0) {


			$row = $data_query->fetch(PDO::FETCH_ASSOC); 
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];

				//Prepare user_to string so it can be included even if not posted to a user
				if($row['user_to'] == "none") {
					$user_to = "";
				}
				else {
					$user_to_obj = new User($this->pdo, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['user_to'] ."'>" . $user_to_name . "</a>";
				}

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->pdo, $added_by);
				if($added_by_obj->isClosed()) {
					return;
				}

				$user_logged_obj = new User($this->pdo, $userLoggedIn);
				if($user_logged_obj->isFriend($added_by)){


					if($userLoggedIn == $added_by)
						$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
					else 
						$delete_button = "";


					$user_details_query = $this->pdo->prepare("SELECT first_name, last_name, profile_pic FROM users WHERE username=:added_by");
					$user_details_query->bindValue(':added_by', $added_by);
					$user_details_query->execute();


					$user_row = $user_details_query->fetch(PDO::FETCH_ASSOC);
					$first_name = $user_row['first_name'];
					$last_name = $user_row['last_name'];
					$profile_pic = $user_row['profile_pic'];


					?>
					<script> 
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block") 
									element.style.display = "none";
								else 
									element.style.display = "block";
							}
						}

					</script>
					<?php

					$comments_check = $this->pdo->prepare("SELECT * FROM comments WHERE post_id=:post_id");
					$comments_check->bindValue(':post_id', $post_id);
					$comments_check->execute();
					$comment_count = $comments_check->rowCount();


					//Timeframe
					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates 
					if($interval->y >= 1) {
						if($interval == 1)
							$time_message = $interval->y . " year ago"; //1 year ago
						else 
							$time_message = $interval->y . " years ago"; //1+ year ago
					}
					else if ($interval->m >= 1) {
						if($interval->d == 0) {
							$days = " ago";
						}
						else if($interval->d == 1) {
							$days = $interval->d . " day ago";
						}
						else {
							$days = $interval->d . " days ago";
						}


						if($interval->m == 1) {
							$time_message = $interval->m . " month ". $days;
						}
						else {
							$time_message = $interval->m . " months ". $days;
						}

					}
					else if($interval->d >= 1) {
						if($interval->d == 1) {
							$time_message = "Yesterday";
						}
						else {
							$time_message = $interval->d . " days ago";
						}
					}
					else if($interval->h >= 1) {
						if($interval->h == 1) {
							$time_message = $interval->h . " hour ago";
						}
						else {
							$time_message = $interval->h . " hours ago";
						}
					}
					else if($interval->i >= 1) {
						if($interval->i == 1) {
							$time_message = $interval->i . " minute ago";
						}
						else {
							$time_message = $interval->i . " minutes ago";
						}
					}
					else {
						if($interval->s < 30) {
							$time_message = "Just now";
						}
						else {
							$time_message = $interval->s . " seconds ago";
						}
					}

					$str .= "<div class='status_post'>
				<div class='post_profile_pic'>
					<img src='$profile_pic' class='rounded-circle' alt='profile pic' width='50'>
				</div>
				<div class='posted_by' style='#ACACAC'>
				<a href='$added_by'>$first_name $last_name</a> $user_to &emsp; $time_message 
				$delete_button
				</div>
					<div id='post_body'>
						$body
						<br/>
						<br/>
						<br/>
					</div>
					<div class='newsFeedPostOptions' onClick='javascript:toggle$id()'>
						Comments ($comment_count) &emsp;
						<iframe src='like.php?post_id=$id' scrolling='no' id='like-btn'></iframe>
					</div>
				</div>
				<div class='post_comment' id='toggleComment$id' style='display:none;'>
					 <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
				</div>
				<hr>";


				?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>
				<?php
				}
				else {
					echo "<p>You cannot see this post because you are not friends with this user.</p>";
					return;
				}
		}
		else {
			echo "<p>No post found. If you clicked a link, it may be broken.</p>";
					return;
		}

		echo $str;
	}


}

?>