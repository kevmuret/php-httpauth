<?php
;namespace KevinMuret\HttpAuth
;require_once __DIR__."/Basic.php"
;if (!function_exists("http_parse_params"))
	require_once __DIR__."/http_parse_params.func.php"
;
class Digest extends Basic {
	protected $hash		= "md5"
	;protected $algorithm	= "MD5"
	;protected $uri
	;protected $response
	;protected $domains
	;protected $nonce
	;protected $opaque
	;protected $secret
	;public $stale		= false
	;
	// Get $opaque property (compute it if necessary)
	public function opaque(){
		;return !$this->opaque ? ($this->opaque = $this->hash($this->realm))
		: $this->opaque
		;
	}
	// Get $nonce property (compute it if necessary or replace it if provided)
	public function nonce($nonce = null){
		;return !$nonce ? (!$this->nonce ? ($this->nonce = uniqid()) : $this->nonce)
		: ($this->nonce = $nonce)
		;
	}
	// Get $secret property
	public function secret(){
		;return $this->secret
		;
	}
	//-$realm
	//-$nonce Must be null if no nonce were previously communicated to the client
	// and must be provided to check the given client's value.
	//-$response Must be provided only if $nonce value is provided too and the client
	// has already been fully authentificated. (It can be retreived using ->response()
	// method and will be valid only if $status is ::JUSTLOGGED)
	public function __construct($realm = null, $nonce = null, $secret = null){
		;if ($realm) $this->realm = $realm
		;if ($nonce) $this->nonce = $nonce
		;if ($secret) $this->secret = $secret
		;if (array_key_exists('PHP_AUTH_DIGEST', $_SERVER)) {
			;if (!($digest = http_parse_params($_SERVER['PHP_AUTH_DIGEST']))
			|| !property_exists($digest, 'params')
			|| !($digest = $digest->params)
			|| !$this->checkDigest($digest))
				$this->status = $this::FAILED
			;else if ($this->secret && $this->isLogged())
				$this->status = $this::LOGGED
			;else $this->status = $this->isAuthorized($digest)
			;
		}
	}
	// Apply the current hash function to a string
	public function hash($str){
		;return hash($this->hash, $str)
		;
	}
	// Create a secret token for storage (used for check too) with given
	// $username and $pass.
	public function createSecret($username, $pass){
		;return $this->hash("$username:{$this->realm}:$pass")
		;
	}
	// Check according $digest parameters array if authroization can be given or not.
	// ($status property will be ::JUSTLOGGED in case of success)
	public function isAuthorized($digest){
		;return ($this->secret = $this->getSecret($digest))
		&& ($response = $this->response($digest))
		=== $digest['response']
		? $this::JUSTLOGGED : $this::FAILED
		;
	}
	// Check $digest parameters array (in a quicker way but complete enougth).
	public function checkDigest($digest){
		;return array_key_exists('username', $digest)
		&& array_key_exists('realm', $digest)
		&& $digest['realm'] === $this->realm
		&& array_key_exists('nonce', $digest)
		&& $digest['nonce'] === $this->nonce
		&& array_key_exists('uri', $digest)
		&& array_key_exists('opaque', $digest)
		&& $digest['opaque'] === $this->opaque()
		&& array_key_exists('response', $digest)
		&& (!$this->secret || $digest['response'] === $this->response())
		;
	}
	// Ask for an authorization ($nonce value can be manually provided).
	public function ask($nonce = null){
		//realm,nonce,uri,algorithm,response,opaque
		;header("WWW-Authenticate: Digest "
		."realm=\"".$this::escapeQuotes($this->realm)."\""
		.(!$this->domains ? "" : ",\"".implode(" ", array_map(get_class().'::escapeQuotes', $this->domains))."\"")
		.(!$this->data ? "" : ",").$this->data
		.",nonce=\"".$this::escapeQuotes($this->nonce($nonce))."\""
		.",algorithm=\"".$this::escapeQuotes($this->algorithm)."\""
		.",opaque=\"".$this::escapeQuotes($this->opaque())."\""
		.(!$this->stale ? "" : ",stale=\"true\"")
		, false, 401)
		;
	}
	// Get $response property
	// (Call this method only when $secret property were provided or when $status is ::JUSTLOGGED)
	public function response(){
		;return ($this->response = $this->hash("{$this->secret}:{$this->nonce}:"
		.$this->hash("{$_SERVER['REQUEST_METHOD']}:{$_SERVER['REQUEST_URI']}")))
		;
	}
	// Stronger authentication with a new nonce value to be used is the next requests (not supported by browsers !)
	public function authenticate($nonce = null, $code = 200){
		;$this->nonce($nonce)
		;return header("Authentication-Info: "
		."nextnonce=\"".$this::escapeQuotes($this->nonce)."\"", true, $code)
		;
	}
}
