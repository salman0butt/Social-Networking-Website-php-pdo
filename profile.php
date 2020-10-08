<?php
require_once 'includes/header.php';

$message_obj = new Message($pdo, $userLoggedIn);

if(isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = $pdo->prepare("SELECT * FROM `users` WHERE `username`=:username");
    $user_details_query->bindParam(':username', $username);
    $user_details_query->execute();
    $user_array = $user_details_query->fetch(PDO::FETCH_OBJ);

    $num_friends = substr_count($user_array->friend_array, ",");

}



if(isset($_POST['remove_friend'])) {
    $user = new User($pdo, $userLoggedIn);
    $user->removeFriend($username);
}

if(isset($_POST['add_friend'])) {
    $user = new User($pdo, $userLoggedIn);
    $user->sendRequest($username);
}
if(isset($_POST['respond_request'])) {
    header("Location: requests.php");
}

if(isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = htmlspecialchars($_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }

  $link = '#profileTabs a[href="#messages_div"]';
  echo "<script> 
          $(function() {
              $('" . $link ."').tab('show');
          });
        </script>";
}

 ?>
<style type="text/css">
.wrapper {
    margin-left: 0px;
    padding-left: 0px;
}
</style>
<div class="profile_left">
    <img src="<?php echo $user_array->profile_pic; ?>">
    <div class="profile_info">
        <p>
            <?php echo "Posts: " . $user_array->num_posts; ?>
        </p>
        <p>
            <?php echo "Likes: " . $user_array->num_likes; ?>
        </p>
        <p>
            <?php echo "Friends: " . $num_friends ?>
        </p>
    </div>
    <form action="<?php echo $username; ?>" method="POST">
        <?php 
            $profile_user_obj = new User($pdo, $username); 
            if($profile_user_obj->isClosed()) {
                header("Location: user_closed.php");
            }

            $logged_in_user_obj = new User($pdo, $userLoggedIn); 

            if($userLoggedIn != $username) {

                if($logged_in_user_obj->isFriend($username)) {
                    echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
                }
                else if ($logged_in_user_obj->didReceiveRequest($username)) {
                    echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
                }
                else if ($logged_in_user_obj->didSendRequest($username)) {
                    echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
                }
                else 
                    echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';

            }
            ?>
    </form>
    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">
    <?php  
        if($userLoggedIn != $username) {
          echo '<div class="profile_info_bottom">';
            echo $logged_in_user_obj->getMutualFriends($username) . " Mutual friends";
          echo '</div>';
        }
       ?>
</div>
<div class="profile_main_column column"><br>
    <ul class="nav nav-tabs" role="tablist" id="profileTabs">
        <li role="presentation" class="nav-item active"><a href="#newsfeed_div" aria-controls="newsfeed_div" class="nav-item nav-link active" role="tab" data-toggle="tab">Newsfeed</a></li>
        <li role="presentation"><a href="#messages_div" class="nav-item nav-link" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="newsfeed_div">
            <div class="posts_area"></div>
            <div id="loading" class="text-center">
                <img src="assets/images/icons/loading.gif" alt="loading" height="30">
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="messages_div">
            <?php  
        

          echo "<h4>You and <a href='" . $username ."'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";

          echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
          echo "</div>";
        ?>
            <div class="message_post">
                <form action="" method="POST">
                    <textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
                    <input type='submit' name='post_message' class='info' id='message_submit' value='Send'>
                </form>
            </div>
            <script>
                var div = document.getElementById("scroll_messages");
            if (div) {
                div.scrollTop = div.scrollHeight;
            }
    </script>
        </div>
    </div>
     
</div>
<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="postModalLabel">Post something!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>
                <form class="profile_post" action="" method="POST">
                    <div class="form-group">
                        <textarea class="form-control" name="post_body"></textarea>
                        <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
                        <input type="hidden" name="user_to" value="<?php echo $username; ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script>
jQuery(document).ready(function($) {

    let userLoggedIn = '<?php echo $userLoggedIn; ?>';
    let profileUsername = '<?php echo $username; ?>';
    $('#loading').show();

    //Original ajax request for loading first posts 
    $.ajax({
        url: "includes/handlers/ajax_load_profile_posts.php",
        type: "GET",
        data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
        cache: false,

        success: function(data) {
            $('#loading').hide();
            $('.posts_area').html(data);
        }
    });

    $(window).scroll(function(event) {
        /* Act on the event */
        let height = $('#post_area').height();
        let scroll_top = $(this).scrollTop();
        let page = $('.posts_area').find('.nextPage').val();
        let noMorePosts = $('.posts_area').find('.noMorePosts').val();

        if (($(window).scrollTop() + $(window).height() >= $(document).height()) && noMorePosts == 'false') {

            $('#loading').show();

            //ajax request for loading posts
            var ajaxReq = $.ajax({
                url: 'includes/handlers/ajax_load_profile_posts.php',
                type: "GET",
                data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                cache: false,
                success: function(response) {
                    $('.posts_area').find('.nextPage').remove();
                    $('.posts_area').find('.noMorePosts').remove();

                    $('#loading').hide();
                    $('.posts_area').append(response);
                }
            });
        } //end if
        return false;
    }); // End $(widnow).scroll(function(event)

});
</script>
</body>

</html>