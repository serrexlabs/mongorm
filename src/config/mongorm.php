<?php

return [

    'mongo' => [
        'host' => env('MONGO_HOST', ""),
        'database' => env('MONGO_DB', ''),
        'entity_namespace' => "App\\Entity\\"
    ],

];
