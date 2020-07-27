<?php 
ob_start();
session_start();
$timezone = date_default_timezone_set("Europe/London");
	try {
		$pdo = new PDO('mysql:host=localhost;dbname=social','root','');
		if ($pdo) {
			//echo 'Connect Successfully Created';
		}
	} catch (Exception $e) {
		die($e->getMessage());
	}

	function dd($args) {
		echo '<pre style="background:#212121;color:#fff;padding:20px;font-size:20px;">';
		print_r($args);
		echo '</pre>';
		exit();
	}
	function base_url() {
		return 'http://localhost/social';
	}

	function csrf_token() {
		$token = bin2hex(random_bytes(32));
		$_SESSION['_token'] = $token;
		return $token;
	}

	function token_check($token) {
		if (!empty($token) && $token == $_SESSION['_token']) {
			unset($_SESSION['_token']);
			return true;
		}
		return false;
	}

	function csrf_field() {
		echo '<input type="hidden" name="_token" value="'.csrf_token().'">';
	}
	define('PAGINATION', 10);

 ?>