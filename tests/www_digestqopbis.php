<?php
;namespace KevinMuret\HttpAuth
;define('HTTPAUTHDIR', '../httpauth/src')
;require_once HTTPAUTHDIR."/DigestQOP.php"
;
class HttpAuth extends DigestQOP {
	// List of users and passowrds
	private $users_pwds = array(
		'test' => 'foobar'
	)
	;
	// Method to fetch secret token (according given Digest parameters)
	public function getSecret($digest){
		// Check that username is not empty and exists
		;return ($username = $digest['username']) && array_key_exists($username, $this->users_pwds)
		// For example this generate the secret token to be stored in secured application.
		? $this->createSecret($username, $this->users_pwds[$username]) : null
		;
	}
}

// Start session before instanciate because ->isLogged() wil be called at this time
;session_name("SDIGESTQOPBISID")
;session_start()
// If authorization already started ('nonce' value must be re-used)
;if (array_key_exists('auth_nonce', $_SESSION))
	$auth = new HttpAuth(null, $_SESSION['auth_nonce'], ++$_SESSION['auth_nc'])
// If not initalize session variables with a generated 'nonce' value
;else if ($auth = new HttpAuth())
	// Should be completely reseted (ex: in case of others methods elsewhere on the same domain)
	$_SESSION = array('auth_nonce' => $auth->nonce())
// Check authentication status
;switch ($auth->status){
case $auth::NOTLOGGED:
	// Make sure there is no bypass to this login system
	;if (array_key_exists('logged', $_SESSION))
		unset($_SESSION['logged'])
	// Force the counter to be zero
	;$_SESSION['auth_nc'] = 0
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
