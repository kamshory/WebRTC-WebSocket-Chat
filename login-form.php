<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="form.css">
<title>Login</title>
</head>
<body>

<form action="login.php" method="post">
  <div class="imgcontainer">
    <img src="avatar/M.png" alt="Avatar" class="avatar">
  </div>

  <div class="container">
    <label for="uname">Username</label>
    <input class="form-control" type="text" placeholder="Enter Username" name="username" required>

    <label  for="uname">Full Name</label>
    <input class="form-control"  type="text" placeholder="Enter Full Name" name="full_name" required>
    <label>Sex</label>
    <select name="sex" class="form-control">
        <option value="M">Man</option>
        <option value="W">Woman</option>
    </select>
        
    <input class="btn" type="submit" name="login" value="Login">
    <label>
      <input type="checkbox" checked="checked" name="remember"> Remember me
    </label>
  </div>

  <div class="container">
    <button type="button" class="btn">Cancel</button>
    <span class="psw"><a href="#">Forgot password?</a></span>
  </div>
</form>

</body>
</html>
