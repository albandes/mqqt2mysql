<?php
error_reporting(E_ERROR | E_PARSE);

require("vendor/autoload.php");

use Albandes\db;
use Albandes\mqtt2mysql;
use Albandes\services;

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\Exceptions\MqttClientException;
use \PhpMqtt\Client\ConnectionSettings;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('DEBUG')->isBoolean();

// I transform the array into an object, it seems to me that the code is clearer
$objENV =  (object) $_ENV;

date_default_timezone_set($objENV->TIME_ZONE);
$debug = filter_var($objENV->DEBUG, FILTER_VALIDATE_BOOLEAN) ;

$service = new services();
$objLogger = $service->get_applogger();

$dsn = "mysql:dbname={$objENV->DB_NAME};port={$objENV->DB_PORT};host={$objENV->DB_HOSTNAME}";
$db = new DB($dsn,$objENV->DB_USERNAME,$objENV->DB_PASSWORD);

$mqtt2mysql = new mqtt2mysql($db);
$mqtt2mysql->set_debug($debug);

$sql = "INSERT INTO dtc_mqtt (temperature,id_sensor,mqtt_date,publication_date) VALUES (?,?,?,NOW(6))";

$clean_session  = false;
$mqttQos = getBrokerQOS($_ENV);

$connectionSettings = (new ConnectionSettings)
  ->setUsername($objENV->BROKER_USERNAME)
  ->setPassword($objENV->BROKER_PASSWORD);
  
try {
  
  $mqtt = new MqttClient($objENV->BROKER_URL, $objENV->BROKER_PORT,rand(5, 15), MqttClient::MQTT_3_1_1);
  $mqtt->connect($connectionSettings, $clean_session);
  
  $objLogger->info("Conected to broker ", ['broker' => $objENV->BROKER_URL, 'qos' => $mqttQos]);
  
  if($debug)
    printf("client connected\n");
  
  $mqtt->subscribe($objENV->BROKER_TOPIC, function (string $topic, string $message, bool $retained)  use ($sql, $mqtt2mysql, $objLogger) {
  
    $aPayload = json_decode($message);
  
    $objLogger->info("Received a message from broker.", ['topic' => $topic,'message' => $message]);

    if(!empty($aPayload->DS18B20->Temperature)){
      $arrayValues = array($aPayload->DS18B20->Temperature,$aPayload->DS18B20->Id,date("Y-m-d H:i:s", strtotime($aPayload->Time)));
      $mqtt2mysql->saveTopic($sql,$arrayValues);  
    }
    
    }, $mqttQos) ;
  
  $mqtt->loop(true);

  // Gracefully terminate the connection to the broker.
  $client->disconnect();
  
} catch (MqttClientException $e) {
    
  $logger->error('Subscribing to a topic using QoS 0 failed. An exception occurred.', ['exception' => $e]);

}

/**
  * getBrokerQOS
  *
  * @param  mixed $env
  * @return void
  */
function getBrokerQOS($env)
    {
    
    switch ($_ENV['BROKER_QOS']) {
        case 0:
            $mqttQos = MqttClient::QOS_AT_MOST_ONCE;
            break;
        case 1:
            $mqttQos = MqttClient::QOS_AT_LEAST_ONCE;
            break;
        case 2:
            $mqttQos = MqttClient::QOS_EXACTLY_ONCE;
            break;
    }
        
    return $mqttQos;      

}


