<?php
  //Functions
  //Source: https://stackoverflow.com/a/18693/1800854
  function time_since($since) {
      $chunks = array(
          array(60 * 60 * 24 * 365 , 'year'),
          array(60 * 60 * 24 * 30 , 'month'),
          array(60 * 60 * 24 * 7, 'week'),
          array(60 * 60 * 24 , 'day'),
          array(60 * 60 , 'hour'),
          array(60 , 'minute'),
          array(1 , 'second')
      );

      for ($i = 0, $j = count($chunks); $i < $j; $i++) {
          $seconds = $chunks[$i][0];
          $name = $chunks[$i][1];
          if (($count = floor($since / $seconds)) != 0) {
              break;
          }
      }

      $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
      return $print;
  }

  //Database information
  define("DB_SERVERNAME", "localhost");
  define("DB_USERNAME", "dbuser");
  define("DB_PASSWORD", "dbpass");
  define("DB_DBNAME", "reportbotpro");
  
  //Query our database
  $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
  
  if (!$conn)
    die("Connection to MySQL database failed"); 
  
  $res = $conn->query("SELECT timereported FROM reportlog ORDER BY timereported DESC LIMIT 1");
  $row = $res->fetch_assoc();
  $lastReported = (int)$row['timereported'];
  
  if (empty($lastReported)) {
    $reportsAvailable = 1;
    $lastReportedDisplay = "N/A";
    $nextReportAvailableDisplay = "-";
  } else {
    $lastReportedDisplay = time_since(time() - $lastReported) . " ago";
    $nextReportAvailable = 21600 - (time() - $lastReported);
    
    if ($nextReportAvailable < 0) {
      $nextReportAvailableDisplay = '-';
      $reportsAvailable = 1;
    } else {
      $nextReportAvailableDisplay = time_since($nextReportAvailable);
      $reportsAvailable = 0;
    }
  }
  
  //Check parameters
  $showModal = false;

  //If reports available, parse POST parameters
  if ($reportsAvailable > 0) {
    if (!empty($_POST["steamid64"]) && !empty($_POST["password"])) {
      $steamid64 = htmlspecialchars($_POST["steamid64"]);
      $password = htmlspecialchars($_POST["password"]);
      
      $errorArray = [];
      
      //Verify password
      $passwordList = ['vexisbest', 'invexvip_TEFMgZj6PLamyr3c'];
      
      if (!in_array($password, $passwordList)) {
        $errorArray[] = "The provided password was incorrect.";
      }
     
      if (!preg_match("/^[0-9]{17}$/", $steamid64)) {
        $errorArray[] = "The provided SteamID64 is not valid.";
      }
    
      if (count($errorArray) == 0) {
        //Insert into DB_DBNAME
        $curTime = time();
        $userIpAddress = $_SERVER["HTTP_CF_CONNECTING_IP"]; //cloudflare
        $conn->query("INSERT INTO reportlog(steamid64, timereported, ipaddress, password) VALUES($steamid64, $curTime, '$userIpAddress', '$password')");
        
        //Execute report bot script
        set_time_limit(0);
        $output = [];
        exec("/usr/bin/node /var/www/reportbot.pro/node-csgo-reportbot/bot_arg.js " . escapeshellarg($steamid64), $output);
        
        $showModal = true;
        $modalTitle = "Success! 11 reports sent to $steamid64";
        $modalBody = "<strong>Output:</strong><br>" . join('<br>', $output);
      } else {
        $showModal = true;
        $modalTitle = "Error! Please fix the following issues.";
        $modalBody = join('<br>', $errorArray);
      }
    }
  }
  
  //Close MySQL connection
  $conn->close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>ReportBot.pro</title>
<link rel="stylesheet" href="css/main.css" media="screen">
<link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="js/main.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!-- Random Background Selector -->
<script type="text/javascript">
var images = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg'];
$('html').css({'background-image': 'url(images/bg/' + images[Math.floor(Math.random() * images.length)] + ')'});
</script>
</head>

<body>
<!-- Container -->
<div id="container" class="well">
<!-- Header -->
<div class="page-header">
  <h1 id="typography">Byte's Report Bot</h1>
  <p>Made with ❤️ for <a href="https://www.invexgaming.com.au/">Invex Gaming</a>.</p>
</div>
<!-- Header End -->

<!-- Status -->
<legend>Status</legend>
<p><strong>Note:</strong> 1 set of reports can be sent every 6 hours.</p>
<table class="table table-striped table-hover">
  <tbody>
    <tr>
      <td><strong>Reports Available</strong></td>
      <td><?php echo $reportsAvailable; ?></td>
    </tr>
    <tr>
      <td><strong>Last Report</strong></td>
      <td><?php echo $lastReportedDisplay; ?></td>
    </tr>
    <tr>
      <td><strong>Next Report Available</strong></td>
      <td><?php echo $nextReportAvailableDisplay; ?></td>
    </tr>
  </tbody>
</table> 
<!-- Status End -->

<!-- Form -->
<form class="form-horizontal" method="POST" id="reportbotform">
  <fieldset>
    <legend>Report a cheater</legend>
    <div class="form-group" id="forum-group-errorbox">
      <div class="isa_error">
        <span id="errorIcon"><i class="fa fa-times-circle"></i></span>
        <span id="errorMessage"></span>
      </div>
    </div>
    
    <div class="form-group" id="forum-group-steamid64">
      <label for="inputsteamid64" class="col-lg-2 control-label">SteamID64</label>
      <div class="col-lg-10">
        <input type="text" name="steamid64" class="form-control" id="inputsteamid64" placeholder="e.g. 76561197960265734" onchange="checkSteamId64();">
      </div>
    </div>
    
    <div class="form-group" id="forum-group-password">
      <label for="inputPassword" class="col-lg-2 control-label">Password</label>
      <div class="col-lg-10">
        <input type="password" name="password" class="form-control" id="inputPassword" placeholder="password" onchange="checkPassword();">
      </div>
    </div>
    
    <div class="form-group" id="forum-group-checkbox">
      <label for="inputCheckbox" class="col-lg-2 control-label"></label>
      <div class="col-lg-10">
        <div class="checkbox">
          <label>
            <input type="checkbox" id="inputCheckbox" onchange="checkCheckbox();"> I have the proper permission to use this tool.
          </label>
        </div>
      </div>
    </div>
    
    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="reset" class="btn btn-default" onclick="resetForm();">Reset</button>
        <button type="submit" class="btn btn-primary" onclick="return checkForm();">Submit</button>
      </div>
    </div>
  </fieldset>
</form>
<!-- Form End -->

<!-- Footer -->
<div id="footer">
<p class="copyright">Copyright &copy; <a href="/"><?php echo $_SERVER['SERVER_NAME']; ?></a> <?php echo date("Y"); ?>. All Rights Reserved.</p>
</div>
<!-- Footer End -->

</div>
<!-- Container End -->

<!-- Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php if (!empty($modalTitle)) echo $modalTitle; ?></h4>
      </div>
      <div class="modal-body">
        <p><?php if (!empty($modalBody)) echo $modalBody; ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location = window.location.href;">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Modals end -->

<?php
if ($showModal) {
  echo <<<EOF
<!-- Modal trigger -->
<script type="text/javascript">
$('#myModal').modal();
</script>
<!-- Modal trigger end -->
EOF;
}

if ($reportsAvailable == 0) {
  echo <<<EOF
<!-- Disable form -->
<script type="text/javascript">
$("#reportbotform *").attr("disabled", "disabled").off('click');
</script>
<!-- Disable form -->
EOF;
}
?>

</body>
</html>