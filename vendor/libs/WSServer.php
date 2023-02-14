<?php
class WSServer implements WSInterface {
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

	private $sock;

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
	
	public function run() //NOSONAR
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
						$chatClient = new WSClient($index, $clientSocket, $header, new \RemoteConnection($remoteAddress, $remotePort), new \SessionParams($this->sessionCookieName, $this->sessionSavePath, $this->sessionFilePrefix), $this, 'onClientLogin');
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
						
					if($nread > 0)
					{
						if(strlen($buffer) > 0)
						{
							socket_getpeername($changeSocket, $ip, $port); 
							$decodedData = $this->hybi10Decode($buffer); 
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

	 /**
     * Encodes a frame/message according the the WebSocket protocol standard.     
     * @param $payload
     * @param $type
     * @param $masked
     * @throws \RuntimeException
     * @return string
     */
    public function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) 
        {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) 
        {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) 
            {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) 
            {
                //$this->close(1004);
                throw new \RuntimeException('Invalid payload. Could not encode frame.');
            }
        } 
		elseif ($payloadLength > 125) 
        {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } 
        else 
        {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) 
        {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) 
        {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) 
            {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) 
        {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }

    /**
     * Decodes a frame/message according to the WebSocket protocol standard.
     *
     * @param $data
     * @return array
     */
    public function hybi10Decode($data)
    {
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // close connection if unmasked frame is received:
        if ($isMasked === false) 
        {
        }

        switch ($opcode) 
        {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;
            case 2:
                $decodedData['type'] = 'binary';
                break;
            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;
            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;
            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;
        }

        if ($payloadLength === 126) 
        {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } 
        elseif ($payloadLength === 127) 
        {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) 
            {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } 
        else 
        {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) 
        {
            return array();
        }

        if ($isMasked === true) 
        {
            for ($i = $payloadOffset; $i < $dataLength; $i++) 
            {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) 
                {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } 
        else 
        {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }

	
	/*
	public function seal($data) 
	{
		return $this->hybi10Encode($data);
	}
	public function unseal($data) 
	{
		$decodedData = $this->hybi10Decode($data);
		return $decodedData['payload'];
	}
	*/

	public function onClientLogin($clientChat)
	{
	}
	/**
	 * Method when a new client is connected
	 * @param \WSClient $clientChat Chat client
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
	 * @param string $receivedText Text sent by the client
	 * @param string $ip Remote adddress or IP address of the client 
	 * @param int $port Remote port or port number of the client
	 */
	public function onMessage($clientChat, $receivedText)
	{
	}
	/**
	 * Method to send the broadcast message to all client
	 * @param \WSClient $clientChat Chat client
	 * @param string $message Message to sent to all client
	 * @param array $receiverGroups Receiver
	 * @param bool $meeToo
	 */
	public function sendBroadcast($clientChat, $message, $receiverGroups = null, $meeToo = false)
	{
		foreach($this->chatClients as $client) 
		{
			if($meeToo || ($clientChat->getResourceId() != $client->getResourceId() && ($receiverGroups == null || $this->groupReceive($receiverGroups, $client->getGroupId()))))
			{
				$client->send($message);
			}
		}
	}

	public function groupReceive($receiverGroups, $groupId)
	{
		return isset($receiverGroups) 
		&& is_array($receiverGroups) 
		&& isset($groupId) 
		&& in_array($groupId, $receiverGroups);
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->sock);
	}
}


?>