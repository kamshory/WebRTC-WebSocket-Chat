<?php
class WSServer implements WSInterface{
	public $chatClients = array();
	public $host = '127.0.0.1';
	public $port = 8888;
	public $socket = NULL;
	public $clientSockets = array();
	public $maxDataSize = 4096;

	public $sessionSavePath = '/';
	public $sessionFilePrefix = 'sess_';
	public $sessionCookieName = 'PHPSESSID';

	public function __construct($host = '127.0.0.1', $port = 8888)
	{
		$this->host = $host;
		$this->port = $port;

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// reuseable port
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		// bind socket to specified host
		socket_bind($this->socket, 0, $this->port);
		// listen to port
		socket_listen($this->socket);
		$this->clientSockets = array($this->socket);
		$this->sessionSavePath = session_save_path();
	}
	public function unseal($socketData) {
		$length = ord($socketData[1]) & 127;
		if($length == 126) {
			$masks = substr($socketData, 4, 4);
			$data = substr($socketData, 8);
		}
		elseif($length == 127) {
			$masks = substr($socketData, 10, 4);
			$data = substr($socketData, 14);
		}
		else {
			$masks = substr($socketData, 2, 4);
			$data = substr($socketData, 6);
		}
		$socketData = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socketData .= $data[$i] ^ $masks[$i%4];
		}
		return $socketData;
	}

	public function seal($socketData) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socketData);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$socketData;
	}
	public function run()
	{
		$index = 0;
		$null = NULL; //null var
		while (true) 
		{
			// manage multiple connections
			$changed = $this->clientSockets;
			// returns the socket resources in $changed array
			@socket_select($changed, $null, $null, 0, 10);
			// check for new socket
			if (in_array($this->socket, $changed)) 
			{
				$socketNew = socket_accept($this->socket); //accpet new socket
				$index++;
				$header = socket_read($socketNew, $this->maxDataSize); //read data sent by the socket
				$this->performHandshaking($header, $socketNew, $this->host, $this->port); //perform websocket handshake
				socket_getpeername($socketNew, $ip, $port); //get ip address of connected socket
				$chatClient = new WSClient($index, $socketNew, $header, $ip, $port, $this->sessionCookieName, $this->sessionSavePath, $this->sessionFilePrefix, $this, 'onClientLogin');

				$this->clientSockets[$index] = $socketNew; //add socket to client array
				$this->chatClients[$index] = $chatClient;
				$this->onOpen($chatClient);
				
				//make room for new socket
				$foundSocket = array_search($this->socket, $changed);
				unset($changed[$foundSocket]);
			}
			if(is_array($changed))
			{
				//loop through all connected sockets
				foreach ($changed as $index => $changeSocket) 
				{
					//check for any incomming data
					$buffer = '';
					$buf1 = '';
					
					
					/*
					while(@socket_recv($changeSocket, $buf1, $this->maxDataSize, 0) > 1) 
					{
						socket_getpeername($changeSocket, $ip, $port); 
						$receivedText = Utility::unmask($buf1); 
						$this->onMessage($this->chatClients[$index], $receivedText);
						break 2;
					}
					*/
					while(socket_recv($changeSocket, $socketData, 1024, 0) >= 1){
						$socketMessage = $this->unseal($socketData);
						socket_getpeername($changeSocket, $ip, $port);
						$this->onMessage($this->chatClients[$index], $socketMessage);
						break 2;
					}
					
					$buf2 = @socket_read($changeSocket, $this->maxDataSize, PHP_NORMAL_READ);
					if ($buf2 === false) 
					{ 
						// check disconnected client
						// remove client for $clientSockets array
						$foundSocket = array_search($changeSocket, $this->clientSockets);
						@socket_getpeername($changeSocket, $ip, $port);
						$closeClient = $this->chatClients[$foundSocket];
						unset($this->clientSockets[$foundSocket]);
						unset($this->chatClients[$foundSocket]);
						$this->onClose($closeClient);
					}
				}
			}
		}
	}
	public function onClientLogin($clientChat)
	{
	}
	/**
	 * Method when a new client is connected
	 * @param $clientChat Chat client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onOpen($clientChat)
	{
	}
	/**
	 * Method when a new client is disconnected
	 * @param $clientChat Chat client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onClose($clientChat)
	{
	}
	/**
	 * Method when a client send the message
	 * @param $clientChat Chat client
	 * @param $receivedText Text sent by the client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onMessage($clientChat, $receivedText)
	{
	}
	/**
	 * Method to send the broadcast message to all client
	 * @param $message Message to sent to all client
	 */
	public function sendBroadcast($message)
	{
		foreach($this->chatClients as $client) 
		{
			$client->send($message);
		}
	}
	/**
	 * Handshake new client
	 * @param $recevedHeader Request header sent by the client
	 * @param $client_conn Client connection
	 * @param $host Host name of the websocket server
	 * @param $port Port number of the websocket server
	 */
	public function performHandshaking($recevedHeader, $client_conn, $host, $port)
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
				. "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" 
				. "WebSocket-Origin: $host\r\n" 
				. "WebSocket-Location: ws://$host:$port\r\n" 
				. "Sec-WebSocket-Accept: $secAccept\r\n"
				. "X-Engine: PlanetChat\r\n\r\n";
			socket_write($client_conn, $upgrade, strlen($upgrade));
		}
	}
	/**
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->sock);
	}
}


?>