<?php
require_once __DIR__.'/db/Connection.php';

class Counter extends Connection {

    private $today;
    private $client_ip;

    public function __construct(){
        $this->today = date("Y-m-d");
        $this->client_ip = $this->get_client_ip();
        $this->count();
    }

    private function get_client_ip() {
        
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        
        return $ipaddress;
    }

    public function count(){

        $this->connect();

        $this->alreadyVisited() ? $this->insertVisit() : NULL;

    }

    private function alreadyVisited(){

        $query = $this->query("SELECT * FROM visits WHERE date='".$this->today."' AND ip='".$this->client_ip ."'");

        return mysqli_num_rows($query) == 0;

    }

    private function insertVisit(){

        $this->query("INSERT INTO visits (ip, date, cont) VALUES ('".$this->client_ip."', '".$this->today."', 1)");
      
    }
}