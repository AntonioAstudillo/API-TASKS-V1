<?php

//creamos un objeto controlador
require_once 'controladores/UsersControllers.php';

$objeto = new UsersControllers();

//Comprobamos que la peticion se haga por medio de un post
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
  $objeto->errorMethod(405 , false , 'Request method not allowed 333');
}

//comprobamos que el contenido sea un json
if(isset($_SERVER['CONTENT_TYPE']) &&  $_SERVER['CONTENT_TYPE'] !== 'application/json')
{
   $objeto->errorMethod(400 , false , 'Content Type header not set to JSON');
}


// Obtenemos el contenido que nos envien en el body
$rawPostData = file_get_contents('php://input');


//si no es un json valido
if(!$jsonData = json_decode($rawPostData)) {
  // set up response for unsuccessful request
  $objeto->errorMethod(400 , false , 'Request body is not valid JSON');
}

// comprobamos que los valores que nos tienen que enviar esten seteados
if(!isset($jsonData->fullname) || !isset($jsonData->username) || !isset($jsonData->password))
{
  // add message to message array where necessary
  $message = 'Credentials incompleted';
  $objeto->errorMethod(400 , false , $message);

}

// validamos que las longitudes de los datos sean correctas
if(strlen($jsonData->fullname) < 1 || strlen($jsonData->fullname) > 255 || strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 100) {
  $objeto->errorMethod(400 , false , 'Credentials are not good');
}

// limpiamos todos los datos
$fullname = trim($jsonData->fullname);
$username = trim($jsonData->username);
$password = trim($jsonData->password);


if($objeto->thereIsUser($username))
{
   $hashed_password = password_hash($password, PASSWORD_DEFAULT);
}

/*Si los valores que nos envinan en el json son correctos, procedemos a almacenarlos en la database */
$objeto->insertUser($fullname , $username , $hashed_password);















 ?>
