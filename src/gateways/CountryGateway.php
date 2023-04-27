<?php

class CountryGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["country"];
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

  public function getBy($relation_table, $id, $type='array') {
    $src_table = R::load(TABLE[$relation_table], $id);
    $vacancies = $src_table->ownCountryList;

    if ($type != 'array') {
      return $vacancies;
    }

    return arr_bean_to_arr($vacancies);
  }
}