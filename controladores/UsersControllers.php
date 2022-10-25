<?php

require_once 'modelos/UsersModel.php';
require_once 'ResponseControlador.php';

class UsersControllers
{
   private $response;
   private $modelo;

   public function __construct()
   {
      $this->response = new ResponseControlador();
      $this->modelo = new UsersModel();
   }


   public function thereIsUser($username)
   {
      if($this->modelo->thereIsUser($username) > 0)
      {
         $this->response->generateResponse(409 , false  , 'Username already exists');
      }

      return true;

   }


   // $fullname , $username , $hashed_password
   public function insertUser($fullname , $username , $hashed_password)
   {
      if($this->modelo->insertUser($fullname , $username , $hashed_password) === 0)
      {
         $this->response->generateResponse(500 , false ,  'There was an error creating the user account - please try again');
      }


      $this->response->generateResponse(201 , true ,  'User created');


   }


   public function errorMethod($status , $sucess , $message)
   {
      $this->response->generateResponse(405 , false , $message);
   }
}


?>
