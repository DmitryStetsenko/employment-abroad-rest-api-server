<?php

class ExpirienceGateway extends Gateway {
  
  function __construct() {
    $this->table = TABLE["expirience"];
    $this->table_fields = [
      "name" => false,
    ];
  }
}