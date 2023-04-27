<?php
require "libs/rb.php";

R::setup('mysql:host=127.0.0.1;dbname=employment-abroad', 'root', '');
R::Freeze(false); // true on production !!!

if ( !R::testConnection() ) {
  exit('Нет подключения');
}