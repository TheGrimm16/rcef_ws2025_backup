<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => 'sdms_db_dev',
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],

        'survey_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DATABASE_SURVEY', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'seed_grower_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DATABASE_SEED_GROWERS', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'seed_coop_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_SEED_COOPERATIVES', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'geotag_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DATABASE_GEOTAG', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'registry_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DATABASE_REGISTRY', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'seeds_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_SEEDS', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		
       'hris_db' => [
            'driver' => 'mysql',
            'host' => '192.168.10.17',
            'port' => '3306',
            'database' => 'hris',
            'username' => 'efoi_hris',
            'password' => '3CVD7hHqcwVTZm9v',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        'rcep_farmers_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_RCEP_FARMERS', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		  'rcep_transfers_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DELIVERY', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],


        'ls_rcep_transfers_db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
			'port' => '3306',
            'database' => 'rcep_transfers_ws',
            'username' => 'jpalileo',
			'password' => 'P@ssw0rd',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],	
		
		'ls_inspection_db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
			'port' => '3306',
            'database' => 'rcep_delivery_inspection',
            'username' => 'jpalileo',
			'password' => 'P@ssw0rd',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'ls_seed_coop' => [
            'driver' => 'mysql',
            'host' => 'localhost',
			'port' => '3306',
            'database' => 'rcep_seed_cooperatives',
            'username' => 'jpalileo',
			'password' => 'P@ssw0rd',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'extension_connector' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => 'rcep_seed_cooperatives',
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pre_registration_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => 'rcep_farmer_registration',
            'username' => 'json',
            'password' => 'Zeijan@13',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'delivery_inspection_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DELIVERY', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'distribution_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DISTRIBUTION', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'inspector_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_INSPECTOR', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'central_delivery_inspection_db' => [
            'driver' => 'mysql',
            'host' => env('CENTRAL_HOST', '172.16.10.41'),
            'port' => env('CENTRAL_PORT', '3306'),
            'database' => env('DB_DELIVERY', 'forge'),
            'username' => env('CENTRAL_USERNAME', 'forge'),
            'password' => env('CENTRAL_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'central_distribution_db' => [
            'driver' => 'mysql',
            'host' => env('CENTRAL_HOST', '172.16.10.41'),
            'port' => env('CENTRAL_PORT', '3306'),
            'database' => env('DB_DISTRIBUTION', 'forge'),
            'username' => env('CENTRAL_USERNAME', 'forge'),
            'password' => env('CENTRAL_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		
        'farmer_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_FARMER', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'farmer_id_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_FARMER_ID', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'distribution_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_DISTRIBUTION', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		    'allocations_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_ALLOCATIONS', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'reports_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => '',
            'username' => 'rcef_web',
            'password' => 'SKF9wzFtKmNMfwy',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		
		'rcep_reports_db' => [
            'driver' => 'mysql',
            'host' => '172.16.10.41',
            'port' => '3306',
            'database' => env('DB_REPORTS', 'forge'),
            'username' => 'rcef_user4',
            'password' => 'lciz]eYhSaUbTcpF',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '172.16.10.41'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', '172.16.10.41'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
