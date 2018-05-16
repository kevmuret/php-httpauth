<?php
;namespace KevinMuret\HttpAuth
;require_once __DIR__."/DigestQOP.php"
;
class DigestSess extends DigestQOP {
	protected $algorithm	= "MD5-sess"
	;
	// Get $response property
	// (Call this method only when $secret property were provided or when $status is ::JUSTLOGGED)
	public function response(){
		;return ($this->response = $this->hash($this->hash("{$this->secret}:{$this->nonce}:{$this->cnonce}")
		.":{$this->nonce}:{$this->nc}:{$this->cnonce}:auth:"
		.$this->hash("{$_SERVER['REQUEST_METHOD']}:{$_SERVER['REQUEST_URI']}")))
		;
	}
}
