<?php 
	require_once('../../config/config.php');
	require_once('../classes/User.php');
	require_once('../classes/Post.php');

	$limit = PAGINATION; //numbers of posts to be loaded

	$posts = new Post($pdo, $_REQUEST['userLoggedIn']);
	$posts->loadPostsFriends($_REQUEST, $limit);


 ?>