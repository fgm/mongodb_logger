<?php
/**
 * @file
 * Emitter.php
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

namespace FGM\MongoDBLogger\Logger;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Emitter emits PSR-3 log messages for a MongoClient instance.
 *
 * @package FGM\MongoDBLogger\Logger
 */
class Emitter implements LoggerAwareInterface {

  use LoggerAwareTrait;

  /**
   * The default logging level used when emitting events for the logger.
   *
   * @var int
   */
  protected $level;

  /**
   * Set the logging level used for events.
   *
   * @param int $level
   *   Level is expected to be one of the \Psr\Log\LogLevel constants.
   */
  public function setLogLevel($level) {
    $this->level = $level;
  }

  /**
   * Helper wrapping the PSR-3 debug() method.
   *
   * @param array $message
   *   The message to pass to the logger, after prepending it with the caller.
   */
  protected function log(array $message) {
    $stack = debug_backtrace(FALSE);
    $caller = $stack[1]['function'];
    $message = ['op' => $caller] + $message;
    $this->logger->log($this->level, json_encode($message));
  }

  /**
   * Helper method to provide arguments for MongoDB stream contexts.
   *
   * @return array
   *   A default mapping for the MongoDB stream context.
   */
  public function buildContextArray() {
    $context = [
      'log_cmd_insert'  => [$this, 'insert'],
      'log_cmd_delete'  => [$this, 'delete'],
      'log_cmd_update'  => [$this, 'update'],
      'log_getmore'     => [$this, 'getMore'],
      'log_reply'       => [$this, 'reply'],
      'log_killcursor'  => [$this, 'killCursor'],

      // @see https://bugs.php.net/bug.php?id=70326
      'log_batchinsert' => [$this, 'batchInsert'],
      'log_query'       => [$this, 'query'],
    ];
    return $context;
  }

  /**
   * The default method to build a MongoDB stream context.
   *
   * @return resource
   *   A stream context resource.
   */
  public function createContext() {
    return stream_context_create([
      'mongodb' => $this->buildContextArray(),
    ]);
  }

  /**
   * Logging callback for 'delete' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $write_options
   *   The command write options, e.g. ['w' => 1].
   * @param array $delete_options
   *   The query part of the command.
   * @param array $protocol_options
   *   The protocol information for the operation, e.g. request_id.
   */
  public function delete(array $server, array $write_options, array $delete_options, array $protocol_options) {
    $event = [
      'deleteOptions' => $delete_options,
      'writeOptions' => $write_options,
      'protocolOptions' => $protocol_options,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'insert' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $document
   *   The inserted document.
   * @param array $write_options
   *   The command write options, e.g. ['w' => 1].
   * @param array $protocol_options
   *   The protocol information for the operation, e.g. request_id.
   */
  public function insert(array $server, array $document, array $write_options, array $protocol_options) {
    $event = [
      'document' => $document,
      'writeOptions' => $write_options,
      'protocolOptions' => $protocol_options,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'update' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $write_options
   *   The command write options, e.g. ['w' => 1].
   * @param array $update_options
   *   The update command options.
   * @param array $protocol_options
   *   The protocol information for the operation, e.g. request_id.
   */
  public function update(array $server, array $write_options, array $update_options, array $protocol_options) {
    $event = [
      'updateOptions' => $update_options,
      'writeOptions' => $write_options,
      'protocolOptions' => $protocol_options,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'batchInsert' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $documents
   *   The PHP documents to insert.
   * @param array $info
   *   Information about the batch.
   * @param array $options
   *   The update command options.
   */
  public function batchInsert(array $server, array $documents, array $info, array $options) {
    $event = [
      'count' => count($documents),
      'info' => $info,
      'options' => $options,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for documented (but nonexistent ?) 'writeBatch' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $write_options
   *   The command write options, e.g. ['w' => 1].
   * @param array $batch
   *   Information about the batch.
   * @param array $protocol_options
   *   The protocol information for the operation, e.g. request_id.
   */
  public function writeBatch(array $server, array $write_options, array $batch, array $protocol_options) {
    $event = [
      'batch' => $batch,
      'writeOptions' => $write_options,
      'protocolOptions' => $protocol_options,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'query' operation ($query command).
   *
   * @param array $server
   *   Server information.
   * @param array $arguments
   *   The query find() criteria.
   * @param array $query_options
   *   The query options, e.g. skip, limit.
   */
  public function query(array $server, array $arguments, array $query_options) {
    $event = [
      'arguments' => $arguments,
      'queryOptions' => $query_options,
    ];
    $this->log($event);
  }

  /**
   * Generic reply logging callback.
   *
   * @param array $server
   *   Server information.
   * @param array $message_headers
   *   Message headers returned by the server.
   * @param array $operation_headers
   *   Operation headers returned by the server.
   */
  public function reply(array $server, array $message_headers, array $operation_headers) {
    $event = [
      'operationHeaders' => $operation_headers,
      'messageHeaders' => $message_headers,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'getMore' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $info
   *   Information about the cursor for which more data is requested.
   */
  public function getMore(array $server, array $info) {
    $event = [
      'info' => $info,
    ];
    $this->log($event);
  }

  /**
   * Logging callback for 'killCursor' operation.
   *
   * @param array $server
   *   Server information.
   * @param array $info
   *   Information about the killed cursor.
   */
  public function killCursor(array $server, array $info) {
    $event = [
      'info' => $info,
    ];
    $this->log($event);
  }

}
