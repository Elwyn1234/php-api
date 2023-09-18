<?php
  require_once '../models/role.php';
  require_once '../models/errorResponse.php';
  require_once '../vendor/autoload.php';
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;

  const secretKey = "bGFrZWZlYXR1cmVpcm9uc3RyZWV0bm9pc2V3ZWFyZWF0cG9wdWxhdGlvbnBpbmt3aGk=";

  function dataToXml($data, $xml = null) {
    if ($xml === null) {
        $xml = new SimpleXMLElement('<root/>');
    }

    foreach ($data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            if (is_object($value)) {
                $value = (array) $value; // Convert object to array
            }
            dataToXml($value, $xml->addChild("node" . $key));
        } else {
            $xml->addChild($key, htmlspecialchars($value)); // Ensure proper XML encoding
        }
    }

    return $xml->asXML();
  }

  function send($array) {
    if ($_SERVER['HTTP_ACCEPT'] === "application/json" || $_SERVER['HTTP_ACCEPT'] === "*/*") {
      header('Content-Type: application/json;charset-utf-8');
      echo json_encode($array);
    }
    else if ($_SERVER['HTTP_ACCEPT'] === "application/xml") { 
      header('Content-Type: application/xml');
      echo dataToXml($array);
    }
    else {
      errorAndExit(400,"Invalid HTTP_ACCEPT specified: " );
    }
  }

  function assertAuthorised($role = Role::CUSTOMER, $username = null) {
    $headers = getallheaders();
    if (!isset($headers["Authorization"])) {
      errorAndExit(400,"No Authorization Header given.");
    }
    $authorizationHeader = $headers["Authorization"];

    if (strpos($authorizationHeader, 'Bearer ') !== 0) {
      errorAndExit(400, "Invalid Authorization header format.");
    }
    $token = substr($authorizationHeader, 7); // Remove "Bearer " prefix


    try {
        $decoded = JWT::decode($token, new Key(secretKey, 'HS256'));
    } catch (Exception $ex) {
      errorAndExit(401,"Invalid token provided");
    }
    // Check if the user has permission. if not, check for role perms.
    if ($username != null && $decoded->sub === $username) {
      return;
    }
    if (Role::fromString($decoded->role) < $role) {
      errorAndExit(403,"Unauthorised");
    }
  }

  function generateJWT($user) {
    $payload = array(
        "iat" => time(),
        "exp" => time() + 3600,
        "sub" => $user->username,
        "role" => $user->role,
    );
    $jwt = JWT::encode($payload, secretKey, 'HS256');
    return $jwt;
  }

  function extractIdFromPath($segment = "last") {
    $uri = $_SERVER['REQUEST_URI'];
    $uriSegments = explode('/', trim($uri, '/'));
    if ($segment >= count($uriSegments)) {
      return null;
    }
    if ($segment === "last") {
      $segment = count($uriSegments) - 1;
    }
    $userIdSegment = $uriSegments[$segment];
    if (!is_numeric($userIdSegment)) {
      errorAndExit(400, "Invalid request. A path param must be provided that is a valid integer");
    }
    return intval($userIdSegment);
  }

  function extractRequestBody() {
    $requestBody = file_get_contents("php://input");
    if (empty($requestBody)) {
      errorAndExit(400, "ERROR: no request body given");
    }
    return json_decode($requestBody);
  }

  function bindParam($query, $strParam, $param, $paramType) {
    if (isset($param))
        $query->bindParam($strParam, $param, $paramType);
  }

  function errorAndExit($statusCode, $error) {
    $errorResponse = new ErrorResponse();
    $errorResponse->statusCode = $statusCode;
    $errorResponse->error = $error;
    http_response_code($errorResponse->statusCode);
    send($errorResponse);
    exit(1);
  }
?>
