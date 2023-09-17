<?php 

require_once('../db.php');
require_once('../models/movie.php');
require_once('../models/errorResponse.php');
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
    /* GET - Movies
    /*********************************************/
    if (preg_match("~/movies.php$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();

      $query = $db->prepare('SELECT * FROM movies;');
      $query->execute();

      $movies = readMovieResultSet($query);
      send($movies);
      exit();
    }


    /*********************************************/
    /* GET - Favourited by
    /*********************************************/
    else if (preg_match("~/movies.php/(\d+)/favourited-by$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();
      $movieId = intval($matches[1]);
      assertValidMovie($db, $movieId);

      $query = $db->prepare('SELECT * FROM favourites WHERE movieId = :movieId;');
      $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
      $query->execute();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $userId = $row['userId'];
        $query = $db->prepare('SELECT * FROM users WHERE id = :userId;');
        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $query->execute();
        $users = readUserResultSet($query);
      }
      send($users);
      exit();
    } 


    /*********************************************/
    /* GET - Movie
    /*********************************************/
    else if (preg_match("~/movies.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised();
      $movieId = intval($matches[1]);
      assertValidMovie($db, $movieId);

      $query = $db->prepare('SELECT * FROM movies WHERE id = :movieId;');
      $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
      $query->execute();

      $movies = readMovieResultSet($query);
      send($movies);
      exit();
    }


    else {
      errorAndExit(400, "Invalid URI Specified");
    }
  }



  /*********************************************/
  /* DELETE
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (preg_match("~/movies.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised(Role::STAFF);
      $movieId = intval($matches[1]);
      assertValidMovie($db, $movieId);

      $query = $db->prepare('DELETE FROM movies WHERE id = :movieId;');
      $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500, "Error deleting movie.");
      }
      exit();
    }


    else {
      errorAndExit(400, "Invalid URI Specified");
    }
  }



  /*********************************************/
  /* POST
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (preg_match("~/movies.php$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised(Role::STAFF);
      $json_request = extractRequestBody();
      assertRequiredMovieFields($json_request);
      assertValidMovieFields($json_request);

      $query = $db->prepare('INSERT INTO movies (name, description, weeklyPrice, thumbnailUrl, availableToRent, rating, diskType) VALUES (:name, :description, :weeklyPrice, :thumbnailUrl, :availableToRent, :rating, :diskType)');
      $query->bindParam(':name', $json_request->name, PDO::PARAM_STR);
      $query->bindParam(':description', $json_request->description, PDO::PARAM_STR);
      $query->bindParam(':weeklyPrice', $json_request->weeklyPrice, PDO::PARAM_INT);
      $query->bindParam(':thumbnailUrl', $json_request->thumbnailUrl, PDO::PARAM_INT);
      $query->bindParam(':availableToRent', $json_request->availableToRent, PDO::PARAM_INT);
      $query->bindParam(':rating', $json_request->rating, PDO::PARAM_INT);
      $query->bindParam(':diskType', $json_request->diskType, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() === 0) {
        errorAndExit(500, "Error creating movie.");
      }
      exit();
    }


    else {
      errorAndExit(400, "Invalid URI Specified");
    }
  }



  /*********************************************/
  /* PATCH
  /*********************************************/
  else if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if (preg_match("~/movies.php/(\d+)$~", $_SERVER['REQUEST_URI'], $matches)) {
      assertAuthorised(Role::STAFF);
      $movieId = intval($matches[1]);
      assertValidMovie($db, $movieId);
      $json_request = extractRequestBody();
      assertValidMovieFields($json_request);

      $fields = array();
      if (isset($json_request->name))           { $fields[] = "name = :name"; }
      if (isset($json_request->description))    { $fields[] = "description = :description"; }
      if (isset($json_request->weeklyPrice))    { $fields[] = "weeklyPrice = :weeklyPrice"; }
      if (isset($json_request->thumbnailUrl))   { $fields[] = "thumbnailUrl = :thumbnailUrl"; }
      if (isset($json_request->availableToRent)){ $fields[] = "availableToRent = :availableToRent"; }
      if (isset($json_request->rating))         { $fields[] = "rating = :rating"; }
      if (isset($json_request->diskType))       { $fields[] = "diskType = :diskType"; }

      $query = $db->prepare('UPDATE movies SET ' . join(', ', $fields) . ' WHERE id = :movieId');
      
      bindParam($query, ':name', $json_request->name, PDO::PARAM_STR);
      bindParam($query, ':description', $json_request->description, PDO::PARAM_STR);
      bindParam($query, ':weeklyPrice', $json_request->weeklyPrice, PDO::PARAM_INT);
      bindParam($query, ':thumbnailUrl', $json_request->thumbnailUrl, PDO::PARAM_STR);
      bindParam($query, ':availableToRent', $json_request->availableToRent, PDO::PARAM_STR);
      bindParam($query, ':rating', $json_request->rating, PDO::PARAM_INT);
      bindParam($query, ':diskType', $json_request->diskType, PDO::PARAM_STR);
      bindParam($query, ':movieId', $movieId, PDO::PARAM_INT);

      $query->execute();
      if ($query->rowCount() === 0) {
        errorAndExit(500, "Error updating movie.");
      }
      exit();
    }


    else {
      errorAndExit(400, "Invalid URI Specified");
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
  echo("Connection error: " . $ex);
}

?>
