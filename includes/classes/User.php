<?php
/**
 * User Class
 */
class User {
	private $user;
	private $pdo;

	public function __construct($pdo, $user) {
		$this->pdo = $pdo;
		$user_details_query = $this->pdo->prepare("SELECT * FROM `users` WHERE `username`=:user");
		$user_details_query->bindParam(':user', $user);
		$user_details_query->execute();
		$user_data = $user_details_query->fetch(PDO::FETCH_OBJ);
		$this->user = $user_data;
	}

	public function getNumPosts() {
		$username = $this->user->username;
		$query = $this->pdo->prepare("SELECT `num_posts` FROM `users` WHERE `username` = :username");
		$query->bindParam(':username', $username);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_OBJ);
		return $row->num_posts;

	}

	public function getUserName() {
		return $this->user->username;
	}

	public function getFirstAndLastName() {
		$username = $this->user->username;
		$query = $this->pdo->prepare("SELECT `first_name`, `last_name` FROM `users` WHERE `username` = :username");
		$query->bindParam(':username', $username);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_OBJ);
		return $row->first_name . ' ' . $row->last_name;
	}

	public function getFriendArray() {
		$username = $this->user->username;
		$query = $this->pdo->prepare("SELECT friend_array FROM users WHERE username=:username");
		$query->bindParam(':username', $username);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_OBJ);
		return $row->friend_array;
	}

	public function getProfilePic() {
		$username = $this->user->username;
		$query = $this->pdo->prepare("SELECT `profile_pic` FROM `users` WHERE `username` = :username");
		$query->bindParam(':username', $username);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_OBJ);
		return $row->profile_pic;
	}
	public function getRequestProfilePic($userfrom) {
		$query = $this->pdo->prepare("SELECT `profile_pic` FROM `users` WHERE `username` = :userfrom");
		$query->bindParam(':userfrom', $userfrom);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_OBJ);
		return $row->profile_pic;
	}

	public function isClosed() {
		$username = $this->user->username;
		$check_closed = $this->pdo->prepare("SELECT `user_closed` FROM `users` WHERE `username` = :username");
		$check_closed->bindParam(':username', $username);
		$check_closed->execute();
		$row = $check_closed->fetch(PDO::FETCH_OBJ);
		if ($row->user_closed == 'yes') {
			return true;
		}
		return false;
	}

	public function isFriend($username_to_check) {

		$data = explode(",", $this->user->friend_array);

		if (in_array($username_to_check, $data) || $username_to_check == $this->user->username) {
			return true;
		} else {
			return false;
		}

	}

	public function didReceiveRequest($user_from) {
		$user_to = $this->user->username;
		
		$request_query = $this->pdo->prepare("SELECT * FROM friend_requests WHERE user_to=:user_to AND user_from=:user_from");
		$request_query->bindParam(':user_to', $user_to);
		$request_query->bindParam(':user_from', $user_from);
		$request_query->execute();

		$check_request_query = $request_query->fetch(PDO::FETCH_OBJ);
		//$request_query->debugDumpParams();
			//dd($check_request_query);
		if ($check_request_query) {
			return true;
		} else {
			return false;
		}
	}

	public function didSendRequest($user_to) {
		$user_from = $this->user->username;
		$request_query = $this->pdo->prepare("SELECT * FROM friend_requests WHERE user_to=:user_to AND user_from=:user_from");
		$request_query->bindParam(':user_to', $user_to);
		$request_query->bindParam(':user_from', $user_from);
		$request_query->execute();

		$check_request_query = $request_query->fetch(PDO::FETCH_OBJ);
		if ($check_request_query) {
			return true;
		} else {
			return false;
		}
	}

	public function removeFriend($user_to_remove) {
		try {
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->beginTransaction();

			$logged_in_user = $this->user->username;

			$query = $this->pdo->prepare("SELECT friend_array FROM users WHERE username=:user_to_remove");
			$query->bindParam(':user_to_remove', $user_to_remove);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_OBJ);
			$friend_array_username = $row->friend_array;


			$new_friend_array = str_replace($this->user->username . ",", "", $friend_array_username);
			$remove_friend_query = $this->pdo->prepare("UPDATE users SET friend_array=:new_friend_array WHERE username=:logged_in_user");
			$remove_friend_query->bindValue(':new_friend_array', $new_friend_array);
			$remove_friend_query->bindValue(':logged_in_user', $logged_in_user);
			$remove_friend = $remove_friend_query->execute();

			//$insert_query->debugDumpParams();

			$new_friend_array = str_replace($this->user->username . ",", "", $friend_array_username);
			$remove_friend_query = $this->pdo->prepare("UPDATE users SET friend_array=:new_friend_array WHERE username=:user_to_remove");
			$remove_friend_query->bindValue(':new_friend_array', $new_friend_array);
			$remove_friend_query->bindValue(':user_to_remove', $user_to_remove);
			$remove_friend = $remove_friend_query->execute();

			$this->pdo->commit();
		} catch (Exception $e) {
			echo $e->getMessage();
			$this->pdo->rollback();
		}

	}

	public function sendRequest($user_to) {
		$user_from = $this->user->username;
		$send_request_query = $this->pdo->prepare("INSERT INTO friend_requests(`user_to`, `user_from`) VALUES(:user_to, :user_from)");
		$send_request_query->bindValue(':user_to', $user_to);
		$send_request_query->bindValue(':user_from', $user_from);
		$send_request_query->execute();
	}

	public function getMutualFriends($user_to_check) {
		$mutualFriends = 0;
		$user_array = $this->user->friend_array;
		$user_array_explode = explode(",", $user_array);

		$user_to_check_query= $this->pdo->prepare("SELECT friend_array FROM users WHERE username=:user_to_check");
		$user_to_check_query->bindValue(':user_to_check', $user_to_check);
		$user_to_check_query->execute();
		$row = $user_to_check_query->fetch(PDO::FETCH_OBJ);
			
		$user_to_check_array = $row->friend_array;
		$user_to_check_array_explode = explode(",", $user_to_check_array);

		foreach($user_array_explode as $i) {

			foreach($user_to_check_array_explode as $j) {

				if($i == $j && $i != "") {
					$mutualFriends++;
				}
			}
		}
		return $mutualFriends;

	}

}

?>