<?php
declare (strict_types=1);

require 'db.php';
require 'func.php';

require 'src/headers.php';
require 'src/tables.php';

require 'src/Classes.php';
// spl_autoload_register(function($class) {
//   require __DIR__ . "/src/$class.php";
// });

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

$get_params = [];
$get_params_str = explode('?', $_SERVER["REQUEST_URI"])[1] ?? null;

if ($get_params_str) {
  $get_params = parse_params($get_params_str);

  $part = explode('?', $_SERVER["REQUEST_URI"]);
  $table = str_replace("/", "", trim($part[0]));
} else {
  $part = explode('/', $_SERVER["REQUEST_URI"]);
  $table = trim($part[1]);
}

$request = [];

if (!array_key_exists($table, TABLE)) {
  $request['status'] = '404';
  $request['message'] = 'table not found';

  http_response_code(404);
  echo json_encode($request);
  exit;
}

$part  = array_values(array_filter($part));

$resource = $part[1] ?? null;
$resource = $resource ? trim($resource) : null;
$resource = is_numeric($resource) ? (int) $resource : $resource;

// set controller ==============================================
$gateway_name = ucfirst($table);
$gateway = "{$gateway_name}Gateway";
$controller = new Controller(new $gateway);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $part, $resource, $get_params);
?>