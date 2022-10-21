<?php

require_once 'modelos/TaskModelo.php';
require_once 'ResponseControlador.php';

class TaskControlador
{
   private $response;
   private $modelo;

   public function __construct()
   {
      $this->response = new ResponseControlador();
      $this->modelo = new TaskModelo();
   }


   /**
    * [getTaskId Utilizamos este método para obtener una task por medio del id. Dicho id nos los envian desde la URL]
    * @param  [int] $id               [id de la tarea]
    * @return [Response]     [Este metodo retorna una response de la clase Response]
    */
   public function getTaskId($id)
   {

      if($row = $this->modelo->getTaskId($id))
      {
         $data['rows_returned'] = 1;
         $data['task'] = $row;
         $this->response->generateResponse(200 , true , null , true , $data);
      }
      else
      {
         $this->response->generateResponse(400 , false , 'Task not found');
      }
   }

   /**
    * [deleteTask Por medio de este controlador llamamos al modelo encargado de eliminar una task de la database]
    * @param  [int] $id               [id de la task ]
    * @return [type]     [description]
    */
   public function deleteTask($id)
   {

      $rowCount = $this->modelo->deleteTask($id);

      if($rowCount >= 1 )
      {
         $this->response->generateResponse(200 , true , 'TASK DELETE SUCCESS');
      }
      else
      {
         $this->response->generateResponse(400 , false , 'TASK NOT FOUND');
      }

   }

    /**
     * [updateTask Este metodo actualiza los valores de una determina task dichos valores son: title , description and completed]
     * @param  [int] $id                 [id de la task ]
     * @param  [array] $data               [información que se va a actualizar (title,description,completed)]
     * @return [type]       [description]
     */
   public function updateTask($id , $data , $bandera)
   {

      if(!$bandera)
      {
         $this->response->generateResponse(400 , false , 'Content type header is not set to JSON' );
      }

      $returnData = array();

      if($data !== null)
      {
         if( $rowCount =  $this->modelo->updateTask($id , $data) <= 0)
         {
            $this->response->generateResponse(400 , false , 'Task not found');
         }

         $returnData['rows_returned'] = $rowCount;
         $this->response->generateResponse(200 , true , 'Task update success' );
      }
      else
      {
         $this->response->generateResponse(400 , false , 'Request body is not valid JSON');
      }
   }

   /**
    * [insertTask description]
    * @param  [type] $data                  [description]
    * @param  [type] $bandera               [description]
    * @return [type]          [description]
    */
   public function insertTask($data , $bandera)
   {

      if(!$bandera)
      {
         $this->response->generateResponse(400 , false , 'Content type header is not set to JSON' );
      }

      if(!isset($data['title']) || !isset($data['completed']))
      {
         $mensaje = (!isset($jsonData->title)) ? 'Title field is mandatory and must be provided' : false;

         if(!$mensaje)
         {
            $mensaje = (!isset($jsonData->completed) ) ? 'Completed field is mandatory and must be provided' : false;
         }

         $this->response->generateResponse(400 , false , $mensaje);

      }

      if($rowCount = $this->modelo->insertTask($data) <= 0)
      {
         $this->response->generateResponse(500 , false , 'Failed to create task');
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $data;
      $this->response->generateResponse(201 , true , 'Task created' , true , $returnData);

   }


   //metodo utilizado para crear un paginador
   public function pageTask($page , $limit)
   {
      //comprobamos que la pagina no esté vacia o  que no sea un numero
      if($page === '' || !is_numeric($page))
      {
         $this->response->generateResponse(400 , false , 'Page number cannot be blank and must be numeric');
      }

      if($tasks = $this->modelo->pageTask($page , $limit))
      {
         $returnData = array();
         $returnData['rows_returned'] = count($tasks);
         $returnData['tasks'] = $tasks;
         $this->response->generateResponse(200 , true , null , true , $returnData);
      }
      else{
         $this->response->generateResponse(400 , false , 'Task not found');
      }

   }


   /*Obtendremos todas las tareas completadas o incompletadas de acuerdo al valor que nos mande el cliente desde la url*/
   public function completedTask($completed , $bandera )
   {
      if($bandera)
      {
         if($completed !== 'Y' && $completed !== 'N')
         {
            $this->response->generateResponse(400 , false , 'Completed filter must be Y or N');
         }


         if($data = $this->modelo->completed($completed))
         {
            $returnData = array();

            $returnData['rows_returned'] = count($data);
            $returnData['tasks'] = $data;

            $this->response->generateResponse(200 , true , null , true , $returnData);
         }
      }
      else {
         $this->response->generateResponse(405 , false , 'REQUEST METHOD NOT ALLOWED' );
      }



   }

}




 ?>
