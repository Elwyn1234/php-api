<?php

require_once('../db.php');
require_once('../models/user.php');
require_once('../lib/users.php');
require_once('../lib/movies.php');
require_once('./utils.php');

try {
  $db = DB::connectReadDB();
  $query = $db->prepare('DELETE FROM movies;');
  $query->execute();
  $query = $db->prepare('DELETE FROM users WHERE role != "admin";');
  $query->execute();
  $query = $db->prepare('DELETE FROM favourites;');
  $query->execute();
}

catch(PDOException $ex) {
  echo("Error: " . $ex);
}

?>
