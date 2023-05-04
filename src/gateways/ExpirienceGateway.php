<?php

class ExpirienceGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["expirience"];
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

  public function getBy($relation_table, $id, $type='array') {
    $src_table = R::load(TABLE[$relation_table], $id);
    $vacancies = $src_table->ownCountryList;

    if ($type != 'array') {
      return $vacancies;
    }

    return arr_bean_to_arr($vacancies);
  }
}