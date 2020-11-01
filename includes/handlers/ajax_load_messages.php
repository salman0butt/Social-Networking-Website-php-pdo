<?php
require_once '../../config/config.php';
require_once '../classes/User.php';
require_once '../classes/Message.php';

$limit = 7; //Number of messages to load

$message = new Message($pdo, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);

?>