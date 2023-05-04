<?php

class SpecialityGateway extends Gateway {
  
  function __construct() {
    $this->table = TABLE["speciality"];
    $this->table_fields = [
      "name" => false,
    ];
  }
}