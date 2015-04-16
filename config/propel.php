<?php

return [
    'propel' => [
        'general' => [
            'project' => 'My App',
            'version' => '1.0',
        ],
        'paths' => [
            'projectDir' => app_path('resources/propel'),
            'schemaDir'  => app_path('database'),
            'outputDir'  => base_path('resources/propel'),
            'phpDir'     => app_path('Models'),
            'phpConfDir' => base_path('config/propel'),
            'sqlDir'     => base_path('database'),
            'migrationDir' => app_path('database/migrations'),
            'composerDir' => base_path(),
        ],

        'database' => [
            'connections' => array_map(function($item) {
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
                },
                array_filter(app('config')->get('database.connections'), function($item) {
                    return in_array($item['driver'], ['pgsql', 'mysql']);
                })
            ),
            'adapters' => [
                'mysql' => [
                    'tableType' => 'InnoDB'
                ],
            ],
        ],

        'runtime' => [
            'defaultConnection' => app('config')->get('database.default'),
            'connections' => [app('config')->get('database.default')],
        ],

        'generator' => [
            'defaultConnection' => app('config')->get('database.default'),
            'connections' => [app('config')->get('database.default')],

            'targetPackage' => '',
            'namespaceAutoPackage' => false,
        ],

    ]
];
