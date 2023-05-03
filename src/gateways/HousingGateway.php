<?php

class HousingGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["housing"];
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

    $relations = $this->get_relations_array($data);

    foreach( $relations as $relation_table => $id ) {
      $relation = R::load(TABLE[$relation_table], $id);
      $relation->ownHousingList[] = $record;
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
}