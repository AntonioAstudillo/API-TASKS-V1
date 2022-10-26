<?php

   //creamos un objeto controlador
   require_once 'controladores/SessionControllers.php';

   $objeto = new SessionControllers();

   if(empty($_GET))
   {

      if($_SERVER['REQUEST_METHOD'] !== 'POST')
      {
         $objeto->errorMethod(405 , false , 'Request method not allowed');
      }

      //Ralentizamos el scrip para evitar ataques de fuerza bruta
      sleep(1);

      if(isset($_SERVER['CONTENT_TYPE']) AND  $_SERVER['CONTENT_TYPE'] !== 'application/json')
      {
         $objeto->errorMethod(400 , false , "Content Type header not set to JSON");
      }

      $rawPostData = file_get_contents('php://input');

      if(!$jsonData = json_decode($rawPostData))
      {
         // set up response for unsuccessful request
         $objeto->errorMethod(400 , false , "Request body is not valid JSON");
      }

      if(!isset($jsonData->username) || !isset($jsonData->password))
      {
         $mensaje =  (!isset($jsonData->username) ) ? "Username not supplied" : false;
         $mensaje =  (!isset($jsonData->password) ) ? "Password not supplied" : false;
         $objeto->errorMethod(400 , false , $mensaje);
      }

      //check to make sure that username and password are not empty and not greater than 255 characters
      if(strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255)
      {
         $mensaje = (strlen($jsonData->username) < 1  )  ?  "Username cannot be blank" : false;
         $mensaje = (strlen($jsonData->username) > 255 ) ?  "Username must be less than 255 characters" : false;
         $mensaje = (strlen($jsonData->password) < 1 )   ?  "Password cannot be blank" : false;
         $mensaje = (strlen($jsonData->password) > 255 ) ?  "Password must be less than 255 characters" : false;
         $objeto->errorMethod(400 , false , $mensaje);
      }


      //Aqui se supone que todo está correcto, asi que prosigo a realizar las querys a los modelos
       $username = $jsonData->username;
       $password = $jsonData->password;

   //si la logica no me falla, aqui no hago ningun condicional, debido a que el proceso lo realizo desde el controlador
   //en caso de que no exista informacion, desde el controlador mando directamente un mensaje de error y termino la ejecucion del script
   $row = $objeto->existsUser($username);


    // salvamos las variables
    $returned_id = $row['id'];
    $returned_fullname = $row['fullname'];
    $returned_username = $row['username'];
    $returned_password = $row['password'];
    $returned_useractive = $row['useractive'];
    $returned_loginattempts = $row['loginattempts'];

    // comprobamos que el usuario esté activo
    if($returned_useractive != 'Y')
    {
      $objeto->errorMethod(401 , false , 'User account is not active');
    }

    // checamos que la cuenta no este bloqueada
    if($returned_loginattempts >= 3)
    {
      $objeto->errorMethod(401 , false , 'User account is currently locked out');
    }

    //si el password ingresado por el usuario, no coincide con la contraseña almacenada, actualizamos el campo de intentos y mandamos mensaje de error
    if(!password_verify($password, $returned_password))
    {
      $objeto->updateAttempts($returned_id);
      $objeto->errorMethod(401 , false , 'Username or password is incorrect');
    }

    /*   ---------------------------------------------------------------------
         APARTIR DE AQUI, COMENZAMOS CON LA LOGICA DE LA GENERACIÓN DEL TOKEN
         ---------------------------------------------------------------------
     */

    $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

    $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
    $access_token_expiry_seconds = 1200;
    $refresh_token_expiry_seconds = 1209600;

    $objeto->generarToken($returned_id , $access_token_expiry_seconds , $refresh_token_expiry_seconds , $refreshtoken  , $accesstoken);



}//cierra if
else
{
  $objeto->errorMethod(404 , false , 'Endpoint not found');
}
