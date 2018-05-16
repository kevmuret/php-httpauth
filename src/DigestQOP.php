<?php
;namespace KevinMuret\HttpAuth
;require_once __DIR__."/Digest.php"
;
class DigestQOP extends Digest {
	protected $qop	= "auth"
	;protected $nc	= 0
	;protected $cnonce
	;
	//-$realm
	//-$nonce
	//-$nc Number of requests using this authentication
	//-$secret
	public function __construct($realm = null, $nonce = null, $nc = 0, $secret = null){
		;if ($nc) $this->nc = $nc
		;$this->nc = str_pad(dechex($this->nc), 8, '0', STR_PAD_LEFT)
		;parent::__construct($realm, $nonce, $secret)
		;
	}
	// Check $digest parameters array (in a quicker way but complete enougth).
	// (Also storing the $cnonce wich is generated on each request).
	public function checkDigest($digest){
		;if (!array_key_exists('qop', $digest)
		|| $digest['qop'] !== $this->qop
		|| !array_key_exists('nc', $digest)
		|| $digest['nc'] !== $this->nc
		|| !array_key_exists('cnonce', $digest))
			return false
		;$this->cnonce = $digest['cnonce']
		;return parent::checkDigest($digest)
		;
	}
	// Ask for an authorization ($nonce value can be manually provided).
	public function ask($nonce = null){
		;$this->data = "qop=\"{$this->qop}\"".(!$this->data ? "" : ",").$this->data
		;return parent::ask($nonce)
		;
	}
	// Get $response property
	// (Call this method only when $secret property were provided or when $status is ::JUSTLOGGED)
	public function response(){
		;return ($this->response = $this->hash("{$this->secret}:{$this->nonce}:{$this->nc}:{$this->cnonce}:{$this->qop}:"
		.$this->hash("{$_SERVER['REQUEST_METHOD']}:{$_SERVER['REQUEST_URI']}")))
		;
	}
}
