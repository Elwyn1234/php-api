<?php 
require_once('../db.php');
require_once('../models/user.php');
require_once('../lib/users.php');
require_once('./utils.php');
try {
  $db = DB::connectReadDB();


  /*********************************************/
  /* POST
  /*********************************************/
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorAndExit(400,"Request method unsupported");
  }
  if (!preg_match("~/token.php$~", $_SERVER['REQUEST_URI'], $matches)) {
    errorAndExit(400,"Invalid URI specified: " . $_SERVER['REQUEST_URI']);
  }
  $json_request = extractRequestBody();
  assertRequiredUserFields($json_request);
  assertValidUserFields($json_request);


  $query = $db->prepare('SELECT * FROM users WHERE username = :username;');
  $query->bindParam(':username', $json_request->username, PDO::PARAM_INT);
  $query->execute();

  $users = readUserResultSet($query, true);
  if (count($users) == 0) {
    errorAndExit(400,"Username or password incorrect");
  }

  $user = $users[0];
  if (!password_verify($json_request->password, $user->password)) {
    errorAndExit(400,"Username or password incorrect");
  }


  class Token {
    public $token;
  }
  $token = new Token();
  $token->token = generateJWT($user);
  send($token);
  exit();
}
catch(PDOException $ex) {
  echo("Connection error: " . $ex);
}
?>
