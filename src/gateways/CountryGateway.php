<?php

class CountryGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["country"];
    
    $this->table_fields = [
      "name" => false,
    ];
  }
}