<?php
class Utility
{
	/**
	* Parse request header
	* @param string $header Request header from client
	* @return array Associated array of the request header
	*/
	public static function parseHeaders($headers)
	{
		$headers = trim($headers, "\r\n");
		$headers = str_replace("\n", "\r\n", $headers);
		$headers = str_replace("\r\r\n", "\r\n", $headers);
		$headers = str_replace("\r", "\r\n", $headers);
		$headers = str_replace("\r\n\n", "\r\n", $headers);
		$arr = explode("\r\n", $headers);
		$arr2 = array();
		
		$firstLine = $arr[0];
		$arr4 = explode(" ", $firstLine);
		$method = @$arr4[0];
		$version = @$arr4[2];
		$path = '/';
		$requestURL = '/';
		$query = array();
		if(isset($arr4[1]))
		{
			$requestURL = $arr4[1];
			if(stripos($arr4[1], "?") !== false)
			{
				$arr5 = explode("?", $arr4[1], 2);
				$path = $arr5[0];
				@parse_str($arr5[1], $query);
			}
		}
		
		foreach($arr as $idx=>$value)
		{
			if($idx > 0)
			{
				$arr3 = explode(": ", $value, 2);
				if(count($arr3) == 2)
				{
					$arr2[strtolower($arr3[0])] = $arr3[1];
				}
			}
		}
		return array('method'=>$arr4[0], 'uri'=>$requestURL, 'path'=>$path, 'query'=>$query, 'version'=>$version, 'headers'=>$arr2);
	}
	/**
	* Parse cookie
	* @param string $cookieString Cookie from client
	* @return array Associated array of the cookie
	*/
	public static function parseCookie($cookieString)
	{
		$cookieData = array();
		$arr = explode("; ", $cookieString);
		foreach($arr as $key=>$val)
		{
			$arr2 = explode("=", $val, 2);
			if(count($arr2) > 1)
			{
				$cookieData[$arr2[0]] = $arr2[1];
			}
		}
		return $cookieData;
	} 
	/**
	* Read cookie
	* @param array $cookieData Associated array of the cookie
	* @return string name Cooke name
	*/
	public static function readCookie($cookieData, $name)
	{
		$v0 = (isset($cookieData[$name."0"]))?($cookieData[$name."0"]):"";
		$v1 = (isset($cookieData[$name."1"]))?($cookieData[$name."1"]):"";
		$v2 = (isset($cookieData[$name."2"]))?($cookieData[$name."2"]):"";
		$v3 = (isset($cookieData[$name."3"]))?($cookieData[$name."3"]):"";
		$v  = strrev(str_rot13($v1.$v3.$v2.$v0));
		if($v=="")
		{
			return md5(microtime().mt_rand(1,9999999));
		}
		else 
		{
			return $v;
		}
	}
	/**
	* Get session data
	* @param string $sessionID Session ID
	* @param string $sessionSavePath Session save path
	* @param string $prefix Prefix of the session file name
	* @return array Asociated array contain session
	*/
	public static function getSessions($sessionID, $sessionSavePath = NULL, $prefix = "sess_")
	{
		if($sessionSavePath === NULL)
		{
			$sessionSavePath = session_save_path();
		}
		$path = $sessionSavePath."/".$prefix.$sessionID;
		if(file_exists($path))
		{
			$session_text = file_get_contents($path);
			if($session_text != '')
			{
				return Utility::sessionDecode($session_text);
			}
		}
		return array();
	}
	/**
	* Decode session data
	* @param string $sessionData Raw session data
	* @return array Asociated array contain session
	*/
	public static function sessionDecode($sessionData) 
	{
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($sessionData)) 
		{
			if (!strstr(substr($sessionData, $offset), "|")) 
			{
				throw new Exception("invalid data, remaining: " . substr($sessionData, $offset));
			}
			$pos = strpos($sessionData, "|", $offset);
			$num = $pos - $offset;
			$varname = substr($sessionData, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($sessionData, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}
	/**
	* Decode binary session data
	* @param string $sessionData Raw session data
	* @return array Asociated array contain session
	*/
	public static function sessionDecodeBinary($sessionData) 
	{
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($sessionData)) 
		{
			$num = ord($sessionData[$offset]);
			$offset += 1;
			$varname = substr($sessionData, $offset, $num);
			$offset += $num;
			$data = unserialize(substr($sessionData, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}


	

	/**
	 * Unmask incoming framed message
	 * @param $text Masked message
	 * @return string Plain text
	 */
	 /*
	public static function unmask($text)
	{
		$length = ord($text[1]) & 127;
		if ($length == 126) 
		{
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} 
		else if ($length == 127) 
		{
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} 
		else 
		{
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) 
		{
			$text.= $data[$i] ^ $masks[$i % 4];
		}
		return $text;
	}
	*/
	/**
	 * Encode message for transfer to client
	 * @param $text Plain text to be sent to the client
	 * @return string Masked message
	 */
	public static function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		if ($length <= 125) 
		{
			$header = pack('CC', $b1, $length);
		}
		else if ($length > 125 && $length < 65536)
		{ 
			$header = pack('CCn', $b1, 126, $length);
		}
		else if($length >= 65536)
		{
			$header = pack('CCNN', $b1, 127, $length);
		} 
		return $header . $text;
	}
	/*
	 * Convert UTF-8 to 8 bits HTML Entity code
	 * @param $string String to be converted
	 * @return string 8 bits HTML Entity code
	 */
	function utf8ToEntities($content) {

		if(!mb_check_encoding($content, 'UTF-8')
	
			OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
	
	
	
			$content = mb_convert_encoding($content, 'UTF-8');
	
	
	
			if (mb_check_encoding($content, 'UTF-8')) {
	
				// log('Converted to UTF-8');
	
			} else {
	
				// log('Could not converted to UTF-8');
	
			}
	
		}
	
		return $content;
	
	}

	/*
	public static function utf8ToEntities($string)
	{
		if (!@ereg("[\200-\237]",$string) && !@ereg("[\241-\377]",$string))
			return $string;
		$string = preg_replace("/[\302-\375]([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\340-\375].([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\360-\375]..([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\370-\375]...([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\374-\375]....([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\300-\301]./", "&#65533;", $string);
		$string = preg_replace("/\364[\220-\277]../","&#65533;",$string);
		$string = preg_replace("/[\365-\367].../","&#65533;",$string);
		$string = preg_replace("/[\370-\373]..../","&#65533;",$string);
		$string = preg_replace("/[\374-\375]...../","&#65533;",$string);
		$string = preg_replace("/[\376-\377]/","&#65533;",$string);
		$string = preg_replace("/[\302-\364]{2,}/","&#65533;",$string);
		$string = preg_replace(
			"/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/e",
			"'&#'.((ord('\\1')&7)<<18 | (ord('\\2')&63)<<12 |".
			" (ord('\\3')&63)<<6 | (ord('\\4')&63)).';'",
		$string);
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
		"'&#'.((ord('\\1')&15)<<12 | (ord('\\2')&63)<<6 | (ord('\\3')&63)).';'",
		$string);
		$string = preg_replace("/([\300-\337])([\200-\277])/e",
		"'&#'.((ord('\\1')&31)<<6 | (ord('\\2')&63)).';'",
		$string);
		$string = preg_replace("/[\200-\277]/","&#65533;",$string);
		return $string;
	}
	*/

}
?>