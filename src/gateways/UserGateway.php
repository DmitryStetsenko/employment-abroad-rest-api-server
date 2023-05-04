<?php

class UserGateway extends Gateway {
  function __construct() {
    $this->table = TABLE["user"];
    $this->table_fields = [
      "login" => false,
      "password"  => false,
      "email"  => false,
      "phone" => false,
      
      "role_id" => false
    ];
  }

  public function getBy($relation_table, $id, $type='array') {
    $src_table = R::load(TABLE[$relation_table], $id);
    $vacancies = $src_table->ownUserList;

    if ($type != 'array') {
      return $vacancies;
    }

    return arr_bean_to_arr($vacancies);
  }
}