<?php
	session_start();
	include_once('csrf.php');
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Login Page</title>
	</head>
	<body>
		<h1>Shop 2 Login</h1>
		<fieldset>
			<legend>Login Form</legend>
			<form id="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'login'); ?>">
			<label for="email">Email:</label>
			<input type="email" name="email" required="true" pattern="^[\w_\.\-]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$" />
			<label for="pw">Password:</label>
			<input type="password" name="pw" required="true" pattern="^[\w@#$%\^\&\*\-]+$" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
			<input type="submit" value="Login" />
			</form>
		</fieldset>
		<?php
			if(isset($_REQUEST['error'])){
				print "Somthing wrong with your account, please kindly check your ".$_REQUEST['error'];
			}
		?>
	</body>
</html>