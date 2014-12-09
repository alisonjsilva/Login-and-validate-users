<?php
require 'login.class.php';

$login = new HandlerForm();
$user = new ValidateUser();

if($user->getUserId()) :

?>
	<div>Hello <?php echo $user->getUserName(); ?></div>
	<form action="" method="POST" id="logout-form">
		<input type="submit" value="Logout" >
		<input type="hidden" name="redirect" value="#logedout">
		<input type="hidden" name="dologout" value="dologout">
	</form>
	
<?php else : ?>
	
	<form action="" method="POST" id="loging-form" >
	
		<label for="username">
			Username:
		</label>
		<input type="text" name="username" placeholder="Username or email" value="">
		<div class="separator"></div>
		<label for="password">
			Password:
		</label>
		<input type="password" name="password" placeholder="Type your Password" value="">
		<div class="separator"></div>
		<input type="hidden" name="dologin" value="dologin">
		<input type="hidden" name="redirect" value="#loged">
		<input id="btnlogin" type="submit">
		
		<div class="message"><?php echo $login->message; ?></div>
	
	</form>

<?php endif; ?>