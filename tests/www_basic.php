<?php
;namespace KevinMuret\HttpAuth
;define('HTTPAUTHDIR', '../httpauth/src')
;require_once HTTPAUTHDIR."/Basic.php"
;
class HttpAuth extends Basic {
	// List of users and passowrds
	private $users_pwds = array(
		'test' => 'foobar'
	)
	;
	// Method to check if user has been already logged are not ?
	// (Bypass the call to ->getSecret())
	public function isLogged(){
		;return array_key_exists('logged', $_SESSION)
		;
	}
	// Method to autenticate (using the PHP globals variables)
	// must return a correct status (should be JUSTLOGGED / FAILED and can also be LOGGED)
	public function isAuthorized(){
		// Check that username is not empty and exists
		;return ($username = $_SERVER['PHP_AUTH_USER']) && array_key_exists($username, $this->users_pwds)
		// Check passord validity
		&& $this->users_pwds[$username] === $_SERVER['PHP_AUTH_PW']
		;
	}
}

// Start session before instanciate because ->isLogged() wil be called at this time
;session_name("SBASICID")
;session_start()
;$auth = new HttpAuth()
// Check authentication status
;switch ($auth->status){
case $auth::NOTLOGGED:
	;session_destroy()// Keep temporary files cleaner
	// Ask for autorization (HTTP Code: 401)
	;$auth->ask()
	;break
	;
case $auth::JUSTLOGGED:
	// Login were just made !
	;$_SESSION['logged'] = $_SERVER['REQUEST_TIME']
	;
case $auth::LOGGED:// Or previously logged !
	;echo "Logged successfully !"
	;break
	;
case $auth::FAILED:
default:
	;session_destroy()// Keep temporary files cleaner
	// 401 Code needed for re-asking password (keeping the parameters)
	;http_response_code(401)
	;echo "Login failed !"
	;break
	;
}
;
