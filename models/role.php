<?php
class Role
  {
    const CUSTOMER = 1;
    const STAFF = 2;
    const ADMIN = 3;

    public static function fromString($roleName)
    {
        switch (strtolower($roleName)) {
            case 'admin':
                return self::ADMIN;
            case 'staff':
                return self::STAFF;
            case 'customer':
                return self::CUSTOMER;
            default:
                throw new Exception("customer does not have a valid role");
        }
    }

    public static function toString($roleValue)
    {
        switch ($roleValue) {
            case self::ADMIN:
                return 'ADMIN';
            case self::STAFF:
                return 'STAFF';
            case self::CUSTOMER:
                return 'CUSTOMER';
            default:
                throw new Exception("customer does not have a valid role");
        }
    }
  }
?>
