<?php

class VacancyGateway extends Gateway {
  function __construct() {
    $this->table = TABLE["vacancy"];
    $this->table_fields = [
      "title" => false,
      "description" => false,
      "salary" => false,
      "available" => true,
      // "thumbnails" => false,
      
      "employer_id" => false,
      "country_id"  => false,
      "speciality_id" => false,
      "expirience_id" => false,
    ];
  }
  
  public function create($data){
    $result = $this->check_fields($data);
    if (!$result["ok"]) {
      return $result;
    }

    $record = R::dispense($this->table);
    
    $record->title = $data["title"];
    $record->description = $data["description"];
    $record->salary = $data["salary"];
    // $record->thumbnails = json_encode($data["thumbnails"]);

    $record->available = true;
    $record->created = date("Y-m-d H:i:s");

    $relations = [
      "employer" => $data["employer_id"],
      "country" => $data["country_id"],
      "speciality" => $data["speciality_id"],
      "expirience" => $data["expirience_id"],
    ];

    foreach( $relations as $relation_table => $id ) {
      $relation = R::load(TABLE[$relation_table], $id);
      $relation->ownVacancyList[] = $record;
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
    $vacancies = $src_table->ownVacancyList;

    if ($type != 'array') {
      return $vacancies;
    }

    return arr_bean_to_arr($vacancies);
  }
}