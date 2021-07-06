<?php
require_once __DIR__.'/db/Connection.php';

class Counter extends Connection {

    private $today;
    private $client_ip;
    private const SHOW_PER_DAY_LIMIT = 30;

    public function __construct(){
        $this->today = date("Y-m-d");
        $this->client_ip = $this->get_client_ip();
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

    public function renderVisits(){
        $visits = $this->getVisitsInAssoc();
        echo "  <style>
                    body{
                        font-family: sans-serif;
                        text-align:center;
                    }        
                </style>
                <h1>Contador de visitas reales por día</h1>
                <h3>Datos de los últimos 30 días</h3>

                <table cellspacing='0' style='width:80%; border:1px solid black; margin:auto;'>
                    <tr>
                        <th style='border-bottom:1px solid black;border-right:1px solid black;'>Fecha</th>
                        <th style='border-bottom:1px solid black;'>Cantidad de visitas</th>
                    </tr>
            ";

            foreach ($visits as $visit) {
                echo "
                    <tr>
                        <td align='center' style='border-right:1px solid black;'>{$visit['date']}</td>
                        <td align='center'>{$visit['cont']}</td>
                    </tr>
                ";  
            }
            
        echo "</table>";
    }

    private function getVisitsInAssoc(){
        $this->connect();
        $datesQuery = "SELECT DISTINCT(date) FROM visits ORDER BY date DESC LIMIT ".self::SHOW_PER_DAY_LIMIT;
        $dateResults = $this->query($datesQuery);
        $res_in_array = array();

        if (mysqli_num_rows($dateResults) > 0) {
            while ($row = mysqli_fetch_assoc($dateResults)) {
                $current_date = $row['date'];
                $visits_per_day_query = "SELECT date, COUNT(*) as cont FROM `visits` WHERE date = '$current_date'";
                $visits_per_day_result = $this->query($visits_per_day_query);
                $visits_per_day_row = mysqli_fetch_assoc($visits_per_day_result);
                array_push($res_in_array, $visits_per_day_row);
            }
        }

        return $res_in_array;
    }
}