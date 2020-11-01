<?php

class Message {
	private $user_obj;
	private $pdo;

	public function __construct($pdo, $user) {
		$this->pdo = $pdo;
		$this->user_obj = new User($pdo, $user);
	}

	public function getMostRecentUser() {
		$userLoggedIn = $this->user_obj->getUsername();

		$query = $this->pdo->prepare("SELECT user_to, user_from FROM messages WHERE user_to=:user_to OR user_from=:user_from ORDER BY id DESC LIMIT 1");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->bindValue(':user_from', $userLoggedIn);
		$query->execute();
		$res_count = $query->rowCount();

		if ($res_count <= 0) {
			return false;
		}

		$row = $query->fetch(PDO::FETCH_OBJ);
		$user_to = $row->user_to;
		$user_from = $row->user_from;
		if ($user_to != $userLoggedIn) {
			return $user_to;
		} else {
			return $user_from;
		}

	}

	public function sendMessage($user_to, $body, $date) {

		if (isset($user_to) && $body != "") {
			$userLoggedIn = $this->user_obj->getUsername();
			$query = $this->pdo->prepare("INSERT INTO `messages`(`user_to`, `user_from`, `body`, `date`, `opened`, `viewed`, `deleted`) VALUES (:user_to, :userLoggedIn, :body, :date, :opened, :viewed, :deleted)");
			$query->bindValue(':user_to', $user_to);
			$query->bindValue(':userLoggedIn', $userLoggedIn);
			$query->bindValue(':body', $body);
			$query->bindValue(':date', $date);
			$query->bindValue(':opened', 'no');
			$query->bindValue(':viewed', 'no');
			$query->bindValue(':deleted', 'no');
			$query->execute();
		}
	}

	public function getMessages($otherUser) {
		$userLoggedIn = $this->user_obj->getUsername();
		$data = "";

		$query = $this->pdo->prepare("UPDATE messages SET opened=:opened WHERE user_to=:user_to AND user_from=:otherUser");
		$query->bindValue(':opened', 'yes');
		$query->bindValue(':user_to', $userLoggedIn);
		$query->bindValue(':otherUser', $otherUser);
		$query->execute();

		$get_messages_query = $this->pdo->prepare("SELECT * FROM messages WHERE (user_to=:userLoggedIn AND user_from=:otherUser) OR (user_from=:user_from AND user_to=:user_to)");
		$get_messages_query->bindValue(':userLoggedIn', $userLoggedIn);
		$get_messages_query->bindValue(':otherUser', $otherUser);
		$get_messages_query->bindValue(':user_from', $userLoggedIn);
		$get_messages_query->bindValue(':user_to', $otherUser);
		$get_messages_query->execute();

		foreach ($get_messages_query->fetchAll(PDO::FETCH_OBJ) as $row) {
			$user_to = $row->user_to;
			$user_from = $row->user_from;
			$body = $row->body;

			$div_top = ($user_to == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $div_top . $body . "</div><br><br>";
		}
		return $data;
	}

	public function getLatestMessage($userLoggedIn, $user2) {
		$details_array = array();

		$query = $this->pdo->prepare("SELECT body, user_to, date FROM messages WHERE (user_to=:user_to AND user_from=:user2) OR (user_to=:user2 AND user_from=:userfrom) ORDER BY id DESC LIMIT 1");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->bindValue(':user_from', $user2);
		$query->bindValue(':user2', $user2);
		$query->bindValue(':userfrom', $userLoggedIn);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_OBJ);
		$sent_by = ($row->user_to == $userLoggedIn) ? "They said: " : "You said: ";

		//Timeframe
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($row->date); //Time of post
		$end_date = new DateTime($date_time_now); //Current time
		$interval = $start_date->diff($end_date); //Difference between dates
		if ($interval->y >= 1) {
			if ($interval == 1) {
				$time_message = $interval->y . " year ago";
			}
			//1 year ago
			else {
				$time_message = $interval->y . " years ago";
			}
			//1+ year ago
		} else if ($interval->m >= 1) {
			if ($interval->d == 0) {
				$days = " ago";
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
				$time_message = "Just now";
			} else {
				$time_message = $interval->s . " seconds ago";
			}
		}

		array_push($details_array, $sent_by);
		array_push($details_array, $row->body);
		array_push($details_array, $time_message);

		return $details_array;
	}

	public function getConvos() {
		$userLoggedIn = $this->user_obj->getUsername();

		$return_string = "";
		$convos = array();

		$query = $this->pdo->prepare("SELECT user_to, user_from FROM messages WHERE user_to=:user_to OR user_from=:user_from ORDER BY id DESC");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->bindValue(':user_from', $userLoggedIn);
		$query->execute();

		foreach ($query->fetchAll(PDO::FETCH_OBJ) as $row) {
			$user_to_push = ($row->user_to != $userLoggedIn) ? $row->user_to : $row->user_from;
			if (!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push);
			}
		}

		foreach ($convos as $username) {
			$user_found_obj = new User($this->pdo, $username);
			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u=$username'> <div class='user_found_messages'>
								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>
								" . $user_found_obj->getFirstAndLastName() . "
								<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span>
								<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . " </p>
								</div>
								</a>";
		}

		return $return_string;

	}

	public function getConvosDropdown($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$query = $this->pdo->prepare("UPDATE messages SET viewed='yes' WHERE user_to=:user_to");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->execute();

		$query = $this->pdo->prepare("SELECT user_to, user_from FROM messages WHERE user_to=:user_to OR user_from=:user_from ORDER BY id DESC");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->bindValue(':user_from', $userLoggedIn);
		$query->execute();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {

			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

			if (!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push);
			}
		}

		$num_iterations = 0; //Number of messages checked
		$count = 1; //Number of messages posted

		foreach ($convos as $username) {

			if ($num_iterations++ < $start) {
				continue;
			}

			if ($count > $limit) {
				break;
			} else {
				$count++;
			}

			$is_unread_query = $this->pdo->prepare("SELECT opened FROM messages WHERE user_to=:user_to AND user_from=:user_from ORDER BY id DESC");
			$is_unread_query->bindValue(':user_to', $userLoggedIn);
			$is_unread_query->bindValue(':user_from', $username);
			$is_unread_query->execute();
			$row = $is_unread_query->fetch(PDO::FETCH_ASSOC);
			
			$style = $row['opened'] == 'no' ? "background-color: #DDEDFF;" : "";

			$user_found_obj = new User($this->pdo, $username);
			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u=$username'>
								<div class='user_found_messages' style='" . $style . "'>
								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>
								" . $user_found_obj->getFirstAndLastName() . "
								<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span>
								<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . " </p>
								</div>
								</a>";
		}

		//If posts were loaded
		if ($count > $limit) {
			$return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
		} else {
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <a href='../messages.php' class='btn btn-info btn-block'>View All</a>";
		}

		return $return_string;
	}

	public function getUnreadNumber() {
		$userLoggedIn = $this->user_obj->getUsername();
		$query = $this->pdo->prepare("SELECT * FROM messages WHERE viewed='no' AND user_to=:user_to");
		$query->bindValue(':user_to', $userLoggedIn);
		$query->execute();

		return $query->rowCount();
	}

}

?>