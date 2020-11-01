<?php
require_once '../../config/config.php';
require_once '../classes/User.php';
$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

if (strpos($query, "_") !== false) {
	$usersReturned = $pdo->prepare("SELECT * FROM users WHERE username LIKE :query% AND user_closed=:user_closed LIMIT 8");
	$usersReturned->bindValue(':query', $query);
	$usersReturned->bindValue(':user_closed', 'no');
	$usersReturned->execute();

} else if (count($names) == 2) {
		$usersReturned = $pdo->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_closed=:user_closed LIMIT 8");
		$usersReturned->bindValue(':first_name', '%'.$names[0].'%');
		$usersReturned->bindValue(':last_name', '%'.$names[1].'%');
		$usersReturned->bindValue(':user_closed', 'no');
		$usersReturned->execute();
		// $usersReturned->debugDumpParams();exit;
} else {
		$usersReturned = $pdo->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name OR last_name LIKE :last_name) AND user_closed=:user_closed LIMIT 8");
		$usersReturned->bindValue(':first_name', '%'.$names[0].'%');
		$usersReturned->bindValue(':last_name', '%'.$names[0].'%');
		$usersReturned->bindValue(':user_closed', 'no');
		$usersReturned->execute();
}
if ($query != "") {
	foreach($usersReturned->fetchAll(PDO::FETCH_OBJ) as $row) {

		$user = new User($pdo, $userLoggedIn);

		if ($row->username != $userLoggedIn) {
			$mutual_friends = $user->getMutualFriends($row->username) . " friends in common";
		} else {
			$mutual_friends = "";
		}

		if ($user->isFriend($row->username)) {
			echo "<div class='resultDisplay'>
					<a href='messages.php?u=" . $row->username . "' style='color: #000'>
						<div class='liveSearchProfilePic'>
							<img src='" . $row->profile_pic . "'>
						</div>

						<div class='liveSearchText'>
							" . $row->first_name . " " . $row->last_name . "
							<p style='margin: 0;'>" . $row->username . "</p>
							<p id='grey'>" . $mutual_friends . "</p>
						</div>
					</a>
				</div>";

		}

	}
}

?>