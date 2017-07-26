<?php
namespace vcms;

use JsonSerializable;

class User extends VcmsObject
    implements JsonSerializable
{
    protected $user_id;

    protected $email;
    protected $password;

    protected $isAuthenticated;

    function jsonSerialize () {
        return get_object_vars($this);
    }

}