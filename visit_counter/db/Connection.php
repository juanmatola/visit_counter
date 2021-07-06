<?php
class Connection{

    private $server = "localhost";
    private $bd = "visit_counter_db";
    private $user = 'root';
    private $pass = '';
    private $conection;

    public function connect(){
        $this->connection = mysqli_connect( $this->server, $this->user, $this->pass, $this->bd) 
                            or die ("No se ha podido conectar al servidor de Base de datos");
    }

    public function query($sql){
        return mysqli_query($this->connection, $sql);
    }

}