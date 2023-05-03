<?php
require "libs/rb.php";

// R::setup('mysql:host=localhost;dbname=liftronc_employment_abroad', 'liftronc', '2I3Jiic8q7');
R::setup('mysql:host=127.0.0.1;dbname=employment-abroad', 'root', '');
R::Freeze(false); // true on production !!!

if ( !R::testConnection() ) {
  exit('Нет подключения');
}