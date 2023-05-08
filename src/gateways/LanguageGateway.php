<?php

class LanguageGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["language"];
    
    $this->table_fields = [
      "name"  => false,
      "level" => false,
    ];
  }
}