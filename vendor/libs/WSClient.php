<?php
class WSClient{
	private $socket;
	private $remoteConnection;
	private $headers = array();
	private $cookies = array();
	private $sessions = array();
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
			$this->cookies = Utility::parseRawCookies($this->headers['cookie']);
		}

		if($sessionParams === null)
		{
			$this->setSessionParams(new SessionParams(null, session_save_path(), null));
		}
		else
		{
			$this->setSessionParams($sessionParams);
		}
		$sessionName = $this->getSessionParams()->getSessionCookieName();
		if(isset($this->cookies[$sessionName]))
		{
			$this->setSessionID($this->cookies[$sessionName]);
		}

		$this->setSessions(Utility::getSessions($this->getSessionID(), $this->getSessionParams()));
		
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
			$port = (int) $arrHost[1];
		}
		else
		{
			$port = 443;
		}
		$this->host = $host;
		$this->port = $port;	
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
			socket_write($this->socket, $upgrade, strlen($upgrade));
		}
	}
	

	public function login()
	{
		return true;
	}



	/**
	 * Get the value of sessionParams
	 * @return \SessionParams
	 */ 
	public function getSessionParams()
	{
		return $this->sessionParams;
	}

	/**
	 * Set the value of sessionParams
	 * @param \SessionParams $sessionParams
	 * @return  self
	 */ 
	public function setSessionParams($sessionParams)
	{
		$this->sessionParams = $sessionParams;

		return $this;
	}

	/**
	 * Get the value of headerInfo
	 * @return array
	 */ 
	public function getHeaderInfo()
	{
		return $this->headerInfo;
	}

	/**
	 * Set the value of headerInfo
	 * @param array $headerInfo
	 *
	 * @return  self
	 */ 
	public function setHeaderInfo($headerInfo)
	{
		$this->headerInfo = $headerInfo;

		return $this;
	}

	/**
	 * Get the value of remoteConnection
	 */ 
	public function getRemoteConnection()
	{
		return $this->remoteConnection;
	}

	/**
	 * Set the value of remoteConnection
	 *
	 * @return  self
	 */ 
	public function setRemoteConnection($remoteConnection)
	{
		$this->remoteConnection = $remoteConnection;

		return $this;
	}

	/**
	 * Get the value of resourceId
	 * @return int
	 */ 
	public function getResourceId()
	{
		return $this->resourceId;
	}

	/**
	 * Set the value of resourceId
	 *
	 * @return  self
	 */ 
	public function setResourceId($resourceId)
	{
		$this->resourceId = $resourceId;

		return $this;
	}

	/**
	 * Get the value of httpVersion
	 * @return string
	 */ 
	public function getHttpVersion()
	{
		return $this->httpVersion;
	}

	/**
	 * Set the value of httpVersion
	 *
	 * @return  self
	 */ 
	public function setHttpVersion($httpVersion)
	{
		$this->httpVersion = $httpVersion;

		return $this;
	}

	/**
	 * Get the value of method
	 * @return string
	 */ 
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set the value of method
	 *
	 * @return  self
	 */ 
	public function setMethod($method)
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Get the value of uri
	 * @return string
	 */ 
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Set the value of uri
	 *
	 * @return  self
	 */ 
	public function setUri($uri)
	{
		$this->uri = $uri;

		return $this;
	}

	/**
	 * Get the value of groupId
	 * @return string
	 */ 
	public function getGroupId()
	{
		return $this->groupId;
	}

	/**
	 * Set the value of groupId
	 *
	 * @return  self
	 */ 
	public function setGroupId($groupId)
	{
		$this->groupId = $groupId;

		return $this;
	}

	/**
	 * Get the value of path
	 */ 
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set the value of path
	 *
	 * @return  self
	 */ 
	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * Get the value of query
	 * @return array
	 */ 
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Set the value of query
	 *
	 * @return  self
	 */ 
	public function setQuery($query)
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * Get the value of sessionID
	 * @return string
	 */ 
	public function getSessionID()
	{
		return $this->sessionID;
	}

	/**
	 * Set the value of sessionID
	 *
	 * @return  self
	 */ 
	public function setSessionID($sessionID)
	{
		$this->sessionID = $sessionID;

		return $this;
	}

	/**
	 * Get the value of sessions
	 */ 
	public function getSessions()
	{
		return $this->sessions;
	}

	/**
	 * Set the value of sessions
	 *
	 * @return  self
	 */ 
	public function setSessions($sessions)
	{
		$this->sessions = $sessions;

		return $this;
	}

	/**
	 * Get the value of clientData
	 * @return array
	 */ 
	public function getClientData()
	{
		return $this->clientData;
	}

	/**
	 * Set the value of clientData
	 *
	 * @return  self
	 */ 
	public function setClientData($clientData)
	{
		$this->clientData = $clientData;

		return $this;
	}
}
