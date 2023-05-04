<?php

class HousingGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["housing"];
    $this->table_fields = [
      "name" => false,
    ];
  }
}