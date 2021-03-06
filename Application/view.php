<?php
  include 'config.php';
  include 'headers.php';
  include 'sessions.php';

// open connection to the database
include 'opendb.php';

try {
    $clip = NULL;
    $media = $mediaDir;
    $shortname = $_GET["video"];
    
    if(!ctype_alnum($shortname)){
    	$shortname = "";
    }
    
    // get clip properties
    $query = $db->prepare("SELECT host, title, description, posted, user, views, extension FROM clips WHERE shortname=:shortname");
    $query->bindParam(':shortname', $shortname, strlen($shortname));
    $query->execute();

    if($query->rowCount() == 0){
        $clip = NULL;
    } else {
        $clipRow = $query->fetch();
        $host = $clipRow[0];
        $shareURL = "http://$WEBSITE_DOMAIN_NAME/view.php?video=$shortname";
        $title = $clipRow[1];
        $description = $clipRow[2];
        $posted = $clipRow[3];
        $userID = $clipRow[4];
        $views = $clipRow[5];
        $extension = $clipRow[6];

        // get username
        $query = $db->prepare("SELECT username FROM users WHERE id=:user");
        $query->bindParam(':user', $userID);
        $query->execute();
        $userRow = $query->fetch();
        $username = $userRow[0];

        // set the clip the filename
        $clip = "$shortname.$extension";

        // update view counter
        $update = $db->prepare("UPDATE clips SET views=views+1 WHERE shortname=:shortname");
        $update->bindParam(':shortname', $shortname, strlen($shortname));
        $update->execute();
    }
    
  } catch (Exception $e) {
    $clip = NULL;
  }
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

    <title><?php if($clip != NULL){echo "$title ";} ?>| CDC</title>

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
    <script src="/lib/mediaelement-and-player.min.js"></script>
    <link rel="stylesheet" href="./lib/mediaelementplayer.css" />
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
     <center>
     <?php if($clip): ?>
     <h1><?php echo $title ?></h1>
     <video src="<?php echo "https://$host$media/$clip" ?>" width="640" height="390" class="mejs-player" data-mejsoptions='{"alwaysShowControls": true}'></video>
     <br />
     <div style="max-width: 640px; height: 150px;">
       <div style="float: left; max-width: 420px; width: 100%; height: 100%;">
         <pre style="text-align: left; height: 100%;"><?php echo $description ?></pre>
       </div>
       <div style="float: left; margin-left: 20px; max-width: 200px; width: 100%;">
         <pre><b>Views: <?php echo $views ?></b></pre>
         <pre><b>Posted by: <a href="/user.php?username=<?php echo $username ?>"><?php echo $username ?></a></b></pre>
         <b>Share&nbsp;</b><input type="text" name="share" value="<?php echo $shareURL ?>" disabled><br />
       </div>
     </div>
     <script>
	    $(document).ready(function() {
		var v = document.getElementsByTagName("video")[0];
		new MediaElement(v, {success: function(media) {
                    <?php 
                      if(isset($_GET["t"])){
                        $seconds = $_GET["t"];
                        echo "media.setCurrentTime($seconds);";
                      }
                    ?>
		    media.play();
		}});
	    });
      </script>
      <?php else: ?>
      <h1>Sorry, we couldn't find that clip :(</h1>
      <?php endif; ?>
      </center>
      <?php include 'closedb.php'; ?>
      <!-- FOOTER -->
      <hr class="featurette-divider">
      <footer>
        <p class="pull-right"><a href="#">Back to top</a></p>
        <p>&copy; <?php echo date("Y"); ?> Completely Digital Clips &middot; <a href="/privacy.php">Privacy</a> &middot; <a href="/terms.php">Terms</a></p>
      </footer>
    </div>
  </body>
</html>

