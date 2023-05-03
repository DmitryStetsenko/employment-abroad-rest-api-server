<?php

class Gateway {
  public $table;

  public $table_fields;

  public function get($id) {
    // $record = R::load($this->table, $id);

    // if ($record->id === 0) {
    //   return null;
    // }

    // return bean_to_arr($record); 

    $where_str = "WHERE {$this->table}.id = $id";
    $relation_tables = $this->get_relation_tables();
      if ($relation_tables) {
        $query = $this->get_join_relations_query($relation_tables);
        $query .= $where_str;

        $records = R::getAll($query);

        // exit(json_encode($query));
        set_content_range_header($this->table, count($records));
        return $records[0];
      }
  }

  public function getAll() {
    $records = R::findAll(
                      $this->table, 
                      "ORDER BY ? ? LIMIT ? OFFSET ?", 
                      ['title', 'ASC', 10, 0]
                    );

    if (!$records) {
      return [];
    }

    return arr_bean_to_arr($records);
  }

  public function getList($get_params) {
    $filter = array_key_exists("filter", $get_params) ? $get_params["filter"] : [];
    $sort = array_key_exists("sort", $get_params) ? $get_params["sort"] : [];
    $range = array_key_exists("range", $get_params) ? $get_params["range"] : [];
    $is_join = array_key_exists("join", $get_params) ? true : false;

    $sort_field = array_key_exists(0, $sort) ? $sort[0] : "id";
    $sort_direction = array_key_exists(1, $sort) ? $sort[1] : "ASC";
    $offset = array_key_exists(0, $range) ? $range[0] : 0;
    $limit  = array_key_exists(1, $range) ? $range[1] : null;

    $where_str = "";
    $query_str = "ORDER BY $sort_field $sort_direction";

    $query_params_arr = [];
    if ($limit) {
      $query_str .= " LIMIT ? OFFSET ?";
      $query_params_arr[] = $limit;
      $query_params_arr[] = $offset;
    } else {
      $range = [];
    }

    if ($filter) {
      $value = current($filter);
      $field = key($filter);
      
      $where_str = "WHERE {$this->table}.{$field} = $value";
      next($filter);

      while(current($filter)) {
        $field = key($filter);
        $value = current($filter);

        $where_str .= " AND {$this->table}.$field = $value";

        next($filter);
      }
      
      $where_str .= " ";
    }

    $query_str = $where_str . $query_str;

    if ($is_join) {
      $relation_tables = $this->get_relation_tables();
      if ($relation_tables) {
        $query = $this->get_join_relations_query($relation_tables);
        $query .= $query_str;

        $records = R::getAll($query);
        set_content_range_header($this->table, count($records), $range);
        return $records;
      }
    }

    $records = R::findAll($this->table, $query_str, $query_params_arr);
    $records= arr_bean_to_arr($records);
    set_content_range_header($this->table, count($records), $range);
    return $records;
  }

  public function getMany($ids) {
    $record = R::loadAll($this->table, $ids);
    $record = arr_bean_to_arr($record);

    set_content_range_header($this->table, count($record));

    return $record;
  }

  public function getManyReference($get_params) {
    $filter = array_key_exists("filter", $get_params) ? $get_params["filter"] : [];
    $sort = array_key_exists("sort", $get_params) ? $get_params["sort"] : [];
    $range = array_key_exists("range", $get_params) ? $get_params["range"] : [];

    $sort_field = array_key_exists(0, $sort) ? $sort[0] : "id";
    $sort_direction = array_key_exists(1, $sort) ? $sort[1] : "ASC";
    $offset = array_key_exists(0, $range) ? $range[0] : 0;
    $limit  = array_key_exists(1, $range) ? $range[1] : null;

    $query_str = "ORDER BY $sort_field $sort_direction";

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
    echo $query_str;

    $record = R::findAll($this->table, $query_str, $query_params_arr);
    $record = arr_bean_to_arr($record);

    set_content_range_header($this->table, count($record), $range);

    return $record;
  }

  public function getFull() {
    $relation_tables = $this->get_relation_tables();
    if (!$relation_tables) {
      return [];
    }

    $query = $this->get_join_relations_query($relation_tables);
    $result = R::getAll($query);

    return $result;
  }

  public function update($record, $new_data) {
    $record = R::convertToBean($this->table, $record);
    $rows = 0;
    foreach($new_data as $field => $value) {
      $record->{$field} = $value;
    }
    R::store($record);

    return $record->export();
  }

  public function updateMany($ids, $new_data) {
    $rows = 0;
    $updated_ids = [];
    foreach($ids as $id) {
      $record = R::load($this->table, $id);

      if ($record) {
        foreach($new_data as $field => $value) {
          $record->{$field} = $value;
        }
        R::store($record);
        $updated_ids[] = $id;
      }
      
      $rows ++;
    }
    
    $updated_ids_str = json_encode($updated_ids);
    return [
      "message" => "Vacancy $updated_ids_str updated",
      "rows" => $rows,
    ];
  }

  public function delete($record) {
    $record = R::convertToBean($this->table, $record);
    $id = $record->id;

    R::hunt($this->table, 'id = ?', [$id]);

    return [
      "message" => "Record $record->id deleted",
      "rows" => 1,
    ];
  }

  public function deleteMany($ids) {
    R::trashBatch($this->table, $ids);
    
    $del_ids_str = json_encode($ids);
    return [
      "message" => "Records $del_ids_str delete",
      "rows" => count($ids),
    ];
  }

  public function getVacancyBy($relation_table, $id) {
    $src_table = R::load(TABLE[$relation_table], $id);
    $vacancies = $src_table->ownVacancyList;

    return arr_bean_to_arr($vacancies);
  }

  // ================= SUPPORT FUNCTION ========================

  public function check_fields($data) {
    $check_arr = $this->table_fields;
    
    foreach($data as $key => $value) {
      if (key_exists($key, $check_arr)) {
        $check_arr[$key] = true;
      }
    }

    $error_arr = array_filter($check_arr, function($field) {
      return !$field;
    });

    if (count($error_arr) > 0) {
      return [
        "ok"  => false,
        "message" => "some fields error",
        "error" => array_keys($error_arr)
      ];
    }
    return ["ok"  => true];
  }

  public function get_relation_tables() {
    $fields = array_keys($this->table_fields);
    $relations = array_values(array_filter($fields, function($key) {
      return substr_count($key, '_');
    }));
    $relation_tables = array_map(function($item) {
      return explode('_', $item)[0];
    }, $relations);

    return $relation_tables;
  }

  public function get_join_relations_query($relation_tables) {
    if (!$relation_tables) {
      return false;
    }

    $select_str = '';
    $join_str = '';

    foreach ($relation_tables as $index => $table) {
      $comma = $index !== count($relation_tables) - 1 ? ',' : '';
      $select_str .= "$table.name as {$table}_name{$comma}";
      $join_str .= "INNER JOIN $table ON {$this->table}.{$table}_id = $table.id ";
    }

    $query = 
    "SELECT {$this->table}.*, {$select_str}
    FROM {$this->table} 
    $join_str";

    return $query;
  }

  public function get_relations_array($data) {
    $relation_tables = $this->get_relation_tables();
    if (!$relation_tables) {
      return [];
    }

    $relations = [];
    foreach ($relation_tables as $key) {
      $relations[$key] = $data[$key . "_id"];
    }

    return $relations;
  }
}