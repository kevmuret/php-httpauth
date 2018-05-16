<?php
// Function needed to parse Digest params
// can be replaced by a php extension (http://php.net/manual/fa/function.http-parse-params.php)
function __http_parse_nextParam($param, $offset = 0){
	;$next_pos = array(
		strpos($param, ",", $offset)
	,	strpos($param, ";", $offset)
	)
	;if ($next_pos[0] === $next_pos[1]) return $next_pos[0]
	;else if ($next_pos[0] === false) return $next_pos[1]
	;else if ($next_pos[1] === false) return $next_pos[0]
	;else return min($next_pos[0], $next_pos[1])
	;
}
function http_parse_params($param){
	;$res = array()
	;$offset = 0
	;while (true) {
		;$next_pos = __http_parse_nextParam($param, $offset)
		;if (trim($frag = $next_pos !== false ? substr($param, $offset, $next_pos - $offset)
		: substr($param, $offset))) {
			// No quote
			;if (!preg_match("#^\s*([^=\s]+)\s*=\s*\"([^\"]*)#", $frag, $match)) {
				;if (preg_match("#^\s*([^=\s]+)\s*=\s*([^\s]+)\s*$#", $frag, $match))
					$res[$match[1]] = $match[2]
				;else array_push($res, trim($frag))
				;if ($next_pos === false) break
				;$offset = $next_pos + 1
				;
			// With quote
			} else {
				;$cut = $offset + strlen($match[0])
				;$offset = $cut
				;if (substr($param, $offset, 1) === "\"") {
					;$res[$match[1]] = str_replace("\\\"", "\"", $match[2])
					;if ($next_pos === false) break
					;$offset = __http_parse_nextParam($param, $offset + 1)
					;
				} else {
					;while (($eof = strpos($param, "\"", $offset)) !== false) {
						;$frag = substr($param, $offset, $eof - $offset)
						;if (preg_match("#\\\\+$#", $frag, $match_escape)) {
							;if (!(strlen($match_escape[0]) % 2))
								break
							;
						} else break
						;$offset = $eof+1
						;
					}
					;$res[$match[1]] = str_replace("\\\"", "\"", $match[2].($eof === false
					? substr($param, $cut) : substr($param, $cut, $eof - $cut)))
					;if ($eof === false
					|| ($offset = __http_parse_nextParam($param, $eof)) === false)
						break
					;
				}
				;$offset++
				;
			}
		} else if ($next_pos === false) break
		;else $offset = $next_pos + 1
		;
	}
	;return (object)array('params' => $res)
	;
}
