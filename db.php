<?php
  class DB {
    private static $writeDBConnection;
    private static $readDBConnection;

    public static function connectWriteDB() {
      $env = parse_ini_file('.env');
      $pass = $env['PASSWORD'];
      $user = $env['USER'];
      $conn = $env['CONN_STRING'];

      if (self::$writeDBConnection === null) {
        self::$writeDBConnection = new PDO($conn, $user, $pass);
        self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      }
      return self::$writeDBConnection;
    }

    public static function connectReadDB() {
      $env = parse_ini_file('.env');
      $pass = $env['PASSWORD'];
      $user = $env['USER'];
      $conn = $env['CONN_STRING'];

      if (self::$readDBConnection === null) {
        self::$readDBConnection = new PDO($conn, $user, $pass);
        self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      }
      return self::$readDBConnection;
    }
  }
?>
