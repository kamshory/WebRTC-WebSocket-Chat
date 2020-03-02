<?php
	@session_start();
	unset($_SESSION['planet_username']);
	unset($_SESSION['planet_full_name']);
	unset($_SESSION['planet_sex']);
	unset($_SESSION['planet_avatar']);
	header("Location: ./");
?>