<?php

require_once 'Mysql.php';

class SessionModelo extends Mysql
{
   public function __construct()
   {
      parent::__construct();
   }

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

   public function updateAttempts($id)
   {
      $query = $this->conexion->prepare('UPDATE users SET loginattempts = loginattempts + 1 WHERE id = :id');
      $query->bindParam(':id' , $id , PDO::PARAM_INT);
    	return $query->execute();

   }

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

}



 ?>
