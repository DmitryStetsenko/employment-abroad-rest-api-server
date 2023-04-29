<?php

class Controller {

  public function __construct($gateway) {
    $this->gateway = $gateway;
  }

  public function processRequest(string $method, $part, $resource, ?array $get_params): void {
    
    if($get_params) {

      $this->processFilterRequest($method, $get_params);
      
    }
    else if ($resource) {

      if (is_numeric($resource)) {
        $this->processResourceRequest($method, $resource);
      } else {
        $this->processExtraResourceRequest($method, $part, $resource);
      }

    } else {

      $this->processCollectionRequest($method);

    }
  }
  
  private function processFilterRequest(string $method, array $get_params): void {
    switch ($method) {
      case "GET":
        $filter = $get_params["filter"] ?? null;
        if($filter) {
          if (array_key_exists("id", $filter)) {
            $ids = [...$filter["id"]];
            echo json_encode($this->gateway->getMany($ids));
            break;
          }
        }

        echo json_encode($this->gateway->getList($get_params));
        break;
    }
  }

  private function processResourceRequest(string $method, int $id): void {
    $record = $this->gateway->get($id);

    if ( ! $record) {
      http_response_code(404);
      echo json_encode(["message" => "Record not found"]);
      return;
    }

    switch ($method) {
      case "GET":
        echo json_encode($record);
        break;

      case "PUT":
        $data = (array) json_decode(file_get_contents("php://input", true));

        if (!$data) {
          $result = [
            "ok"  => false,
            "message" =>"not data for update"
          ];
          echo json_encode($result);
          break;
        }

        $result = $this->gateway->update($record, $data);
        echo json_encode($result);
        break;
      
      case "DELETE":
        $result = $this->gateway->delete($record);
        echo json_encode([
          "message" => "Record $id deleted",
          "rows" => 1
        ]);
        break;
    }

  }

  private function  processExtraResourceRequest(string $method, $part, $resource): void {
    exit(json_encode($part));

    if ($method !== "GET") {
      return;
    }

    switch ($resource) {
      case "full":
        http_response_code(200);
        $records = $this->gateway->getFull();
        echo json_encode($records);
        break;
      
      default:
        http_response_code(404);
        echo json_encode([
          "message" => "unknown resource '{$resource}' !!!",
        ]);
        break;
    }
  }

  private function processCollectionRequest(string $method): void {
    switch ($method) {
      case "GET":
        echo json_encode($this->gateway->getAll());
        break;

      case "POST":
        $data = $_POST ? $_POST : (array) json_decode(file_get_contents("php://input", true));
        $result = $this->gateway->create($data);
        if (!$result["ok"]) {
          http_response_code(422);
        } else {
          http_response_code(201);
        }
        
        echo json_encode($result);
        break;

      case "PUT":
        $raw_data = (array) json_decode(file_get_contents("php://input", true));

        $ids = $raw_data["id"] ?? null;
        $data = $raw_data["data"] ?? null;

        if (!$ids) {
          $result = [
            "ok"  => false,
            "message" =>"not ids for update"
          ];
          echo json_encode($result);
          break;
        }

        if (!$data) {
          $result = [
            "ok"  => false,
            "message" =>"not data for update"
          ];
          echo json_encode($result);
          break;
        }

        $result = $this->gateway->updateMany($ids, $data);
        echo json_encode($result);
        break;

      case "DELETE":
        $raw_data = (array) json_decode(file_get_contents("php://input", true));

        $ids = $raw_data["id"] ?? null;

        if (!$ids) {
          $result = [
            "ok"  => false,
            "message" =>"not ids for delete"
          ];
          echo json_encode($result);
          break;
        }

        $result = $this->gateway->deleteMany($ids);
        echo json_encode($result);
        break;
    }
  }
}