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

    $relations = [
      "filtertable" => $data["filtertable_id"],
    ];

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

  public function getList($get_params) {
    $filter = array_key_exists("filter", $get_params) ? $get_params["filter"] : [];

    $sort = array_key_exists("sort", $get_params) ? $get_params["sort"] : [];
    $range = array_key_exists("range", $get_params) ? $get_params["range"] : [];

    $sort_field = array_key_exists(0, $sort) ? $sort[0] : "id";
    $sort_direction = array_key_exists(1, $sort) ? $sort[1] : "ASC";
    $offset = array_key_exists(0, $range) ? $range[0] : 0;
    $limit  = array_key_exists(1, $range) ? $range[1] : null;

    $query_str = "ORDER BY $sort_field $sort_direction";
    $query_params_arr = [];

    if ($limit) {
      $query_str .= " LIMIT ? OFFSET ?";
      $query_params_arr[] = $limit;
      $query_params_arr[] = $offset;
    } else {
      $range = [];
    }

    $where_str = "";
    if ($filter) {
      $value = current($filter);
      $field = key($filter);
      $where_str = "WHERE $field = $value";
      next($filter);

      while(current($filter)) {
        $field = key($filter);
        $value = current($filter);

        $where_str .= " AND $field = $value";

        next($filter);
      }
      
      $where_str .= " ";
    }

    $query_str = $where_str . $query_str;

    $records = R::findAll($this->table, $query_str, $query_params_arr);
    $records = arr_bean_to_arr($records);

    $filters = [];

    foreach ($records as $record) {
      $filter_name = $record["name"];
      $filter_table_id = $record['filtertable_id'];
      $filter_data = $this->get_one_filter($filter_table_id, $filter_name);

      $filters[] = $filter_data;
    }
    
    set_content_range_header($this->table, count($records), $range);

    return $filters; 
  }

  public function getFiltersByTable($table) {
    
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