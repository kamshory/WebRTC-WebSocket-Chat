<?php
@session_start();
require "inc/websocket-client.php";
$server = '127.0.0.1';
$port = 8888;

if(isset($_POST['login']) && isset($_POST['username']) && isset($_POST['full_name']) &&  isset($_POST['sex']))
{
	$username = $_POST['username'];
	$full_name = $_POST['full_name'];
	$sex = $_POST['sex'];
	if($sp = websocket_open($server, $port, '', $errstr) ) 
	{
		$message = json_encode(
			array(
				'command'=>'check-user-on-system',
				'data'=>array(
					array('username'=>$username)
				)
			)
		);
		websocket_write($sp, $message);
		$response = websocket_read($sp, $errstr);
		print_r($message);
		$response_json = json_decode($response, true);
		$command = $response_json['command'];
		$username = $response_json['data'][0]['username'];
		$available = $response_json['data'][0]['available'];
		if($available)
		{
			$_SESSION['username'] = $username;
			$_SESSION['full_name'] = $full_name;
			$_SESSION['sex'] = $sex;
			$_SESSION['avatar'] = 'avatar/'.$sex.'.png';
		}
		else
		{
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			unset($_SESSION['full_name']);
			unset($_SESSION['sex']);
			unset($_SESSION['avatar']);
		}
	}
	else 
	{
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		unset($_SESSION['full_name']);
		unset($_SESSION['sex']);
		unset($_SESSION['avatar']);
	}
	header("Location: ./");
}
else
{
	require_once "login-form.php";
}
?>