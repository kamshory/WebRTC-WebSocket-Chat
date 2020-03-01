<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
	<div>
    	<form name="login" action="login.php" enctype="application/x-www-form-urlencoded" method="post">
        	<div>Username</div>
        	<div><input type="text" name="username" required></div>
        	<div>Full Name</div>
        	<div><input type="text" name="full_name" required></div>
        	<div>Sex</div>
        	<div><select name="sex">
            	<option value="M">Man</option>
            	<option value="W">Woman</option>
            </select></div>
        	<div><input type="submit" name="login" value="Login"></div>
        </form>
    </div>
</body>
</html>