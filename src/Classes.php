<?php

require 'src/ErrorHandler.php';

require 'src/Controller.php';
require 'src/Gateway.php';

$exclude_arr = ["worker"];
require_gateways(TABLE, $exclude_arr);
