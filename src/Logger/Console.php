<?php

/**
 * @file
 * Console.php
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

namespace FGM\MongoDBLogger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Class Console is a trivial timing logger. Use a real logger in your app.
 *
 * @package FGM\MongoDBLogger\Logger
 */
class Console extends AbstractLogger implements LoggerInterface, TimingLoggerInterface {

  use LoggerTrait;

  protected $t0;

  /**
   * Start the timer.
   */
  public function startLap() {

    $this->t0 = microtime(TRUE);
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   *   A LogLevel constant.
   * @param string $message
   *   If empty and a lap is started, will be replaced by lap timing info.
   * @param array $context
   *   Ignored.
   */
  public function log($level, $message, array $context = array()) {
    if (empty($message)) {
      if (!empty($this->t0)) {
        $message = sprintf("%d msec.\n", (microtime(TRUE) - $this->t0) * 1000);
      }
      else {
        $message = "\n";
      }
    }
    echo "$message\n";
  }

}
