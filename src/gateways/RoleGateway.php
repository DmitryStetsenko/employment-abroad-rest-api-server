<?php

class RoleGateway extends Gateway {
  
  function __construct() {
    $this->table = TABLE["role"];
    $this->table_fields = [
      "name" => false,
    ];
  }
}