<?php

class EmployerGateway extends Gateway {
  function __construct() {
    $this->table = TABLE["employer"];
    $this->table_fields = [
      "name" => false,

      "login" => false,
      "password" => false,
      "email" => false,
      "phone" => false,

      "role_id" => false,
      "country_id" => false,
    ];
  }

  public function create($data){
    $result = $this->check_fields($data);
    if (!$result["ok"]) {
      return $result;
    }

    $created_data = date("Y-m-d H:i:s");

    $user_data = ["login", "password", "email", "phone"];
    $user = R::dispense(TABLE['user']);
    foreach($user_data as $field) {
      $user->$field = $data[$field];
    }

    $user->created = $created_data;
    
    $record = R::dispense($this->table);
    $record->name = $data["name"];
    $record->created = $created_data;

    $user->ownEmployerList[] = $record;
    R::store($user);

    $relations = $this->get_relations_array($data);
// !!!!!!!
    foreach( $relations as $relation_table => $id ) {
      $relation = R::load(TABLE[$relation_table], $id);
      $relation->ownEmployerList[] = $record;
      $relation->ownUserList[] = $user;
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

  public function delete($record) {
    $record = R::convertToBean($this->table, $record);
    $id = $record->id;

    R::hunt($this->table, 'id = ?', [$id]);
    R::hunt(TABLE["user"], 'id = ?', [$this->user_id]);

    return [
      "message" => "Vacancy $record->id deleted",
      "rows" => 1,
    ];
  }

  public function deleteMany($ids) {
    R::trashBatch($this->table, $ids);
    
    $del_ids_str = json_encode($ids);
    return [
      "message" => "Vacancy $del_ids_str delete",
      "rows" => count($ids),
    ];
  }
}