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
		else
		{
			$this->sessionSavePath = $sessionSavePath;
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
		
		print_r($this->headers);
		if(isset($this->headers['x-forwarded-host']))
		{
			$host = $this->headers['x-forwarded-host'];
		}
		else if(isset($this->headers['x-forwarded-server']))
		{
			$host = $this->headers['x-forwarded-server'];
		}
		else
		{
			$host = $this->headers['host'];
		}
		if(stripos($host, ":") !== false)
		{
			$arrHost = explode(":", $host);
			$host = $arrHost[0];
			$port = $arrHost[1];
		}
		else
		{
			$port = "443";
		}
		
		$this->performHandshaking($headers, $host, $port);
		
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
	/**
	 * Handshake new client
	 * @param $recevedHeader Request header sent by the client
	 * @param $client_conn Client connection
	 * @param $host Host name of the websocket server
	 * @param $port Port number of the websocket server
	 */
	public function performHandshaking($recevedHeader, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $recevedHeader);
		foreach ($lines as $line) 
		{
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) 
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		if(isset($headers['Sec-WebSocket-Key']))
		{
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			//hand shaking header
			$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" 
				. "Upgrade: websocket\r\n" 
				. "Connection: Upgrade\r\n" 
				. "WebSocket-Origin: $host\r\n" 
				. "WebSocket-Location: ws://$host:$port\r\n" 
				. "Sec-WebSocket-Accept: $secAccept\r\n"
				. "Access-Control-Allow-Origin: *\r\n"
				. "X-Engine: PlanetChat\r\n\r\n";
//				echo $recevedHeader."\r\n\r\n";
//				echo $upgrade;
			socket_write($this->socket, $upgrade, strlen($upgrade));
		}
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