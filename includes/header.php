<?php 
    require_once 'config/config.php';
    require_once 'includes/classes/User.php';
    require_once 'includes/classes/Post.php';
    require_once 'includes/classes/Message.php';
    require_once 'includes/classes/Notification.php';


    if (isset($_SESSION['username'])) {
        $userLoggedIn = $_SESSION['username'];
        $user_details_query = $pdo->prepare("SELECT * FROM `users` WHERE `username`=:username");
        $user_details_query->bindParam(':username',$userLoggedIn);
         $user_details_query->execute();
        $user = $user_details_query->fetch(PDO::FETCH_OBJ);
        if (!$user_details_query->rowCount() > 0) {
            echo '<script>alert("First name not found");</script>';
        }
    }else {
        header('Location: register.php');
    }
 ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Social</title>
    <!-- styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/jcrop/dist/jcrop.css">
    <!-- scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="/assets/js/scripts.js"></script>
    <script src="/assets/js/bootbox.min.js"></script>
    <script src="assets/js/jquery.Jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>
</head>

<body>
    <div class="top-bar">
        <div class="logo">
            <a href="index.php">Social!</a>
        </div>
        <div class="search">
            <form action="search.php" method="GET" name="search_form">
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">
                <div class="button_holder">
                   <i class="fas fa-search"></i>
                </div>
            </form>
            <div class="search_results">
            </div>
            <div class="search_results_footer_empty">
            </div>
        </div>
        <nav class="nav">
            <?php
                //Unread messages 
                $messages = new Message($pdo, $userLoggedIn);
                $num_messages = $messages->getUnreadNumber();

                //Unread Notificatons
                $notifications = new Notification($pdo, $userLoggedIn);
                $num_notifications = $notifications->getUnreadNumber();

                //Unread request Notificatons
                $user_obj = new User($pdo, $userLoggedIn);
                $num_requests = $user_obj->getNumberOfFriendRequests();
            ?>
            <a href="<?php echo $userLoggedIn; ?>">
                <?php echo $user->first_name; ?>
            </a>
            <a href="../index.php">
                <i class="fas fa-home"></i>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
                <i class="fas fa-envelope"></i>
                <?php
                if($num_messages > 0)
                 echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
                ?>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
                <i class="fas fa-bell"></i>
                <?php
                if($num_notifications > 0)
                 echo '<span class="notification_badge" id="unread_message">' . $num_notifications . '</span>';
                ?>
            </a>
            <a href="requests.php">
                <i class="fas fa-users"></i>
                <?php
                if($num_requests > 0)
                 echo '<span class="notification_badge">' . $num_requests . '</span>';
                ?>
            </a>
            <a href="../settings.php">
                <i class="fas fa-cog"></i>
            </a>
            <a href="includes/handlers/logout.php">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </nav>
        <div class="dropdown_data_window" style="height:0px; border:none;"></div>
        <input type="hidden" id="dropdown_data_type" value="">
    </div>
    <script>
    $(document).ready(function() {

        $('.dropdown_data_window').scroll(function() {
            let inner_height = $('.dropdown_data_window').innerHeight(); //Div containing data
            let scroll_top = $('.dropdown_data_window').scrollTop();
            let page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
            let noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

            if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                var pageName; //Holds name of page to send ajax request to
                var type = $('#dropdown_data_type').val();


                if (type == 'notification')
                    pageName = "ajax_load_notifications.php";
                else if (type = 'message')
                    pageName = "ajax_load_messages.php"


                var ajaxReq = $.ajax({
                    url: "includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {
                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage 
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current .nextpage 


                        $('.dropdown_data_window').append(response);
                    }
                });

            } //End if 

            return false;

        }); //End (window).scroll(function())


    });
    </script>
    <div class="wrapper">