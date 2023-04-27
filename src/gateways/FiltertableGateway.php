<?php

class FiltertableGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["filtertable"];
    $this->table_fields = [
      "name" => false,
    ];
  }

  public function create($data){
    // exit (json_encode($data));
    $result = $this->check_fields($data);
    if (!$result["ok"]) {
      return $result;
    }

    

    $record = R::dispense($this->table);
    
    $record->name = $data["name"];

    $relations = [];

    foreach( $relations as $relation_table => $id ) {
      $relation = R::load(TABLE[$relation_table], $id);
      $relation->ownCountryList[] = $record;
      R::store($relation);
    }

    if (!$relations) {
      R::store($record);
    }
    
    $record_id = $record->id;

    $result = [
      "ok"  => true,
      "meassage"  => "record created",
      "id"  => $record_id
    ];
    return $result;
  }

  public function getFiltersByTable($table) {
    
  }
}