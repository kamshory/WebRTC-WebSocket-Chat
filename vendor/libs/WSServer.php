<?php
class WSServer implements WSInterface{
	public $chatClients = array();
	public $host = '127.0.0.1';
	public $port = 8888;
	public $masterSocket = NULL;
	public $clientSockets = array();
	public $dataChunk = 128;
	public $maxHeaderSize = 2048;

	public $sessionSavePath = '/';
	public $sessionFilePrefix = 'sess_';
	public $sessionCookieName = 'PHPSESSID';

	public function __construct($host = '127.0.0.1', $port = 8888)
	{
		$this->host = $host;
		$this->port = $port;

		$this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//stream_set_blocking($this->masterSocket, 0);
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
	
	public function run()
	{
		$index = 0;
		$null = NULL; //null var
		while (true) 
		{
			// manage multiple connections
			$changed = $this->clientSockets;
			// returns the socket resources in $changed array
			if(@socket_select($changed, $null, $null, 0, 10000) < 1)
			{
				continue;
			}
			// check for new socket
			if (in_array($this->masterSocket, $changed)) 
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
						$foundSocket = array_search($this->masterSocket, $changed);
						unset($changed[$foundSocket]);
					}
				}
			}
			if(is_array($changed))
			{
				//loop through all connected sockets
				foreach ($changed as $index => $changeSocket) 
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
					/*
					do
					{
						$buf1 = socket_read($changeSocket, $this->dataChunk,  PHP_NORMAL_READ);
						print_r($buf1);
						if($buf1 !== false)
						{
							$buffer .= $buf1;
							$nread++;
						}
						else
						{
						}
					}
					while($buf1 !== false);
					*/
					
									
						
						
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
	
	public function run3()
	{
   		$index = 0;
		while (true) 
		{
			// create a copy, so $this->masterSocket doesn't get modified by socket_select()
			$read = $this->clientSockets;
		   
			// get a list of all the clients that have data to be read from
			// if there are no clients with data, go to next iteration
			if (@socket_select($read, $write = NULL, $except = NULL, 0) < 1)
				continue;
		   
			// check if there is a client trying to connect
			if (in_array($this->masterSocket, $read)) 
			{
				// accept the client, and add him to the $this->clientSockets array
				$clientSocket = socket_accept($this->masterSocket);
			   	$this->clientSockets[$index] = $clientSocket;
				// send the client a welcome message
			   
				socket_getpeername($clientSocket, $ip);
				echo "New client connected: {$ip}\n";
			   
				// remove the listening socket from the clients-with-data array
				$key = array_search($this->masterSocket, $read);
				unset($read[$key]);
			}
		   
			// loop through all the clients that have data to read from
			foreach ($read as $read_sock) 
			{
				// read until newline or 1024 bytes
				// socket_read while show errors when the client is disconnected, so silence the error messages
				$data = @socket_read($read_sock, 1024, PHP_NORMAL_READ);
			   
				// check if the client is disconnected
				if ($data === false) 
				{
					// remove client for $this->clientSockets array
					
					$key = array_search($read_sock, $this->clientSockets);
					unset($this->clientSockets[$key]);
					echo "client disconnected.\n";
					// continue to the next client to read from, if any
					continue;
				}
			   
				// trim off the trailing/beginning white spaces
				$data = trim($data);
			   
				// check if there is any data after trimming off the spaces
				if (!empty($data)) {
			   
					// send this to all the clients in the $this->clientSockets array (except the first one, which is a listening socket)
					
					$header = $data;
					echo 'data = '.$data."\r\n";
					if(stripos($header, 'Sec-WebSocket-Key') !== false)
					{
						socket_getpeername($read_sock, $remoteAddress, $remotePort); //get ip address of connected socket
						echo "header = $header\r\n";
						$chatClient = new WSClient($index, $read_sock, $header, $remoteAddress, $remotePort, $this->sessionCookieName, $this->sessionSavePath, $this->sessionFilePrefix, $this, 'onClientLogin');
						$this->chatClients[$index] = $chatClient;
						echo "onOpen\r\n";
						$this->onOpen($chatClient);
						$foundSocket = array_search($this->masterSocket, $changed);
						unset($read[$foundSocket]);
					}
					
					foreach ($this->clientSockets as $send_sock) {
				   
						// if its the listening sock or the client that we got the message from, go to the next one in the list
						if ($send_sock == $this->masterSocket || $send_sock == $read_sock)
							continue;
					   
						// write the message to the client -- add a newline character to the end of the message
						socket_write($send_sock, $data."\n");
					   
					} // end of broadcast foreach
				   
				}
				else
				{
					
				}
			   
			} // end of reading foreach
		}
	
		// close the listening socket
		socket_close($this->masterSocket);
	}
	
	
	
	
	
	
	public function seal($data) 
	{
		return $this->hybi10Encode($data);
	}
	public function unseal($data) 
	{
		$decodedData = $this->hybi10Decode($data);
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
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->sock);
	}
}


?>