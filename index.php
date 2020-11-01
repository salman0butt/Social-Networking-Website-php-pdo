<?php
require_once 'includes/header.php';


if (isset($_POST['post']) && isset($_POST['_token']) && token_check($_POST['_token'])) {
    $uploadOk = 1;
    $imageName = $_FILES['fileToUpload']['name'];
    $errorMessage = "";

    if($imageName != "") {
        $targetDir = "assets/images/posts/";
        $imageName = $targetDir . uniqid() . basename($imageName);
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

        if($_FILES['fileToUpload']['size'] > 10000000) {
            $errorMessage = "Sorry your file is too large";
            $uploadOk = 0;
        }

        if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
            $errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
            $uploadOk = 0;
        }

        if($uploadOk) {
            if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                //image uploaded okay
            }
            else {
                //image did not upload
                $uploadOk = 0;
            }
        }

    }

    if($uploadOk) {
        $post = new Post($pdo, $userLoggedIn);
        $post->submitPost($_POST['post_text'],'none', $imageName);
        header("Refresh:0");
    }
    else {
        echo "<div style='text-align:center;' class='alert alert-danger'>
                $errorMessage
            </div>";
    }

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
    <form action="index.php" method="POST" class="post_form" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <input type="file" name="fileToUpload" id="fileToUpload">
        <textarea name="post_text" id="post_text" placeholder="Got somthing to say?"></textarea>
        <input type="submit" name="post" id="post_button" value="Post">
        <hr>
    </form>

    <div class="posts_area"></div>
    <div id="loading" class="text-center">
        <img src="assets/images/icons/loading.gif" alt="loading" height="30">
    </div>
</div>
    <div class="user_details column">
        <h4>Popular</h4>
        <div class="trends">
            <?php 
            $query = $pdo->prepare("SELECT * FROM trends ORDER BY hits DESC LIMIT 9");
            $query->execute();
            $query = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($query as $row) {
                
                $word = $row['title'];
                $word_dot = strlen($word) >= 14 ? "..." : "";

                $trimmed_word = str_split($word, 14);
                $trimmed_word = $trimmed_word[0];

                echo "<div style='padding: 2% 0;'>";
                echo '#'.$trimmed_word . $word_dot;
                echo "<br></div>";
            }
            ?>
        </div>
    </div>
<script>
let userLoggedIn = '<?php echo $userLoggedIn; ?>';

jQuery(document).ready(function($) {

    $('#loading').show();

    //Original ajax request for loading first posts 
    $.ajax({
        url: "includes/handlers/ajax_load_posts.php",
        type: "GET",
        data: "page=1&userLoggedIn=" + userLoggedIn,
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
                url: 'includes/handlers/ajax_load_posts.php',
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
</div>
</body>

</html>