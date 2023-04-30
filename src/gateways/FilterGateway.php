<?php

class FilterGateway extends Gateway {
  

  function __construct() {
    $this->table = TABLE["filter"];
    $this->table_fields = [
      "name" => false,
      "available" => true,

      "filtertable_id" => false,
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

    $record->available = true;

    $relations = $this->get_relations_array($data);

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

  public function getAll() {
    $records = R::findAll(
                      $this->table, 
                      // "ORDER BY ? ? LIMIT ? OFFSET ?", 
                      // ['title', 'ASC', 10, 0]
                    );
    if (!$records) {
      return [];
    }
    $records = arr_bean_to_arr($records);

    return $this->get_filters($records);
  }

  public function get_filters($records) {
    $filters = [];

    foreach ($records as $record) {
      $filter_name = $record["name"];
      $filter_table_id = $record['filtertable_id'];
      $filter_data = $this->get_one_filter($filter_table_id, $filter_name);

      $filters[] = $filter_data;
    }
    return $filters;
  }

  public function get_one_filter($filter_table_id, $filter_name) {
    $filter_table = R::load('filtertable', $filter_table_id);
    if (!$filter_table) {
      return [];
    }

    $filter_table_name = $filter_table["name"];

    $filter_table_data = R::findAll($filter_table_name);
    if (!$filter_table_data) {
      return [];
    }

    $filter_table_data = arr_bean_to_arr($filter_table_data);
    $filter_fields = array_column($filter_table_data, "name");

    $filter_data["name"] = $filter_name;
    $filter_data["tablename"] = $filter_table_name;
    $filter_data["fields"] = $filter_fields;

    return $filter_data;
  }
}