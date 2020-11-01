<?php
require_once '../../config/config.php';
require_once '../classes/User.php';
require_once '../classes/Notification.php';

$limit = 7; //Number of messages to load

$notification = new Notification($pdo, $_REQUEST['userLoggedIn']);
echo $notification->getNotifications($_REQUEST, $limit);

?>