<?php
require __DIR__.'/../vendor/autoload.php';
use App\Scrapper;
$scrap = New Scrapper();
//internally set automatically to get last_page information
//$scrap->setPageCounter(2);
//internally is set to 10
//$scrap->setPageSize(10);
$scrap->getData();
