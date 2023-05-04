<?php

class FiltertableGateway extends Gateway {
  
  function __construct() {
    $this->table = TABLE["filtertable"];
    $this->table_fields = [
      "name" => false,
    ];
  }
  
}