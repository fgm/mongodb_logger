Logging for MongoDB in PHP applications
=======================================

This is a an OSInet logger for MongoDB.

(c) 2015 Ouest Systèmes Informatiques

Licensed under the General Public License version 2 or later.


Demo installation
-----------------

* run `composer install` to fetch dependencies
* ensure you have a development MongoDB instance without any important data
  available on localhost:27017. Any data in the instance could be lost.
* run `php loguser.php`.


Using the logging callbacks in your package
-------------------------------------------

* make sure you can use GPL-2.0+ code before proceeding
* add the package to your Composer list of dependencies.
* instantiate the Logger\Emitter as your code needs it. A DIC may help.

        use FGM\MongoDBLogger\Logger\Emitter;
        use Psr\Log\LogLevel;

        // Build an emitter. 
        $emitter = new Emitter();
        
        // Inject your own PSR-3 logger.
        $emitter->setLogger($logger);
        
        // You can choose your log level.
        $emitter->setLogLevel(LogLevel::DEBUG);
        
* use the emitter to create the mongodb:// context for your app.
         
        $context = $emitter->createContext();
        
* pass the context you just built when connecting to MongoDB.

        $client = new \MongoClient($server, $options, ['context' => $context]);

* that'it. All your operations are now logged.


Trademarks
----------

* MongoDB is a registered trademark of MongoDB, Inc. http://mongodb.org/
* OSInet is a trademark of Ouest Systèmes Informatiques http://www.osinet.fr/
