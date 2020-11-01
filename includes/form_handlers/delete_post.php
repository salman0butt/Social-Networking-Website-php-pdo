<?php 
require '../../config/config.php';
	
	if(isset($_GET['post_id']))
		$post_id = $_GET['post_id'];

	if(isset($_POST['result'])) {
		if($_POST['result'] == 'true'){
		$query = $pdo->prepare("UPDATE posts SET deleted=:deleted WHERE id=:post_id");
		$query->bindValue(':deleted', 'yes');
		$query->bindValue(':post_id', $post_id);

		if($query->execute()){
		 	return true;
		 }else {
		 	return false;
		 }
		}
	
	}

?>