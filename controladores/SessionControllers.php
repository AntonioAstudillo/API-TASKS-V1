<?php

require_once 'modelos/SessionModelo.php';
require_once 'ResponseControlador.php';

class SessionControllers
{
   private $response;
   private $modelo;

   public function __construct()
   {
      //formatemos el timezone para trabajar bajo la hora de la ciudad de Mexico
      date_default_timezone_set('America/Mexico_City');
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
         $this->response->generateResponse(201 , true , null , null , $returnData);
      }
      else {
         $this->response->generateResponse(500 , false , 'There was an issue logging in - please try again' , null , null);
      }
   }

   public function deleteSession($id)
   {
      if($this->modelo->deleteSession($id) > 0)
      {
         $this->response->generateResponse(200 , true , 'Session delete success' , null , null);
      }else{
         $this->response->generateResponse(404 , false , 'Session not found' , null , null);
      }
   }

   /**
    * [validarAccessToken Con esta funcion vamos a validar que el token que utilicen en el header corresponda con alguno que tengamos registrado en la database
    *  y tambien que dicho token no esté ya expirado]
    * @param  [string] $token               [token que nos envian desde el header]
    * @return [boolean]        [Este metodo retorna true en caso de que el token sea correcto y en caso que no, mandamos un mensaje de error]
    */

   public function validarAccessToken($token)
   {
      if(!$resultado = $this->modelo->getDataSession($token) )
      {
         $this->errorMethod(401 , false , 'Access Token incorrect');
      }

      //Comprobamos que el tiempo de vida del token no sea menor al tiempo actual
      $fechaToken = strtotime($resultado['accesstokenexpiry']);
      $fechaActual = strtotime(date("Y-m-d H:i:00" , time() ) );

      if($fechaActual > $fechaToken)
      {
         $this->errorMethod(403 , false , 'Token expired');
      }

      return true;

   }

   
   public function updateToken($tokenRefresh)
   {
      // //antes de actualizar, comprobamos que el token exista
      //
      if(!$resultado = $this->modelo->findTokenRefresh($tokenRefresh))
      {
         $this->errorMethod(404 , false , 'Token refresh not found');
      }

      //ahora comprobamos que el token no este expirado
      $fechaToken = strtotime($resultado['refreshtokenexpiry']);
      $fechaActual = strtotime(date("Y-m-d H:i:00" , time() ) );

      if($fechaActual > $fechaToken)
      {
         $this->errorMethod(403 , false , 'Token refresh expired');
      }


      //si el token refresh existe y aun no expira, procedemos a actualizar ambos tokens y a retornar la respuesta
      //generamos valores
      $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
      $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
      $access_token_expiry_seconds = 1200;
      $refresh_token_expiry_seconds = 1209600;


      if( !$this->modelo->updateRefreshToken($resultado['id'] ,  $accesstoken ,  $refreshtoken , $access_token_expiry_seconds , $refresh_token_expiry_seconds ) > 0 )
      {
         $this->errorMethod(400 , false , 'Token refresh not update');
      }

      //generamos el json con toda la informacion sobre la actualizacion
      $returnData = array();
      $returnData['session_id'] = intval($resultado['id']);
      $returnData['access_token'] = $accesstoken;
      $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
      $returnData['refresh_token'] = $refreshtoken;
      $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;


      $this->response->generateResponse(205 , true , null , null , $returnData);
   }
}









 ?>
