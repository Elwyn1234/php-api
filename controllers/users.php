<?php

require_once('../db.php');
require_once('../models/user.php');
require_once('../lib/users.php');
require_once('../lib/movies.php');
require_once('./utils.php');

try {
  $db = DB::connectReadDB();


  /*********************************************/
  /* GET
  /*********************************************/
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    /*********************************************/
    /* GET - Users
    /*********************************************/
    if (preg_match("~/users.php$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();

      $query = $db->prepare('SELECT * FROM users;');
      $query->execute();

      $users = readUserResultSet($query);
      send($users);
      exit();
    }


    /*********************************************/
    /* GET - Favourites
    /*********************************************/
    else if (preg_match("~/users.php/(\d+)/favourite-movies$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();
      $userId = intval($matches[1]);
      assertValidUser($db, $userId);

      $query = $db->prepare('SELECT * FROM favourites WHERE userId = :userId;');
      $query->bindParam(':userId', $userId, PDO::PARAM_INT);
      $query->execute();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $movieId = $row['movieId'];
        $query = $db->prepare('SELECT * FROM movies WHERE id = :movieId;');
        $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
        $query->execute();
        $movies = readMovieResultSet($query);
      }

      send($movies);
      exit();
    } 


    /*********************************************/
    /* GET - User
    /*********************************************/
    else if (preg_match("~/users.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();
      $userId = intval($matches[1]);
      assertValidUser($db, $userId);

      $users = readUser($db, $userId);
      send($users);
      exit();
    }


    else {
      errorAndExit(400,"Invalid URI specified");
    }
  }



  /*********************************************/
  /* DELETE
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    /*********************************************/
    /* DELETE - Favourite
    /*********************************************/
    if (preg_match("~/users.php/(\d+)/favourite-movies/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      $userId = intval($matches[1]);
      $movieId = intval($matches[2]);
      $user = selectUser($db, $userId);
      assertAuthorised(Role::ADMIN, $user->username);
      assertValidUser($db, $userId);
      assertValidMovie($db, $movieId);

      $query = $db->prepare('DELETE FROM favourites WHERE userId = :userId AND movieId = :movieId;');
      $query->bindParam(':userId', $userId, PDO::PARAM_INT);
      $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500,"Error deleting favourite.");
      }
      exit();
    }


    /*********************************************/
    /* DELETE - User
    /*********************************************/
    else if (preg_match("~/users.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      $userId = intval($matches[1]);
      $user = selectUser($db, $userId);
      assertAuthorised(Role::ADMIN, $user->username);
      assertValidUser($db, $userId);

      $query = $db->prepare('DELETE FROM users WHERE id = :userId;');
      $query->bindParam(':userId', $userId, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500,"Error deleting user.");
      }
      exit();
    }


    else {
      errorAndExit(400,"Invalid URI specified");
    }
  }



  /*********************************************/
  /* POST
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*********************************************/
    /* POST - Favourite
    /*********************************************/
    if (preg_match("~/users.php/(\d+)/favourite-movies/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      $userId = intval($matches[1]);
      $movieId = intval($matches[2]);
      $user = selectUser($db, $userId);
      assertAuthorised(Role::ADMIN, $user->username);
      assertValidUser($db, $userId);
      assertValidMovie($db, $movieId);

      $query = $db->prepare('INSERT INTO favourites (userId, movieId) VALUES (:userId, :movieId)');
      $query->bindParam(':userId', $userId, PDO::PARAM_STR);
      $query->bindParam(':movieId', $movieId, PDO::PARAM_STR);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500,"Error creating favourite movie.");
      }
      exit();
    }


    /*********************************************/
    /* POST - User
    /*********************************************/
    else if (preg_match("~/users.php$~", $_SERVER['REQUEST_URI'], $matches)) {
      $json_request = extractRequestBody();
      assertRequiredUserFields($json_request);
      assertValidUserFields($json_request);
      $userRole = Role::toString(Role::CUSTOMER);
      if (isset($json_request->role)) {
        $userRole = $json_request->role;
      }

      $hashed_password = password_hash($json_request->password, PASSWORD_BCRYPT);
      $query = $db->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
      $query->bindParam(':username', $json_request->username, PDO::PARAM_STR);
      $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
      $query->bindParam(':role', $userRole, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500,"Error creating user.");
      }
      exit();
    }


    else {
      errorAndExit(400,"Invalid URI specified");
    }
  }



  /*********************************************/
  /* PATCH
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if (preg_match("~/users.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      $userId = intval($matches[1]);
      $user = selectUser($db, $userId);
      assertAuthorised(Role::ADMIN, $user->username);
      assertValidUser($db, $userId);
      $json_request = extractRequestBody();
      assertValidUserFields($json_request);

      $fields = array();
      if (isset($json_request->username)) {
        $fields[] = "username = :username";
      }
      if (isset($json_request->password)) {
        $fields[] = "password = :password";
      }
      if (isset($json_request->role)) {
        $fields[] = "role = :role";
      }



      $query = $db->prepare('UPDATE users SET ' . join(', ', $fields) . ' WHERE id = :userId');
      


      $hashed_password = password_hash($json_request->password, PASSWORD_BCRYPT);
      if (isset($json_request->username)) {
        $query->bindParam(':username', $json_request->username, PDO::PARAM_STR);
      }
      if (isset($json_request->password)) {
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
      }
      if (isset($json_request->role)) {
      $query->bindParam(':role', $json_request->role, PDO::PARAM_STR);
      }
      $query->bindParam(':userId', $userId, PDO::PARAM_INT);



      $query->execute();
      if ($query->rowCount() === 0) {
        errorAndExit(500,"Error updating user.");
      }
      exit();
    }


    else {
      errorAndExit(400,"Invalid URI specified");
    }
  }



  /*********************************************/
  /* FAIL
  /*********************************************/
  else {
    errorAndExit(400,"Request method unsupported");
  }
}
catch(PDOException $ex) {
  echo("Error: " . $ex);
}

?>
