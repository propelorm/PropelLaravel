<?php

return [
    'propel' => [
        'general' => [
            'project' => 'MyProject',
            'version' => '1.0',
        ],

        'paths' => [
            'projectDir' => app_path('/propel'),
            'schemaDir'  => app_path('/database'),
            'outputDir'  => base_path('/resources/propel'),
            'phpDir'     => app_path('/Models'),
            'phpConfDir' => base_path('/config/propel'),
            'sqlDir'     => base_path('/database'),
            'migrationDir' => app_path('/database/migrations'),
            'composerDir' => base_path(),
        ],

        'database' => [
            'connections' => array_map(function($item) {
                if (in_array($item['driver'], ['pgsql', 'mysql'])) {
                    return [
                        'adapter'  => $item['driver'],
                        'dsn'      => $item['driver'] . ':host=' . $item['host'] . ';port=' . (empty($item['port']) ? '3306' : $item['port']) . ';dbname=' . $item['database'],
                        'user'     => $item['username'],
                        'password' => $item['password'],
                        'settings' => [
                            'charset' => $item['charset'],
                            'queries' => [
                                'SET NAMES utf8 COLLATE utf8_unicode_ci, COLLATION_CONNECTION = utf8_unicode_ci, COLLATION_DATABASE = utf8_unicode_ci, COLLATION_SERVER = utf8_unicode_ci'
                            ],
                        ],
                    ];
                }
            }, app('config')->get('database.connections')),
            'adapters' => [
                'mysql' => [
                    'tableType' => 'InnoDB'
                ],
            ],
        ],

        'runtime' => [
            'defaultConnection' => app('config')->get('database.default'),
            'connections' => array_filter( app('config')->get('database.connections'), function($item) {
                return in_array($item['driver'], ['pgsql', 'mysql']);
            })
        ],

        'generator' => [
            'defaultConnection' => app('config')->get('database.default'),
            'connections' => array_filter( app('config')->get('database.connections'), function($item) {
                return in_array($item['driver'], ['pgsql', 'mysql']);
            }),

            'targetPackage' => '',
            'namespaceAutoPackage' => false,
        ],

    ]
];
