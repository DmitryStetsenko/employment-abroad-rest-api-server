<?php

class RoleGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["role"];
    $this->table_fields = [
      "name" => false,
    ];
  }

  public function create($data){
    $result = $this->check_fields($data);
    if (!$result["ok"]) {
      return $result;
    }

    $record = R::dispense($this->table);
    
    $record->name = $data["name"];

    if (!$this->make_relations_indexes($data, $record)) {
      R::store($record);
    }

    $result = [
      "ok"  => true,
      "meassage"  => "record created",
      "id"  => $record->id
    ];

    return $result;
  }
}