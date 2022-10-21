<?php

require_once 'Config.php';

class Mysql extends Exception
{
   protected $conexion;


   public function __construct()
   {
      try
      {
         $dsn = "mysql:dbname=" . DATABASE . ";host=" . HOST . "";
         $this->conexion = new PDO($dsn, USER, PASSWORD);
         $this->conexion->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
         $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES , false);

      }
      catch(PDOException $e)
      {
         throw new Exception('505');
      }
   }

   public function getConexion()
   {
      return $this->conexion;
   }


}








 ?>
