<?php
session_start();
header("Access-Control-Allow-Origin: *");
if(isset($_SESSION['planet_username']))
{
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Chat</title>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="chat.css?rand=<?php echo mt_rand(1,99999999);?>">
<link rel="stylesheet" href="icon.css">
<script type="text/javascript">
var websocketURL = '';
if(window.location.protocol.toString() == 'https:')
{
	websocketURL = 'wss://<?php echo $_SERVER['SERVER_NAME'];?>/wss.socket/';
}
else
{
	websocketURL = 'ws://<?php echo $_SERVER['SERVER_NAME'];?>:8888/';
}
console.log(window.location.protocol.toString());
console.log(websocketURL);
</script>
<script type="text/javascript" src="jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="tab-control.js"></script>
<script type="text/javascript" src="ringtone.js"></script>
<script type="text/javascript" src="chat.js?rand=<?php echo mt_rand(1,99999999);?>"></script>
<script type="text/javascript" src="lib.languages/en/index.php?rand=<?php echo mt_rand(1,99999999);?>"></script>
<style type="text/css">

</style>
</head>

<body>
<div class="all">
<div class="wrapper">
  <div class="sidebar">
  	<ul>
    	<li><a href="logout.php">Logout</a></li>
    </ul>
    <div class="user-list-area">
        <ul class="user-list">
        </ul>
    </div>
  </div>
  <div class="main">

    <div class="progress-bar-container">
    <div class="progress-bar">
        <div class="progress-bar-inner">
        </div>
    </div>
    </div>
    <div class="control-area">
        <a href="javascript:startVideoCallManualy()" class="make-call"><span></span></a>
        <a href="javascript:pauseVideo()" class="pause-video"><span></span></a>
        <a href="javascript:pauseAudio()" class="pause-audio"><span></span></a>
        <a href="javascript:stopVideoCall()" class="end-call"><span></span></a>
    </div>
    <div class="video-container">
    <div class="video-area" data-connected="false" data-mode="0">
        <div class="local-video">
            <video id="localVideo" autoplay muted="muted"></video>
        </div>
        <div class="remote-video">
            <video id="remoteVideo" autoplay></video>
        </div>
    </div>
    </div>


  </div>
</div>
</div>

<div class="planet-chat-container" data-connected="false"></div>
<div class="planet-video-call"><div class="video-call-popup"></div></div>

</body>
</html>
<?php
}
else
{
	require_once "login-form.php";
}
?>