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

				if ($row['user_to'] == 'none') {
					$user_to = "";
				} else {
					$user_to_obj = new User($pdo, $row['user_to']);
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

						 	if(element.style.display == "block")
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
					$str .= "<div class='status_post'>
				<div class='post_profile_pic'>
					<img src='$profile_pic' class='rounded-circle' alt='profile pic' width='50'>
				</div>
				<div class='posted_by' style='#ACACAC'>
				<a href='$added_by'>$first_name $last_name</a> $user_to &emsp; $time_message
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

}

?>