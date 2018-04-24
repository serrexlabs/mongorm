<?php

namespace Serrexlabs\Mongorm;


use MongoDB\Client;

class Mongo
{
    private static $mongoClient;

    /**
     * @return Client
     */
    public static function get()
    {
        if (isset(self::$mongoClient)) {
            return self::$mongoClient;
        }

        return self::getInstance();
    }

    /**
     * @return Client
     */
    private static function getInstance()
    {
        $host = config('services.mongo.host');
        $uri = 'mongodb://'. $host . '/';
        self::$mongoClient = new Client($uri);
        return self::$mongoClient;
    }

    /**
     * @return string
     */
    public static function getDatabase()
    {
        return config('services.mongo.database');
    }
}