<?php

namespace app\models;

abstract class Connection
{
  private $host = 'localhost';
  private $dbname = 'mvc';
  private $user = 'root'; 
  private $pass = '@Sucesso2022@'; 

  protected function connect()
  {
    try {
      $dsn = "mysql:host=$this->host;dbname=$this->dbname";
      $conn = new \PDO($dsn, $this->user, $this->pass);
      $conn->exec("set names utf8");
      return $conn;
    } catch (\PDOException $error) {
      echo 'Erro: ' . $error->getMessage();
    }
  }
}