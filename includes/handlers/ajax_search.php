<?php
require_once '../../config/config.php';
require_once '../classes/User.php';

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

//If query contains an underscore, assume user is searching for usernames
if(strpos($query, '_') !== false) {
	$usersReturnedQuery = $pdo->prepare("SELECT * FROM users WHERE username LIKE :query AND user_closed=:user_closed LIMIT 8");
	$usersReturnedQuery->bindValue(':query', '%'.$query.'%');
	$usersReturnedQuery->bindValue(':user_closed', 'no');
	$usersReturnedQuery->execute();
}
//If there are two words, assume they are first and last names respectively
else if(count($names) == 2){
		$usersReturnedQuery = $pdo->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_closed=:user_closed LIMIT 8");
		$usersReturnedQuery->bindValue(':first_name', '%'.$names[0].'%');
		$usersReturnedQuery->bindValue(':last_name', '%'.$names[1].'%');
		$usersReturnedQuery->bindValue(':user_closed', 'no');
		$usersReturnedQuery->execute();
}
//If query has one word only, search first names or last names 
else {
	$usersReturnedQuery = $pdo->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name OR last_name LIKE :last_name) AND user_closed=:user_closed LIMIT 8");
	$usersReturnedQuery->bindValue(':first_name', '%'.$names[0].'%');
	$usersReturnedQuery->bindValue(':last_name', '%'.$names[0].'%');
	$usersReturnedQuery->bindValue(':user_closed', 'no');
	$usersReturnedQuery->execute();
}

if($query != ""){

	while($row = $usersReturnedQuery->fetch(PDO::FETCH_ASSOC)) {
		$user = new User($pdo, $userLoggedIn);
		if($row['username'] != $userLoggedIn){
			$mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
		}
		else {
			$mutual_friends = "";
		}

		echo "<div class='resultDisplay'>
				<a href='" . $row['username'] . "' style='color: #1485BD'>
					<div class='liveSearchProfilePic'>
						<img src='" . $row['profile_pic'] ."'>
					</div>

					<div class='liveSearchText'>
						" . $row['first_name'] . " " . $row['last_name'] . "
						<p>" . $row['username'] ."</p>
						<p id='grey'>" . $mutual_friends ."</p>
					</div>
				</a>
				</div>";

	}

}

?>