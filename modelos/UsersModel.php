<?php

require_once 'Mysql.php';

class UsersModel extends Mysql
{


   public function __construct()
   {
      parent::__construct();
   }

   public function thereIsUser($username)
   {
      $query = $this->conexion->prepare('SELECT id from users where username = :username');
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->execute();

      return $query->rowCount();
   }


   public function insertUser($fullname , $username , $hashed_password)
   {
      $useractive = 'Y';

      $query = $this->conexion->prepare('INSERT into users (fullname, username, password , useractive) values (:fullname, :username, :password , :useractive)');
      $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
      $query->bindParam(':useractive', $useractive , PDO::PARAM_STR);
      $query->execute();

      return $query->rowCount();
   }

}

 ?>
