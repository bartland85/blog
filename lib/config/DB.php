<?php
/**
 * Created by PhpStorm.
 * User: Bart
 * Date: 2015-04-18
 * Time: 23:33
 */

namespace Config;


class DB {

    private $dbhost;
    private $dbname;
    private $dbuser;
    private $dbpass;
    private $db;

    function __construct($dbhost = _DBHOST, $dbname = _DBNAME, $dbuser = _DBUSER, $dbpass = _DBPASS)
    {
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
    }

    public function connect(){
        $this->db = new PDO('mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname , $this->dbuser);
    }

    public function query($query, $parameters = array()){

        $q = $this->db->prepare($query);

        $q->execute($parameters);

        $f = $q->fetch();

        return $f;
    }



}