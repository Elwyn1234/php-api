<?php
require_once('./utils.php');
require_once('../models/user.php');

function selectUser($db, $userId) {
  $query = $db->prepare('SELECT * FROM users WHERE id = :userId;');
  $query->bindParam(':userId', $userId, PDO::PARAM_INT);
  $query->execute();

  $users = readUserResultSet($query, true);
  if (count($users) == 0) {
      errorAndExit(400,"Username or password incorrect");
  }

  return $users[0];
}

function readUserResultSet($query, $readPassword = false) {
  $users = array();
  while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $user = new User();
    $user->id = $row['id'];
    $user->username = $row['username'];
    $user->role = $row['role'];
    if ($readPassword) {
      $user->password = $row['password'];
    }

    $users[] = $user;
  }
  return $users;
}

function readUser($db, $userId) {
  $query = $db->prepare('SELECT * FROM users WHERE id = :userId;');
  $query->bindParam(':userId', $userId, PDO::PARAM_INT);
  $query->execute();
  return readUserResultSet($query);
}

function assertValidUser($db, $userId) {
  $users = readUser($db, $userId);
  if (count($users) < 1) {
    errorAndExit(400,"Invalid User ID");
  }
}

function assertRequiredUserFields($json_request) {
  if (!isset($json_request->username)) {
    errorAndExit(400,"Bad Request: username not given");
  }

  if (!isset($json_request->password)) {
    errorAndExit(400,"Bad Request: password not given");
  }
}

function assertValidUserFields($json_request) {
  if (isset($json_request->username)) {
    if (!is_string($json_request->username)) {
      errorAndExit(400,"Bad Request: username must be a string");
    }
    if (strlen($json_request->username) > 32) {
      errorAndExit(400,"Bad Request: username must not be greater than 32 characters");
    }
  }

  if (isset($json_request->password)) {
    if (!is_string($json_request->password)) {
      errorAndExit(400,"Bad Request: password must be a string");
    }
    if (strlen($json_request->password) > 32) {
      errorAndExit(400,"Bad Request: password must not be greater than 32 characters");
    }
  }

  if (isset($json_request->role)) {
    try {
      Role::fromString($json_request->role);
    }
    catch (Exception $ex) {
      errorAndExit(400,"Bad Request: " . $ex);
    }
  }
}

?>
