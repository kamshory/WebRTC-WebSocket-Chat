<?php
class WSClient{
	private $socket;
	private $remoteConnection;
	private $headers = array();
	private $cookies = array();
	private $sessions = array();
	private $sessionSavePath = '/';
	private $sessionFilePrefix = 'sess_';
	private $sessionCookieName = 'PHPSESSID';
	private $sessionID = '';
	private $resourceId = 0;
	private $httpVersion = '';
	private $method = '';
	private $uri = '';
	private $path = '';
	private $query = array();
	private $clientData = array();

	private $host = "";
	private $port = 0;

	private $headerInfo = array();

	private $groupId = "";
	public $sessionParams;

	/**
	 * Get client data
	 * @return array
	 */
	public function getClientData()
	{
		return $this->clientData;
	}
	/**
	 * Get client data
	 * @return array
	 */
	public function getSessions()
	{
		return $this->sessions;
	}
	/**
	 * @param string $resourceId, 
	 * @param Socket $socket
	 * @param string $headers
	 * @param \RemoteConnection $remoteConnection
	 * @param SessionParams $sessionParams 
	 * @param \WSServer $obj,
	 * @param string $loginCallback
	 */
	public function __construct($resourceId, $socket, $headers, $remoteConnection, $sessionParams, $obj, $loginCallback)
	{
		$this->resourceId = $resourceId;
		$this->socket = $socket;
		$this->remoteConnection = $remoteConnection;
		
		$headerInfo = Utility::parseRawHeaders($headers);

		$this->parseHeaders($headerInfo);	
		
		$this->performHandshaking($headers, $this->host, $this->port);
		
		if(isset($this->headers['cookie']))
		{
			$this->cookies = Utility::parseCookie($this->headers['cookie']);
		}
		if(isset($this->cookies[$this->sessionCookieName]))
		{
			$this->sessionID = $this->cookies[$this->sessionCookieName];

		}
		if($sessionParams === null)
		{
			$this->sessionParams = new SessionParams(null, session_save_path(), null);
		}
		else
		{
			$this->sessionParams = $sessionParams;
		}

		$this->sessions = Utility::getSessions($this->sessionID, $this->sessionParams);
		
		$this->clientData = call_user_func(array($obj, $loginCallback), $this); 
	}

	private function parseHeaders($headerInfo)
	{
		$port = 0;
		$host = "";

		$this->headerInfo = $headerInfo;

		$headers = $headerInfo['headers'];

		$this->headers = $headerInfo['headers'];
		$this->method = $headerInfo['method'];
		$this->uri = $headerInfo['uri'];
		$this->path = $headerInfo['path'];
		$this->query = $headerInfo['query'];
		$this->httpVersion = $headerInfo['version'];

		if(isset($headers['x-forwarded-host']))
		{
			$host = $headers['x-forwarded-host'];
		}
		else if(isset($headers['x-forwarded-server']))
		{
			$host = $headers['x-forwarded-server'];
		}
		else
		{
			$host = $headers['host'];
		}
		if(stripos($host, ":") !== false)
		{
			$arrHost = explode(":", $host);
			$host = $arrHost[0];
			$port = (int)$arrHost[1];
		}
		else
		{
			$port = 443;
		}
		$this->host = $host;
		$this->port = $port;
	}

	public function getGroupId()
	{
		return $this->groupId;
	}
	public function setGroupId($groupId)
	{
		return $this->groupId = $groupId;
	}

	public function getResourceId()
	{
		return $this->resourceId;
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