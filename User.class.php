<?php
namespace vcms;

use JsonSerializable;

class User extends VObject
    implements JsonSerializable
{
    public $user_id;

    protected $email;
    protected $password;

    public $isAuthenticated;


    function get_password () {
        return $this->password;
    }
    function set_password (string $password) {
        $this->password = $password;
    }
    function jsonSerialize () {
        return get_object_vars($this);
    }
}