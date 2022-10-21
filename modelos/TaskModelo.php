<?php

require_once 'Mysql.php';


class TaskModelo extends Mysql
{
   public function __construct()
   {
      parent::__construct();
   }

   /**
    * [getTaskId Buscamos una task de acuerdo a un id, retornamos el resultado]
    * @param  [type] $id               [description]
    * @return [type]     [description]
    */
   public function getTaskId($id)
   {
      $query = $this->conexion->prepare("SELECT id , title , description , date_format(deadline , '%d/%m/%Y %H:%i') as deadline , completed FROM tbltasks WHERE id = :taskid");
      $query->bindParam(':taskid' , $id , PDO::PARAM_INT);
      $query->execute();

      return $query->fetchAll(PDO::FETCH_ASSOC);
   }

   /**
    * [deleteTask Eliminamos una task de la database]
    * @param  [type] $id               [description]
    * @return [type]     [description]
    */
   public function deleteTask($id)
   {
      $query = $this->conexion->prepare("DELETE FROM tbltasks WHERE id = :taskid");
      $query->bindParam(':taskid' , $id , PDO::PARAM_INT);
      $query->execute();

      return $query->rowCount();

   }

   /**
    * [updateTask Actualizamos una tarea, los valores que se actualizaran sera el title, la description y el completed]
    * @param  [int] $id                 [description]
    * @param  [array] $data               [los valores que se actualizaran]
    * @return [int]       [cantidad de filas afectadas]
    */
   public function updateTask($id , $data)
   {
      $query = $this->conexion->prepare("UPDATE tbltasks SET title = :title , description = :description , completed = :completed WHERE id = :idtask ");
      $query->bindParam(':title' , $data['title'] , PDO::PARAM_STR);
      $query->bindParam(':description' , $data['description'] , PDO::PARAM_STR);
      $query->bindParam(':completed' , $data['completed'] , PDO::PARAM_STR);
      $query->bindParam(':idtask' , $id , PDO::PARAM_INT);

      $query->execute();

      return $query->rowCount();
   }




   public function insertTask($data)
   {

      $query = $this->conexion->prepare("INSERT INTO tbltasks(title , description , deadline , completed) VALUES(:title, :description, :deadline, :completed)");
      $query->bindParam(':title' , $data['title'] , PDO::PARAM_STR);
      $query->bindParam(':description' , $data['description'] , PDO::PARAM_STR);
      $query->bindParam(':deadline' , $data['deadline'] , PDO::PARAM_STR);
      $query->bindParam(':completed' , $data['completed'] , PDO::PARAM_STR);
      $query->execute();

      return $query->rowCount();
   }
}






 ?>
