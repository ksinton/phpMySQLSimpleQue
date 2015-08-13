<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: x-requested-with");


require 'Slim/Slim.php';
require 'QueClass.php';
require 'DbConnectionClass.php';
 
//create the DB connection
$dbConnectionObj = new DbConnection();
$dbConnectionObj->getDbConnection();

//instantiate the Que Class
$queObj = new Que($dbConnectionObj->connection);
 
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
 
$app->get("/job/status/:jobId", function ($jobId) use( &$queObj) {
    echo $queObj->checkJob($jobId);
});

$app->post("/job/add/:url", function ($url) use( &$queObj) {
    echo $queObj->addQueJob($url);
});

$app->post("/que/process/", function () use( &$queObj) {

    $queObj->runQue();
});

$app->run();

?>