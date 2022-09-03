<?php
namespace model;
use \PDO;
use PDOException;

class Database {
  // DB Params
  private $host = 'localhost';
  private $db_name = 'sgam';
  private $username = 'admin01';
  private $password = '23111997vitor';
  private $conn;

  // DB Connect

  public function _construct()
  {
    return $this->connect();
  }

  public function connect() {
    $this->conn = null;

    try {
      
      $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name,
      $this->username, $this->password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      echo 'Connection Error: ' . $e->getMessage();
    }

    return $this->conn;
  }
  
}



?>
