<?php

require_once 'Mysql.php';

class SessionModelo extends Mysql
{
   public function __construct()
   {
      parent::__construct();
   }

   //comprobamos si un nombre de usuario existe, para poder darle accesso al sistema
   public function existsUser($username)
   {
      $query = $this->conexion->prepare('SELECT id, fullname, username, password, useractive, loginattempts from users where username = :username');
      $query->bindParam(':username', $username , PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0)
      {
        return false;
      }

      return $query->fetch(PDO::FETCH_ASSOC);
   }

   //vamos aumentando la cantidad de intentos que va generando el usuario en cada intento de inicio de session
   //si el valor es mayor o igual que 3, bloqueamos el accesso de esa cuenta
   public function updateAttempts($id)
   {
      $query = $this->conexion->prepare('UPDATE users SET loginattempts = loginattempts + 1 WHERE id = :id');
      $query->bindParam(':id' , $id , PDO::PARAM_INT);
    	return $query->execute();

   }

   /*
      Este metodo se encarga de generar una inserción en la tabla session y de actualizar la cantidad de intentos generados por el usuarios
      Se utiliza principalmente en la uri encargada de generar una sesssion
    */
   public function generarToken($returned_id , $access_token_expiry_seconds , $refresh_token_expiry_seconds , $refreshtoken  , $accesstoken)
   {
      try
      {

        $this->conexion->beginTransaction();
        // create the query string to reset attempts figure after successful login
        $query = $this->conexion->prepare('UPDATE users SET loginattempts = 0 WHERE id = :id');
        // bind the user id
        $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
        // run the query
        $query->execute();

        // create the query string to insert new session into sessions table and set the token and refresh token as well as their expiry dates and times
        $query = $this->conexion->prepare('INSERT INTO tblsession(userid, accesstoken, accesstokenexpiry, refreshtoken, refreshtokenexpiry)
        values (:userid, :accesstoken, date_add(NOW(), INTERVAL :accesstokenexpiryseconds SECOND), :refreshtoken, date_add(NOW(), INTERVAL :refreshtokenexpiryseconds SECOND))');
        // bind the user id
        $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        // bind the access token
        $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
        // bind the access token expiry date
        $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
        // bind the refresh token
        $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
        // bind the refresh token expiry date
        $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
        // run the query
        $query->execute();

        // get last session id so we can return the session id in the json
        $lastSessionID = $this->conexion->lastInsertId();

        // commit new row and updates if successful
        $this->conexion->commit();

        // build response data array which contains the access token and refresh tokens
        $returnData = array();
        $returnData['session_id'] = intval($lastSessionID);
        $returnData['access_token'] = $accesstoken;
        $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
        $returnData['refresh_token'] = $refreshtoken;
        $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

      }
      catch(PDOException $ex) {
        // roll back update/insert if error
        $this->conexion->rollBack();
        return false;
      }
      return $returnData;
   }


    /**
     * [deleteSession eliminamos una session ]
     * @param  [int] $id               [identificador de la session que vamos a eliminar]
     * @return [int]     [cantidad de filas afectadas]
     */
   public function deleteSession($id)
   {
      $query = "DELETE FROM tblsession WHERE id = :id";
      $query = $this->conexion->prepare($query);
      $query->bindParam(':id' , $id , PDO::PARAM_INT);
    	$query->execute();

      return $query->rowCount();

   }


   //Obtenemos la informacion de un token para procesarlo en el controlador
   public function getDataSession($token)
   {
      $query = "SELECT * FROM tblsession WHERE accesstoken = :accesstoken";

      $query = $this->conexion->prepare($query);
      $query->bindParam(':accesstoken' , $token , PDO::PARAM_STR);
    	$query->execute();

      return $query->fetch(PDO::FETCH_ASSOC);

   }

   /* Buscamos que el token refresh que nos envian se encuentre en la tabla sessions para poder hacer la actualizacion */
   public function findTokenRefresh($token)
   {
      $query = "SELECT * FROM tblsession WHERE refreshtoken = :refreshtoken";

      $query = $this->conexion->prepare($query);
      $query->bindParam(':refreshtoken' , $token , PDO::PARAM_STR);
      $query->execute();

      return $query->fetch(PDO::FETCH_ASSOC);
   }

   /**
    * [updateRefreshToken Con este metodo actualizamos los tokens de una session al igual que su tiempo de expiracion ]
    * @param  [int] $id                                         [id de la session]
    * @param  [string] $accesstoken                                [el nuevo accesstoken generado en el controlador]
    * @param  [string] $refreshtoken                               [el nuevo refreshtoken generado en el controlador]
    * @param  [int] $access_token_expiry_seconds                [valor entero correspondiente a la cantidad de segundos que vamos a utilizar en el date expired]
    * @param  [int] $refresh_token_expiry_seconds               [valor entero correspondiente a la cantidad de segundo que vamos a utilizar en el date expired del refresh token]
    * @return [int]                               [cantidad de filas afectadas despues del update]
    */
   public function updateRefreshToken($id ,  $accesstoken ,  $refreshtoken , $access_token_expiry_seconds , $refresh_token_expiry_seconds )
   {
      $access_token_expiry_seconds = date('Y-m-d H:i' , $access_token_expiry_seconds);
      $refresh_token_expiry_seconds = date('Y-m-d H:i' , $refresh_token_expiry_seconds);

      $query = "UPDATE tblsession SET accesstoken = :accesstoken , accesstokenexpiry = date_add(now() , INTERVAL :accesstokenexpiry SECOND) , refreshtoken = :refreshtoken , refreshtokenexpiry = date_add(now() , INTERVAL :refreshtokenexpiry SECOND)
      WHERE id = :id ";
      $query = $this->conexion->prepare($query);
      $query->bindParam(':accesstoken' , $accesstoken , PDO::PARAM_STR);
      $query->bindParam(':accesstokenexpiry' , $access_token_expiry_seconds , PDO::PARAM_INT);
      $query->bindParam(':refreshtoken' , $refreshtoken , PDO::PARAM_STR);
      $query->bindParam(':refreshtokenexpiry' , $refresh_token_expiry_seconds , PDO::PARAM_INT);
      $query->bindParam(':id' , $id , PDO::PARAM_INT);

      $query->execute();

      return $query->rowCount();
   }
}



 ?>
