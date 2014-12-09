<?php
/**
 * If you have question, send me a email - alisonjsilva@hotmail.com
 * https://www.linkedin.com/pub/alison-silva/20/97b/175
 * @license GPL
 * @version 1.0.0 
 *
 */

define("HOST", "");
define("USER", "");
define("PASS", "");
define("DATABASE", "");

/**
 * The users table
 * @var string
*/
define("USERSTABLE", "");
/**
 * MD5 and SHA1 - If you dont use encrypt, use only ""
 * @var string
*/
define("ENCRYPT", "md5");
/**
 * Set error login message
 * @var string
*/
define("ERROR_LOGIN", "Username or Password is incorrect");

define("EMAIL_LOGIN", true);

/**
 * Class for validate and login users
 * @author alisons
 *
*/
class ValidateUser {

	/**
	 * table users
	 * @var unknown
	 */
	public $userstable = USERSTABLE;
	/**
	 * @var string
	 */
	private $username;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var string
	 */
	public $userId = "";
	/**
	 * @var unknown
	 */
	public $user_data;
	/**
	 * true if operation is complete with success
	 * @var bool
	 */
	public $success = FALSE;
	/**
	 * if user exists
	 * @var bool
	 */
	public $exists = FALSE;
	/**
	 * if user is logged
	 * @var bool
	 */
	public $logged = FALSE;
	/**
	 * if use email for login
	 * @var bool
	 */
	public $isEmail = FALSE;

	/**
	 * if session is started
	 * @var bool
	 */
	public $session = FALSE;

	/**
	 * define encrypt type
	 * @var string
	 */
	private $encrypt = ENCRYPT;

	public $email_login = EMAIL_LOGIN;


	public function __construct() {

		$this->checkSession();

	}


	/**
	 * @param string $username
	 * @param string $password
	 */
	public function login($username, $password) {
		if ($this->getUserData($username, $password) === true) {

			$this->exists = true; // User exists and

			if(!$this->checkSession())
				session_start();

			$_SESSION["user_id"] = $this->user_data["id"];
			$_SESSION["user_name"] = $this->user_data["username"];
			$_SESSION["user_session"] = true;

			$this->logged = true;
			$this->success = true;


		}
	}

	/**
	 * Unset session
	 */
	public function logOut() {
		if(!$this->checkSession())
			session_start();

		if (isset($_SESSION["user_id"]) and isset($_SESSION["user_name"])) {

			session_destroy();
			unset($_SESSION["user_id"]);
			unset($_SESSION["user_name"]);
			
			$this->success = true;

		}

	}


	/**
	 * Get userid only if user logged
	 * @return string
	 */
	public function getUserId() {

		if(!$this->checkSession())
			session_start();

		if (isset($_SESSION["user_id"])) {
			$this->userId = $_SESSION["user_id"];

			$this->logged = true;

			return $_SESSION["user_id"];
		}

	}

	/**
	 * Get username only if user is logged
	 * @return string
	 */
	public function getUserName() {

		if(!$this->checkSession())
			session_start();

		if (isset($_SESSION["user_name"])) {
			$this->userId = $_SESSION["user_name"];

			return $_SESSION["user_name"];
		}

	}


	/**
	 * Used for get userdata. You need set username and password
	 * Data is seted in public $user_data
	 * @return multitype:string
	 */
	private function getUserData($username, $password) {

		$db = new Conn();
		$this->username = $db->safe($username);
		$this->password = $db->safe($password);
		$this->password = $this->encrypt($password);

		if ($this->isEmail($username) === false)
			$rows = $db->query("SELECT id, username FROM " . $this->userstable . " WHERE username = '$this->username' AND password = '$this->password'");
		else
			$rows = $db->query("SELECT id, username FROM " . $this->userstable . " WHERE email = '$this->username' AND password = '$this->password'");

		$this->user_data = $rows->fetch_assoc();

		if($this->user_data !== null)
			return true;

		return false;
			
	}

	/**
	 * Check if user exists using only username or email - This method can be used without password
	 * @param string $username
	 * @return boolean
	 */
	public function existsByUsername($username) {
		$db = new Conn();
		$email = $username;

		if ($this->isEmail($username) === false)
			$rows = $db->query("SELECT username FROM " . $this->userstable . " WHERE username = '$username'");
		else
			$rows = $db->query("SELECT email FROM " . $this->userstable . " WHERE email = '$email'");

		$this->user_data = $rows->fetch_assoc();
		$this->success = true;

		return true;
	}

	/**
	 * Check if user input email
	 * @param string $username
	 * @return boolean
	 */
	public function isEmail($username) {

		if($this->email_login === true){
			if(filter_var($username, FILTER_VALIDATE_EMAIL)){

				return true;
			}
		}

		return false;
	}

	/**
	 * Validate if session is started
	 * @return boolean
	 */
	public function checkSession() {

		return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;

	}


	/**
	 * encrypt password or other string if you need
	 * @param string $string
	 * @return string
	 */
	public function encrypt($string) {

		switch ($this->encrypt) {
			case "md5" :
				$string = trim(md5($string));
				break;
			case "sha1" :
				$string = trim(sha1($string));
				break;
					
			default:
				$string = trim($string);
				break;
		}

		return $string;

	}


}

/**
 * class for handler form.
 * @author alisons
 *
 */
class HandlerForm {

	/**
	 * the post data
	 * @var array
	 */
	public $values = array();
	/**
	 * if operation is complete with success
	 * @var bool
	*/
	public $success = false;
	/**
	 * set url to redirect after login or logout. If empyt do nothing
	 * @var string
	 */
	public $redirect = "";

	/**
	 * @var string
	 */
	public $message = "";

	/**
	 * error message. You can change this in the constant ERROR_LOGIN
	 * @var string
	 */
	public $error_login_message = ERROR_LOGIN;


	public function __construct() {

		if($_POST){

			if (isset($_POST["dologin"])) {

				$this->doLogin();

			}

			if (isset($_POST["dologout"])) {

				$this->doLogOut();

			}

		}

	}

	/**
	 * if data post is correct this method login user using ValidateUser->login
	 * @return boolean
	 */
	public function doLogin() {

		$this->values = $_POST;
			
		if (isset($_POST["username"]) && isset($_POST["password"]) && $_POST["username"] != '' && $_POST["password"] != '') {

			$login = new ValidateUser();

			$this->redirect = $_POST["redirect"];
			$login->login($_POST["username"], $_POST["password"]);

			if($login->success === true){

				$this->success = true;
				$this->redirect();

				return true;

			}


		}
			
		$this->message = $this->error_login_message;
		return false;

	}

	/**
	 * this method logout user using ValidateUser->logout
	 * @return boolean
	 */
	public function doLogOut() {

		$logout = new ValidateUser();

		$this->redirect = $_POST["redirect"];
		$logout->logOut();

		$this->success = true;
		$this->redirect();

		return true;
	}

	/**
	 * Redirect user after login or logout
	 * empty if you don't want to do anything
	 */
	public function redirect() {

		if($this->redirect != "")
			header("location: " . $this->redirect);

	}

	/**
	 * return message for user
	 * @return string
	 */
	public function message() {

		return $this->message;

	}

}

/**
 * class for connect db and make queryes
 * @author alisons
 *
 */
class Conn {

	public $host = HOST;
	public $user = USER;
	public $pass = PASS;
	public $database = DATABASE;
	public $mysqli;
	public $safe = "";

	public function __construct() {
		$this->mysqli = new mysqli($this->host, $this->user, $this->pass, $this->database);

		if (mysqli_connect_errno($this->mysqli)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
	}

	/**
	 * for turn strings safe to mysql
	 * @param $string
	 * @return string
	 */
	public function safe ($string) {

		return $this->mysqli->real_escape_string($string);

	}

	/**
	 * For Query
	 * @param string $query
	 * @return
	 */
	public function query($query) {


		$res = mysqli_query($this->mysqli, $query);
		if (!$res) {
			echo "Failed to run query: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		else {
			return $res;
		}

	}

	public function __destruct() {

		//Close DB Connection
		$this->mysqli->close();
	}

}
