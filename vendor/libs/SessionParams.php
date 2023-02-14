<?php
class SessionParams
{
    private $sessionCookieName = 'PHPSESSID';
    private $sessionSavePath = "/";
    private $sessionFilePrefix = 'sess_';

    
    public function __construct($sessionCookieName = null, $sessionSavePath = null, $sessionFilePrefix = null)
    {
        if($sessionCookieName != null)
        {
            $this->sessionCookieName = $sessionCookieName;
        }
        if($sessionSavePath != null)
        {
            $this->sessionSavePath = $sessionSavePath;
        }
        if($sessionFilePrefix != null)
        {
            $this->sessionFilePrefix = $sessionFilePrefix;
        }
    }


    public function getSessionCookieName()
    {
        return $this->sessionCookieName;
    }

    public function getSessionSavePath()
    {
        return $this->sessionSavePath;
    }

    public function setSessionSavePath($sessionSavePath)
    {
        $this->sessionSavePath = $sessionSavePath;
    }

    public function getSessionFilePrefix()
    {
        return $this->sessionFilePrefix;
    }

    
    
}