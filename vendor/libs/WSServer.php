<?php
class WSServer implements WSInterface{
	public $chatClients = array();
	public $host = '127.0.0.1';
	public $port = 8888;
	public $masterSocket = NULL;
	public $clientSockets = array();
	public $dataChunk = 1024;
	public $maxHeaderSize = 2048;

	public $sessionSavePath = '/';
	public $sessionFilePrefix = 'sess_';
	public $sessionCookieName = 'PHPSESSID';

	public function __construct($host = '127.0.0.1', $port = 8888)
	{
		$this->host = $host;
		$this->port = $port;
		/*
		$this->masterSocket = stream_socket_server("tcp://$host:$port", $errno, $errstr);
		$this->clientSockets = array($this->masterSocket);
		*/
		$this->sessionSavePath = session_save_path();
		echo "Server started ad ".$this->port."\r\n";
	}
	private $connectionIndex = 0;
	public function handleClientSocket($clientSocket)
	{
		$header = fread($clientSocket, 1024);
		print_r($header);
		$this->connectionIndex++;
		$this->clientSockets[$this->connectionIndex] = $clientSocket;
	}
	public function run()
	{
		$host = $this->host;
		$port = $this->port;
		$_w = NULL;
		$_e = NULL;
		$this->masterSocket = stream_socket_server("tcp://$host:$port", $errno, $errstr);
		if (!$this->masterSocket) 
		{
			echo "$errstr ($errno)<br />\n";
		} 
		else 
		{
			stream_set_blocking($this->masterSocket, 0);
			$this->clientSockets[0] = $this->masterSocket;
			$read = $this->clientSockets;
			while (true) 
			{
				$read = $this->clientSockets;
				$mod_fd = stream_select($read, $_w, $_e, 0, 10);
				
				if ($mod_fd === FALSE) 
				{
					break;
				}
				if($mod_fd === 0)
				{
					continue;
				}
				for ($i = 0; $i < $mod_fd; ++$i) 
				{
					if(!isset($read[$i]))
					{
						continue;
					}
					if ($read[$i] === $this->masterSocket) 
					{
						$clientSocket = stream_socket_accept($this->masterSocket);	
						stream_set_blocking($clientSocket, 0);
						$this->handleClientSocket($clientSocket);
					} 
					else 
					{
						if($read[$i] === NULL)
						{
							continue;
						}
						$sock_data = fread($read[$i], 1024);
						if (strlen($sock_data) === 0) 
						{ // connection closed
							$key_to_del = array_search($read[$i], $this->clientSockets, TRUE);
							fclose($read[$i]);
							unset($this->clientSockets[$key_to_del]);
						} 
						else if ($sock_data === FALSE) 
						{
							echo "Something bad happened";
							$key_to_del = array_search($read[$i], $this->clientSockets, TRUE);
							unset($this->clientSockets[$key_to_del]);
						} 
						else 
						{
							echo "The client has sent :"; var_dump($sock_data);
							fwrite($read[$i], "You have sent :[".$sock_data."]\n");
							fclose($read[$i]);
							unset($this->clientSockets[array_search($read[$i], $this->clientSockets)]);
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
			foreach ($read as $read_sock) {
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