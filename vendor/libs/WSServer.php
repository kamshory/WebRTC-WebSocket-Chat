<?php
class WSServer implements WSInterface{
	public $chatClients = array();
	public $host = '127.0.0.1';
	public $port = 8888;
	public $masterSocket = null;
	public $clientSockets = array();
	public $dataChunk = 128;
	public $maxHeaderSize = 2048;

	public $sessionSavePath = '/';
	public $sessionFilePrefix = 'sess_';
	public $sessionCookieName = 'PHPSESSID';

	private $changed;
	public function __construct($host = '127.0.0.1', $port = 8888)
	{
		$this->host = $host;
		$this->port = $port;

		$this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// stream_set_blocking($this->masterSocket, 0);
		// reuseable port
		socket_set_option($this->masterSocket, SOL_SOCKET, SO_REUSEADDR, 1);
		// bind socket to specified host
		socket_bind($this->masterSocket, 0, $this->port);
		// listen to port
		socket_listen($this->masterSocket);
		$this->clientSockets = array($this->masterSocket);
		$this->sessionSavePath = session_save_path();
		echo "Server started ad ".$this->port."\r\n";
	}
	
	public function run() // NOSONAR
	{
		$index = 0;
		$null = null; //null var
		while (true) 
		{
			// manage multiple connections
			$this->changed = $this->clientSockets;
			// returns the socket resources in $this->changed array
			if(@socket_select($this->changed, $null, $null, 0, 10000) < 1)
			{
				continue;
			}
			// check for new socket
			if (in_array($this->masterSocket, $this->changed)) 
			{
				$clientSocket = socket_accept($this->masterSocket); //accpet new socket
				//stream_set_blocking($clientSocket, 0);
				$header = socket_read($clientSocket, $this->maxHeaderSize); //read data sent by the socket
				$header = trim($header, " \r\n ");
				if(strlen($header) > 2)
				{
					if(stripos($header, 'Sec-WebSocket-Key') !== false)
					{
						$index++;
						socket_getpeername($clientSocket, $remoteAddress, $remotePort); //get ip address of connected socket
						$chatClient = new WSClient($index, $clientSocket, $header, $remoteAddress, $remotePort, $this->sessionCookieName, $this->sessionSavePath, $this->sessionFilePrefix, $this, 'onClientLogin');
						$this->clientSockets[$index] = $clientSocket; //add socket to client array
						$this->chatClients[$index] = $chatClient;
						$this->onOpen($chatClient);
						$foundSocket = array_search($this->masterSocket, $this->changed);
						unset($this->changed[$foundSocket]);
					}
				}
			}
			if(is_array($this->changed))
			{
				//loop through all connected sockets
				foreach ($this->changed as $index => $changeSocket) 
				{
					//check for any incomming data
					
					$buffer = '';
					$buf1 = '';
					$nread = 0;
					do
					{
						$recv = @socket_recv($changeSocket, $buf1, $this->dataChunk, 0); 
						if($recv > 1)
						{
							$nread++;
							$buffer .= $buf1;
							if($recv < $this->dataChunk || $recv === false)
							{
								break;
							}
						}
						else
						{
							break;
						}
					}
					while($recv > 0);
						
					if($nread > 0)
					{
						if(strlen($buffer) > 0)
						{
							socket_getpeername($changeSocket, $ip, $port); 
							$decodedData = Utility::unmask($buffer); 
							if(isset($decodedData['type']))
							{
								if($decodedData['type'] == 'close')
								{
									break;
								}
								else
								{
									$this->onMessage($this->chatClients[$index], $decodedData['payload']);
									break;
								}
							}
							else
							{
								break;
							}
						}
					}
					$buf2 = @socket_read($changeSocket, $this->dataChunk, PHP_NORMAL_READ);
					if ($buf2 === false) 
					{ 
						// check disconnected client
						// remove client for $clientSockets array
						$foundSocket = array_search($changeSocket, $this->clientSockets);
						if(isset($this->chatClients[$foundSocket]))
						{
							$closeClient = $this->chatClients[$foundSocket];
							unset($this->clientSockets[$foundSocket]);
							unset($this->chatClients[$foundSocket]);
							$this->onClose($closeClient);
						}
					}
				}
			}
		}
	}
	
	

	public function seal($data) 
	{
		return Utility::hybi10Encode($data);
	}
	public function unseal($data) 
	{
		$decodedData = Utility::hybi10Decode($data);
		return $decodedData['payload'];
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
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->masterSocket);
	}
}
