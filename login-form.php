<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
body {
	font-family: Arial, Helvetica, sans-serif;
	margin:0;
	padding:0;
	background-color:#f1f1f1;
	color:#555555;
	font-size:14px;
}
form {
}

input[type=text], input[type=password], select {
	width: 100%;
	padding: 12px 20px;
	margin: 8px 0;
	display: inline-block;
	border: 1px solid #ccc;
	box-sizing: border-box;
	font-size:14px;
}

input[type="submit"] {
	background-color: #4CAF50;
	color: white;
	padding: 14px 20px;
	margin: 8px 0;
	border: none;
	cursor: pointer;
	width: 100%;
}

input[type="submit"]:hover {
	opacity: 0.8;
}

.cancelbtn {
	width: auto;
	padding: 10px 18px;
	background-color: #f44336;
	border:none;
}

.imgcontainer {
	text-align: center;
	margin: 24px 0 12px 0;
}

img.avatar {
	width: 120px;
	border-radius: 50%;
	background:#FFFFFF;
}

.container {
	padding: 16px;
	background-color:#f1f1f1;
}

span.psw {
	float: right;
	padding-top: 16px;
}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 300px) {
	span.psw {
		display: block;
		float: none;
	}
	.cancelbtn {
		width: 100%;
	}
}

span.psw a {
	color: #555555;
	text-decoration: none;
}
</style>
<title>Login</title>
</head>
<body>

<form action="login.php" method="post">
  <div class="imgcontainer">
    <img src="avatar/M.png" alt="Avatar" class="avatar">
  </div>

  <div class="container">
    <label for="uname">Username</label>
    <input type="text" placeholder="Enter Username" name="username" required>

    <label for="uname">Full Name</label>
    <input type="text" placeholder="Enter Full Name" name="full_name" required>
    <label>Sex</label>
    <select name="sex">
        <option value="M">Man</option>
        <option value="W">Woman</option>
    </select>
        
    <input type="submit" name="login" value="Login">
    <label>
      <input type="checkbox" checked="checked" name="remember"> Remember me
    </label>
  </div>

  <div class="container">
    <button type="button" class="cancelbtn">Cancel</button>
    <span class="psw"><a href="#">Forgot password?</a></span>
  </div>
</form>

</body>
</html>
