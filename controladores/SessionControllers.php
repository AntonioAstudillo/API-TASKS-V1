<?php

require_once 'modelos/SessionModelo.php';
require_once 'ResponseControlador.php';

class SessionControllers
{
   private $response;
   private $modelo;

   public function __construct()
   {
      $this->response = new ResponseControlador();
      $this->modelo = new SessionModelo();
   }

   public function errorMethod($status , $sucess , $message)
   {
      $this->response->generateResponse(405 , false , $message);
   }

   public function existsUser($username)
   {

      if(!$row = $this->modelo->existsUser($username))
      {
         $this->errorMethod(401 , false , 'Username or password is incorrect');
      }

      return $row;
   }


   public function updateAttempts($id)
   {
      return $this->modelo->updateAttempts($id);
   }

   public function truncateAttempts($id)
   {
      return $this->modelo->truncateAttempts($id);
   }


   public function generarToken($returned_id , $access_token_expiry_seconds , $refresh_token_expiry_seconds , $refreshtoken  , $accesstoken)
   {
      $returnData = $this->modelo->generarToken($returned_id , $access_token_expiry_seconds , $refresh_token_expiry_seconds , $refreshtoken  , $accesstoken);

      if($returnData)
      {
         $this->response->setHttpStatusCode(201);
         $this->response->setSuccess(true);
         $this->response->setData($returnData);
         $this->response->send();
         exit;
      }
      else {
         $this->response->setHttpStatusCode(500);
         $this->response->setSuccess(false);
         $this->response->addMessage("There was an issue logging in - please try again");
         $this->response->send();
         exit;

      }
   }
}









 ?>
