<?php

header("Access-Control-Allow-Headers: Range, Content-type");
header("Access-Control-Allow-Methods: GET, PUT, POST,DELETE");
header('Access-Control-Allow-Credentials: true');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Expose-Headers: Content-range');
header('Content-range: vacancy 0-10/20');
header('Content-type: application/json; charset=UTF-8');

date_default_timezone_set('Europe/Kyiv');
?>