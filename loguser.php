<?php

/**
 * @file
 * A demonstration of the MongoDB logging mechanism.
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

use FGM\MongoDBLogger\Logger\Console;
use FGM\MongoDBLogger\Logger\Emitter;
use FGM\MongoDBLogger\Logger\TimingLoggerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

require __DIR__ . '/vendor/autoload.php';

/**
 * Connect to the server and select a demo collection.
 *
 * @param string $server
 *   The server name, in MongoClient::__construct() format.
 * @param array $options
 *   The connection options, in MongoClient::__construct() format.
 * @param array $driver_options
 *   The driver options, in MongoClient::__construct() format.
 * @param string $db_name
 *   The name of the database to select once connected.
 * @param string $collection_name
 *   The name of the collection on which to run the demos.
 * @param \Psr\Log\LoggerInterface $logger
 *   A PSR-3 logger instance.
 *
 * @return \MongoCollection
 *   The demo collection.
 */
function get_collection($server, array $options, array $driver_options, $db_name, $collection_name, LoggerInterface $logger) {
  $client = new \MongoClient($server, $options, $driver_options);
  $db = $client->selectDB($db_name);
  $c = $db->selectCollection($collection_name);
  $logger->debug("Client: $client, DB: $db, Collection: $c\n");
  return $c;
}

/**
 * Demonstrate logging on collection.drop().
 *
 * @param \MongoCollection $collection
 *   The demo collection.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger instance.
 */
function drop_demo(MongoCollection $collection, LoggerInterface $logger) {
  $logger->debug("Dropping $collection");
  $logger instanceof TimingLoggerInterface && $logger->startLap();
  $collection->drop();
  $logger->debug('');
}

/**
 * Prepare a PHP documents array.
 *
 * @param int $max
 *   The number of documents in the collection.
 *
 * @return array
 *   The documents array.
 */
function prepare_documents($max) {
  $docs = [];
  for ($i = 0; $i < $max; $i++) {
    $docs[$i] = ["foo_$i" => "bar_$i", "index" => $i];
  }
  return $docs;
}

/**
 * Demonstrate logging a collection.insert() loop.
 *
 * @param \MongoCollection $collection
 *   The demo collection.
 * @param int $max
 *   The maximum number of documents to insert in a loop.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger instance.
 */
function insert_demo(MongoCollection $collection, $max, LoggerInterface $logger) {
  $logger->debug("Inserting $max documents in a loop.");
  $docs = prepare_documents($max);
  $logger instanceof TimingLoggerInterface && $logger->startLap();
  for ($i = 0; $i < count($docs); $i++) {
    $collection->insert($docs[$i]);
  }
  $logger->debug('');
}

/**
 * Demonstrated logging a collection.batchInsert().
 *
 * @param \MongoCollection $collection
 *   The demo collection.
 * @param int $max
 *   The maximum number of documents to insert in a loop.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger instance.
 */
function batch_insert_demo(MongoCollection $collection, $max, LoggerInterface $logger) {
  $logger->debug("Batch-inserting $max documents");
  $docs = prepare_documents($max);
  $logger instanceof TimingLoggerInterface && $logger->startLap();
  $collection->batchInsert($docs);
  $logger->debug('');
}

/**
 * Demonstrate logging a collection.find().
 *
 * Notice that if $max is > 100, getMore events will be triggered.
 *
 * Progress info is logged every 100ms if applicable.
 *
 * @param \MongoCollection $collection
 *   The demo collection.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger instance.
 */
function find_demo(MongoCollection $collection, LoggerInterface $logger) {
  $logger->debug("Finding documents");
  $logger instanceof TimingLoggerInterface && $logger->startLap();
  $cursor = $collection->find([])->sort(['_id' => 1]);
  $t1 = $t0 = microtime(TRUE);
  $count = 0;
  foreach ($cursor as $doc) {
    $count++;
    if (microtime(TRUE) - $t1 > 0.1) {
      $logger->debug($doc['index'] . ' ');
      $t1 = microtime(TRUE);
    }
  }
  unset($cursor);
  $logger->debug('');
}

/**
 * Demonstrate logging a collection.remove().
 *
 * @param \MongoCollection $collection
 *   The demo collection.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger instance.
 */
function remove_demo(MongoCollection $collection, LoggerInterface $logger) {
  $logger->debug("Removing documents");
  $logger instanceof TimingLoggerInterface && $logger->startLap();
  $collection->remove();
  $logger->debug('');
}

$max = 100000;

$logger = new Console();
$emitter = new Emitter();
$emitter->setLogger($logger);
$emitter->setLogLevel(LogLevel::DEBUG);
$context = $emitter->createContext();

$collection = get_collection(
  $server = 'mongodb://localhost:27017',
  $options = [
    'connect' => TRUE,
    'readPreference' => MongoClient::RP_SECONDARY_PREFERRED,
  ],
  $driver_options = [
    'context' => $context,
  ],
  'logger_demo',
  'coll',
  $logger
);

$logger->debug("Testing with $max documents");
drop_demo($collection, $logger);
insert_demo($collection, min($max, 2), $logger);
drop_demo($collection, $logger);
batch_insert_demo($collection, $max, $logger);
find_demo($collection, $logger);
remove_demo($collection, $logger);
