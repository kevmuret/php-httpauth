<?php
;namespace KevinMuret\HttpAuth
;
class Basic {
	const NOTLOGGED		= 0
	;const FAILED		= 1
	;const JUSTLOGGED	= 2
	;const LOGGED		= 3
	;protected $realm	= "Http Authenticate"
	;public $status		= Basic::NOTLOGGED
	;public $data		= ""
	;
	// Escape quotes (" to \") of a string.
	static public function escapeQuotes($str){
		;return str_replace('"', '\\"', $str)
		;
	}
	//-$realm String identifying the current authrization. (Ex: these could identify
	// a DB/table, will cause to re-login if same login were used somewhere else)
	public function __construct($realm = null){
		;if ($realm) $this->realm = $realm
		;if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
			;if ($this->isLogged())
				$this->status = $this::LOGGED
			;else $this->status = $this->isAuthorized() ? $this::JUSTLOGGED : $this::FAILED
			;
		}
	}
	// Ask for an authorization (401 code is returned).
	public function ask(){
		;return header("WWW-Authenticate: Basic "
		."realm=\"".$this::escapeQuotes($this->realm)."\""
		.(!$this->data ? "" : ",{$this->data}"), true, 401)
		;
	}
}
