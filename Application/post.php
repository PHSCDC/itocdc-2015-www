<?php
  include 'config.php';
  include 'sessions.php';
  include 'opendb.php';
  //include 'headers.php';
  $MAXFILESIZE = 300;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <title>Completely Digital Clips - Post Video</title>

    <!-- Bootstrap core CSS -->
    <link href="/static/css/bootstrap.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <!-- Custom styles for this template -->
    <link href="/static/css/carousel.css" rel="stylesheet">
    <script src="/lib/jquery.js"></script>
    <script src="/static/js/bootstrap.min.js"></script>
  </head>
<!-- NAVBAR
================================================== -->
  <body>
    <div class="navbar-wrapper">
      <div class="container">

        <div class="navbar navbar-inverse navbar-static-top" role="navigation">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php">Completely Digital Clips</a>
              <?php echo "<!-- Hosted by $APPLICATION_HOSTNAME -->"; ?>
            </div>
            <div class="navbar-collapse collapse">
              <ul class="nav navbar-nav">
                <li><a href="/index.php">Home</a></li>
                <?php if(isset($_COOKIE["PHPSESSID"])): ?> 
                  <?php if(is_authenticated($_COOKIE["PHPSESSID"])): ?>
                    <?php $logged_in_email = is_authenticated($_COOKIE["PHPSESSID"]);
                    $query = $db->prepare("SELECT username FROM users WHERE email=:email");
                    $query->bindParam(':email', $logged_in_email, strlen($logged_in_email));
                    $query->execute();
                    if($query->rowCount() == 0){
                      $username = NULL;
                    } else {
                      $userRow = $query->fetch();
                      $username = $userRow[0];
                    } ?>    
                    <li><a href="/post.php">Post Video</a></li>
                    <li><a href="/logout.php">Logout</a></li>
                    <li><a href="/user.php?username=<?php echo($username); ?>">Your Profile</a></li>
                    <li><form name=search action="search.php" method="post">
                    <input type="text" name="q">
                    <input value="Search" type="submit">
                    </form></li>
                  <?php else: ?>
                    <li><a href="/login.php">Login</a></li>
                    <li><a href="/registration.php">Register</a></li>
                  <?php endif; ?>
                <?php else: ?>
                  <li><a href="/login.php">Login</a></li>
                  <li><a href="/registration.php">Register</a></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br />
    <div class="container marketing">
     <hr class="featurette-divider">
     <h1>Post Video</h1>
     <script>
        function getFilesize(fileid) {
          try {
            var fileSize = 0;
              if(checkIE() != 0) {
                var objFSO = new ActiveXObject("Scripting.FileSystemObject"); var filePath = $("#" + fileid)[0].value;
                var objFile = objFSO.getFile(filePath);
                var fileSize = objFile.size; //size in bytes
                fileSize = fileSize / 1048576; //size in mb 
              } else {
                fileSize = $("#" + fileid)[0].files[0].size //size in bytes
                fileSize = fileSize / 1048576; //size in mb 
              }
              return fileSize;
          } catch (e) {
            // alert("Error is :" + e);
          }
        }
        
        function checkIE() {
          var ua = window.navigator.userAgent;
          var msie = ua.indexOf("MSIE ");
          if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){  
            return parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
          } else {
            return 0;
          }
          return false;
        }
     
        function checkPostVideo(){
            if (document.postvideo.title.value.length==0){
                alert("Please enter title!");
                return false;
            }
            if(document.postvideo.description.value.length==0){
                alert("Please enter description!");
                return false;
            }
            if(document.postvideo.video.value.length==0){
                alert("Please select a video to upload!");
                return false;
            }
            if(getFilesize("video") > $MAXFILESIZE){
                alert("Video is too large to upload! Please make sure the video file is smaller than $MAXFILESIZE megabytes.");
                return false;
            }
            //if(false){ //TODO: check for invalid file signatures for ogg, mp4, and webm.
            //    alert("The video file is invalid!")
            //    return false;
            //}
            
            return true;
         }
     </script>
     <br />
     <font face="verdana" color="red">
     <?php 
        if(isset($_GET["message"])) {
          echo "<p>Post Failed</p>";
          echo "<p>" . filter_var($_GET["message"], FILTER_SANITIZE_SPECIAL_CHARS) . "</p>";
        }
     ?>
     </font>
     <form name=postvideo action="upload.php" method="post" enctype="multipart/form-data" onSubmit="return checkPostVideo();">
       <label for="title">Title</label><br />
       <input type="text" name="title"><br />
       <br />
       <label for="description">Description</label><br />
       <textarea name="description" id="description"></textarea>
       <br />
       <br />
       <label for="video">Video File</label><br />
       <input type="file" name="video" id="video"><br />
       Max file size: <?php echo "$MAXFILESIZE" ?> megabytes
       <br />
       <input value="Post" type="submit">
     </form>
     <br />
     <?php include 'closedb.php'; ?>
     <!-- FOOTER -->
     <hr class="featurette-divider">
     <footer>
       <p>&copy; <?php echo date("Y"); ?> Completely Digital Clips &middot; <a href="/privacy.php">Privacy</a> &middot; <a href="/terms.php">Terms</a></p>
      </footer>
    </div>
  </body>
</html>

