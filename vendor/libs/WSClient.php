<?php
class WSClient{
	public $socket;
	public $remoteAddress = '';
	public $remotePort = 0;
	public $headers = array();
	public $cookies = array();
	public $sessions = array();
	public $sessionSavePath = '/';
	public $sessionFilePrefix = 'sess_';
	public $sessionCookieName = 'PHPSESSID';
	public $sessionID = '';
	public $resourceID = 0;
	public $httpVersion = '';
	public $method = '';
	public $uri = '';
	public $path = '';
	public $query = array();
	public $clientData = array();
	public function __construct($resourceID, $socket, $headers, $remoteAddress = NULL, $remotePort = NULL, $sessionCookieName = 'PHPSESSID', $sessionSavePath = NULL, $sessionFilePrefix = 'sess_', $obj, $loginCallback)
	{
		$this->resourceID = $resourceID;
		$this->socket = $socket;
		$this->remoteAddress = $remoteAddress;
		$this->remotePort = $remotePort;
		if($sessionSavePath === NULL)
		{
			$this->sessionSavePath = session_save_path();
		}
		if($sessionCookieName !== NULL)
		{
			$this->sessionCookieName = $sessionCookieName;
		}
		if($sessionFilePrefix !== NULL)
		{
			$this->sessionFilePrefix = $sessionFilePrefix;
		}
		$headerInfo = Utility::parseHeaders($headers);
		$this->headers = $headerInfo['headers'];
		$this->method = $headerInfo['method'];
		$this->uri = $headerInfo['uri'];
		$this->path = $headerInfo['path'];
		$this->query = $headerInfo['query'];
		$this->httpVersion = $headerInfo['version'];
		if(isset($this->headers['cookie']))
		{
			$this->cookies = Utility::parseCookie($this->headers['cookie']);
		}
		if(isset($this->cookies[$this->sessionCookieName]))
		{
			$this->sessionID = $this->cookies[$this->sessionCookieName];
			$this->sessions = Utility::getSessions($this->sessionID, $this->sessionSavePath, $this->sessionFilePrefix);
		}

		$this->sessions = Utility::getSessions($this->sessionID, $sessionSavePath, $sessionFilePrefix);
		
		$this->clientData = call_user_func(array($obj, $loginCallback), $this); 
	}
	public function send($message)
	{
		$maskedMessage = Utility::mask($message);
		@socket_write($this->socket, $maskedMessage, strlen($maskedMessage));
	}
	public function login()
	{
		return true;
	}
	public function parseCookie($cookie_string)
	{
		$cookie_data = array();
		$arr = explode("; ", $cookie_string);
		foreach($arr as $key=>$val)
		{
			$arr2 = explode("=", $val, 2);
			$cookie_data[$arr2[0]] = $arr2[1];
		}
		return $cookie_data;
	}  

}
?>