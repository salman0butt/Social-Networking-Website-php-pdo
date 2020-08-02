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
		public function getProfilePic() {
		$username = $this->user->username;
		$query = $this->pdo->prepare("SELECT `profile_pic` FROM `users` WHERE `username` = :username");
		$query->bindParam(':username', $username);
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

}

?>