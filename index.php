<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
 
require 'vendor/autoload.php';
 
# Database config variables (for your MySQL database)
#Database=bks_iot_database;
#Data Source=eu-cdbr-azure-west-d.cloudapp.net;
#User Id=bdb1b94f9a56f9;
#Password=3a60b010

$config['displayErrorDetails'] = true;
$config['db']['host']   = "eu-cdbr-azure-west-d.cloudapp.net";
$config['db']['user']   = "bdb1b94f9a56f9";
$config['db']['pass']   = "3a60b010";
$config['db']['dbname'] = "bks_iot_database";
 
// bind the database settings to the app (your service) configuration
$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
 
# Database container function, you will simply call this as 'db' in your REST methods
 
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
 
 # GET endpoint to return all data for selected sensor in the sensor table
$app->get('/sensors/getsensordata', function (Request $request, Response $response) {
 
# Sample URL
# http://bksiotworkshop.azurewebsites.net/index.php/sensors/getsensordata?sensorid=2

   // Get the sensorid as passed in the calling URL
   // Actually gets all parameters that are specified in the URL
   $allGetVars = $request->getQueryParams();
   foreach($allGetVars as $key => $param){
   $sensor_id = $allGetVars['sensorid'];
}
    
   // Create database query to select all sensor data for the selected sensor
   // Use our db connection from app configuration
   $db = $this->db;
 
   // SQL SELECT query to select and return data from specified sensorid
   //$stmt = $db->prepare('SELECT sensor_id, time_stamp, sensor_id, sensor_type, value from sensors where sensor_id = :sensorid');
   
   $stmt = $db->prepare('SELECT sensor_type, value from sensors where sensor_id = :sensorid');
   
   
   // Parameterise query for security puposes
   $stmt->bindValue(':sensorid', $sensor_id);
   // Run query
   $stmt->execute();
   // Save query today using PDO object
   $sensordata = $stmt->fetchAll(PDO::FETCH_OBJ);
   // Close database connection
   $db = null;
   // return sensor data for given sensorid to calling client as JSON data
   $newResponse = $response->withHeader('Content-type', 'application/json');
   $newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
   $newResponse = $response->withJson($sensordata);
   return $newResponse;
});
 
# POST endpoint to post specific sensor data to the sensors database table
$app->post('/sensors/postsensordata', function (Request $request, Response $response) {

    // Get the sensorid, sensortype, and sensor value from parameters in the URL from calling client
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param){
    $sensor_id = $allGetVars['sensorid'];
    $sensor_type = $allGetVars['sensortype'];
    $sensor_value = $allGetVars['value'];
}
 
   // Create database query to insert row of sensor data
   // Use our db connection from app configuration
   $db = $this->db;
 
   // // SQL INSERT statement query to save data to sensors table
   $stmt = $db->prepare('INSERT into sensors (sensor_id, sensor_type, value) VALUES (:sensorid, :sensortype, :sensorvalue)');
 
   // Parameterise query for security puposes
   $stmt->bindValue(':sensorid', $sensor_id);
   $stmt->bindValue(':sensortype', $sensor_type);
   $stmt->bindValue(':sensorvalue', $sensor_value);;
   $stmt->execute();
 
   // Get id (auto incremented) of  newly inserted row of sensor data
   $lastid = $db->lastInsertId();
   $db = null;
 
   // Return id of inserted row to calling client in JSON
   $newResponse = $response->withHeader('Content-type', 'application/json');
   $newResponse = $response->withJson($lastid);
   return $newResponse;
});
 
$app->run(); 
		
?>




