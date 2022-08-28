Logging MongoDB operations in PHP applications
==============================================

This is an OSInet logger for MongoDB: it enables your PHP application to dump
the low-level operations performed by the `mongo` extension communicating with
your `mongod` or `mongos` instance.

It does *not* provide a PSR-3 logging destination, but a way to gather
information from your MongoDB-using code, and pushing it to your choice of PSR-3
logger (e.g Monolog in general PHP code, or `mongodb_watchdog` in Drupal 6/7/8).

This code **requires** the legacy `mongo` extension: these features have been
removed from the newer `mongodb` (phongo) extension:

* https://jira.mongodb.org/browse/PHPLIB-30 : won't fix
* https://jira.mongodb.org/browse/PHPLIB-38 : won't fix
* https://github.com/mongodb/mongo-c-driver/pull/80 : closed

(c) 2015 Ouest Systèmes Informatiques

Licensed under the General Public License version 2 or later.


Running the demo on its own
---------------------------

* ensure you have a development MongoDB instance without any important data
  available on `localhost:27017`. Any data in the instance could be lost.
* clone the repository

         https://github.com/FGM/mongodb_logger.git
         cd mongodb_logger

* run `composer install` to fetch dependencies
* that's it: you can now run the demo in the package itself

        `php loguser.php`.


Running the demo code as your application
-----------------------------------------

* ensure you have a development MongoDB instance without any important data
  available on `localhost:27017`. Any data in the instance could be lost.
* create a composer file

        mkdir my_demo
        cd my_demo
        composer init

* answer the usual Composer questions,  when Composer asks for requirements, request `fgm/mongodb_logger`, do not specify a version
* install dependencies

        composer install

* copy the `loguser.php` file to your project directory

        cp vendor/fgm/mongodb_logger/loguser.php .

* that's it: you can now run the demo as a separate application

        php loguser.php


Using the logging callbacks in your package
-------------------------------------------

* add the package to your Composer list of dependencies.
* instantiate the `Logger\Emitter` as your code needs it. A DIC may help.

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

* that'it. All your operations are now ready to be logged.


Trademarks
----------

* MongoDB is a registered trademark of MongoDB, Inc. http://mongodb.org/
* OSInet is a trademark of Ouest Systèmes Informatiques http://www.osinet.fr/
