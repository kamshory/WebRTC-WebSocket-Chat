<?php
class RemoteConnection {
    private $host = "";
    private $port = 0;
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function getHost()
    {
        return $this->host;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
}