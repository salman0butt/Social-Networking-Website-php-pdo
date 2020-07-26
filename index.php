<?php
require_once 'includes/header.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Post.php';

if (isset($_POST['post']) && isset($_POST['_token']) && token_check($_POST['_token'])) {
	$post = new Post($pdo, $userLoggedIn);
	$post->submitPost($_POST['post_text'],'none');
	header("Refresh:0");
}


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
<div class="main_column column">
	<form action="index.php" method="POST" class="post_form">
		<?php csrf_field(); ?>
		<textarea name="post_text" id="post_text" placeholder="Got somthing to say?"></textarea>
		<input type="submit" name="post" id="post_button" value="Post">
		<hr>
	</form>
	<?php 
		$user_obj = new User($pdo, $userLoggedIn);
		echo $user_obj->getFirstAndLastName();

	 ?>
</div>
</div>
</body>

</html>