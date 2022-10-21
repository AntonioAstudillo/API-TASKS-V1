<?php


   //Aqui vamos a recibir todas las peticiones
   require_once 'controladores/TaskControlador.php';

   $controlador = new TaskControlador();

   if(array_key_exists("taskid" , $_GET))
   { //abre if 1.1

      $taskid = $_GET['taskid'];
      $metodo = $_SERVER['REQUEST_METHOD'];

      $data = array();

      switch($metodo)
      {
         case 'GET':
            $controlador->getTaskId($taskid);
         break;

         case 'DELETE':
            $controlador->deleteTask($taskid);
         break;
         case 'PATCH':

            if(isset($_SERVER['CONTENT_TYPE']) &&  $_SERVER['CONTENT_TYPE'] !== 'application/json')
            {
               $controlador->updateTask(null , null , false);
            }

            $data = file_get_contents('php://input');
            $jsonData = json_decode($data , true);
            $controlador->updateTask($taskid , $jsonData , true);
         break;
      }

   }
   else
   {
      if(isset($_SERVER['REQUEST_METHOD'] ) )
      {
         $metodo = $_SERVER['REQUEST_METHOD'];

         switch($metodo)
         {
            case 'POST':

               if(isset($_SERVER['CONTENT_TYPE']) &&  $_SERVER['CONTENT_TYPE'] !== 'application/json')
               {
                  $controlador->insertTask(null , false);
               }

               $data = file_get_contents('php://input');

               if($jsonData = json_decode($data , true) )
               {
                  $controlador->insertTask($jsonData , true);
               }
               else
               {
                  $controlador->insertTask(null , false);
               }

            break;
         }
      }
   }






 ?>
