<?php
/**
 * @file
 * TimingLoggerInterface.
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

namespace FGM\MongoDBLogger\Logger;


/**
 * Interface TimingLoggerInterface is an option for loggers, adding a timer.
 *
 * @package FGM\MongoDBLogger\Logger
 */
interface TimingLoggerInterface {
  /**
   * Start the logger built-in timer.
   */
  public function startLap();

}
