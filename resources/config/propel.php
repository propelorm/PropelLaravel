<?php
return [
    'propel' => [
        'general' => [
            # The name of your project.
            'project' => 'My App',
            'version' => '1.0',
        ],
        ### Directories and Filenames ###
        'paths' => [
              # Directory where the project files (`schema.xml`, etc.) are located.
              # Default value is current path #
              'projectDir' => base_path('resources/propel'),

              # The directory where Propel expects to find your `schema.xml` file.
              'schemaDir' => base_path('database'),

              # The directory where Propel should output classes, sql, config, etc.
              # Default value is current path #
              'outputDir' => base_path('resources/propel'),

              # The directory where Propel should output generated object model classes.
              'phpDir' => app_path('Models'),

              # The directory where Propel should output the compiled runtime configuration.
              'phpConfDir' => base_path('config/propel'),

              # The directory where Propel should output the generated migrations.
              'migrationDir' => base_path('database/migrations'),

              # The directory where Propel should output the generated DDL (or data insert statements, etc.)
              'sqlDir' => base_path('database'),

              # Directory in which your composer.json resides
              'composerDir' => base_path(),
        ],
        ## All Database settings ##
        'database' => [
            # All database sources
            /***** We use data from database.php, please do not change this if you do not understand this code.  *****/
            'connections' => array_map(
                function($item)
                {
                    return [
                        'adapter' => $item['driver'],
                        # Connection class. One of the Propel\Runtime\Connection classes
                        'classname' => 'Propel\Runtime\Connection\ConnectionWrapper',
                         # The PDO dsn
                        'dsn' => $item['driver'] . ':host=' . $item['host'] . ';port=' . (empty($item['port']) ? '3306' : $item['port']) . ';dbname=' . $item['database'],
                        'user' => $item['username'],
                        'password' => $item['password'],
                        # Driver options. See http' => '//www.php.net/manual/en/pdo.construct.php
                        # options must be passed to the contructor of the connection object
                        'options' => [],
                        # See http' => '//www.php.net/manual/en/pdo.getattribute.php
                        # Attributes are set via `setAttribute()` method, after the connection object is created
                        'attributes' => [],
                        #Propel specific settings
                        'settings' => [
                            'charset' => $item['charset'],
                            #Array of queries to run when the database connection is initialized
                            'query' => [
                                'SET NAMES utf8 COLLATE utf8_unicode_ci, COLLATION_CONNECTION = utf8_unicode_ci, COLLATION_DATABASE = utf8_unicode_ci, COLLATION_SERVER = utf8_unicode_ci'
                            ],
                        ],
                        'slaves' => [
                            //[
                            //    'dsn' => 'mysq:host=slave-host-1;dbname=bookstore',
                            //],
                        ],
                    ];
                },
                array_filter(
                    app('config')->get('database.connections'),
                    function($item) {
                        return in_array($item['driver'], ['pgsql', 'mysql']);
                    }
                )
            ),

             ## Specific adapter settings
            'adapters' => [
                ## Mysql ##
                'mysql' => [
                      # Default table type
                      'tableType' => 'InnoDB', //MyIsam

                      # Keyword used to specify the table engine in the CREATE SQL statement.
                      # Defaults to 'ENGINE', users of MYSQL < 5 should use 'TYPE' instead.
                      'tableEngineKeyword' => 'ENGINE', //TYPE,
                ],
                ## Sqlite ##
                'sqlite' => [
                    'foreignKey' => null, //string
                    'tableAlteringWorkaround' => null, //boolean
                ],
                ## Oracle ##
                'oracle' => [
                      'autoincrementSequencePattern' => '${table}_SEQ',
                ],
            ],
        ],
        ## Migration settings ##
        'migrations' => [
            # Whether to specify PHP names that are the same as the column names.
            'samePhpName' => false,

            # Whether to add the vendor info. It does provide additional information (such as full-text indexes) which can
            # affect the generation of the DDL from the schema.
            'addVendorInfo' => false,

            # The name of migrations table
            'tableName' => 'propel_migration',

            # The name of the parser class
            # If you leave this property blank, Propel looks for an appropriate parser class, based on platform' => ' i.e.
            # if the platform is `MysqlPlatform` then parser is `\Propel\Generator\Reverse\MysqlSchemaParser`
            'parserClass' => null, //string,
        ],
        ## Reverse settings
        'reverse' => [
            # The connection to use to reverse the database
            'connection' => app('config')->get('database.default'),

            # Reverse parser class can be different from migration one
            # If you leave this property blank, Propel looks for an appropriate parser class, based on platform' => ' i.e.
            # if the platform is `MysqlPlatform` then parser is `\Propel\Generator\Reverse\MysqlSchemaParser`
            'parserClass' => null, //string
        ],

        ## Runtime settings ##
        'runtime' => [
            'defaultConnection' => app('config')->get('database.default'),
            # Datasources as defined in database.connections
            # This section affects config' => 'convert command
            'connections' => [
                app('config')->get('database.default'),
            ],
            ## Log and loggers definitions ##
            # For `type` and `level` options see Monolog documentation https' => '//github.com/Seldaek/monolog
            'log' => [
                'defaultLogger' => [
                    'type' => null, //string
                    'path' => null, //string
                    'level' => null, //integer
                ],
            ],
            ## Profiler configuration ##
            # To enable the profiler for a connection, set the `classname` option to \Propel\Runtime\Connection\ProfilerConnectionWrapper
            # see' => ' http' => '//propelorm.org/documentation/07-logging.html
            'profiler' => [
                'classname' => '\Propel\Runtime\Util\Profiler',
                'slowTreshold' => 0.1,
                'time' => [
                    'precision' => 3,
                    'pad' => 8,
                ],
                'memory' => [
                    'precision' => 3,
                    'pad' => 8,
                ],
                'innerGlue' => ':',
                'outerGlue' => '|',
            ],
        ],
        ## Generator settings ##
        'generator' => [
            'defaultConnection' => app('config')->get('database.default'),
            # Datasources as defined in database.connections
            'connections' => [
                app('config')->get('database.default'),
            ],

            # Add a prefix to all the table names in the database.
            # This does not affect the tables phpName.
            # This setting can be overridden on a per-database basis in the schema.
            'tablePrefix' => null, //string

            # Platform class name
            'platformClass' => 'Propel\Generator\Platform\MysqlPlatform',

            # The package to use for the generated classes.
            # This affects the value of the @package phpdoc tag, and it also affects
            # the directory that the classes are placed in. By default this will be
            # the same as the project. Note that the target package (and thus the target
            # directory for generated classes) can be overridden in each `<database>` and
            # `<table>` element in the XML schema.
            'targetPackage' => null, //string

            # Whether to join schemas using the same database name into a single schema.
            # This allows splitting schemas in packages, and referencing tables in another
            # schema (but in the same database) in a foreign key. Beware that database
            # behaviors will also be joined when this parameter is set to true.
            'packageObjectModel' => true,

            # If you use namespaces in your schemas, this setting tells Propel to use the
            # namespace attribute for the package. Consequently, the namespace attribute
            # will also stipulate the subdirectory in which model classes get generated.
            'namespaceAutoPackage' => false,

            'schema' => [
                # The schema base name
                'basename' => 'schema',
                # If your XML schema specifies SQL schemas for each table, you can copy the
                # value of the `schema` attribute to other attributes.
                # To copy the schema attribute to the package attribute, set this to true
                'autoPackage' => false,
                # To copy the schema attribute to the namespace attribute, set this to true
                'autoNamespace' => false,
                # To use the schema attribute as a prefix to all model phpNames, set this to true
                'autoPrefix' => false,

                # Whether to transform the XML schema using the XSL file.
                # This was used in previous Propel versions to clean up the schema, but tended
                # to hide problems in the schema. It is disabled by default since Propel 1.5.
                # The default XSL file is located under `resources/xsl/database.xsl`
                # and you can use a custom XSL file by changing the `propel.schema.xsl.file`
                # property.
                'transform' => false,
            ],
            ## Date/Time settings ##
            'dateTime' => [

                # Enable full use of the DateTime class.
                # Setting this to true means that getter methods for date/time/timestamp
                # columns will return a DateTime object when the default format is empty.
                'useDateTimeClass' => true,

                # Specify a custom DateTime subclass that you wish to have Propel use
                # for temporal values.
                'dateTimeClass' => 'DateTime',

                # These are the default formats that will be used when fetching values from
                # temporal columns in Propel. You can always specify these when calling the
                # methods directly, but for methods like getByName() it is nice to change
                # the defaults.
                # To have these methods return DateTime objects instead, you should set these
                # to empty values
                'defaultTimeStampFormat' => 'Y-m-d H:i:s',
                'defaultTimeFormat' => '%X',
                'defaultDateFormat' => '%x',
            ],
            'objectModel' => [
                # Whether to add generic getter/setter methods.
                # Generic accessors are `getByName()`, `getByPosition(), ` and `toArray()`.
                'addGenericAccessors' => true,
                # Generic mutators are `setByName()`, `setByPosition()`, and `fromArray()`.
                'addGenericMutators' => true,
                'emulateForeignKeyConstraints' => false,
                'addClassLevelComment' => true,
                'defaultKeyType' => 'phpName',
                'addSaveMethod' => true,
                'namespaceMap' => 'Map',

                # Whether to add a timestamp to the phpdoc header of generated OM classes.
                # If you use a versioning system, don't set this to true, or the classes
                # will be committed too often with just a date change.
                'addTimeStamp' => false,

                # Whether to support pre- and post- hooks on `save()` and `delete()` methods.
                # Set to false if you never use these hooks for a small speed boost.
                'addHooks' => true,

                # Some sort of "namespacing": All Propel classes with get the Prefix
                # "My_ORM_Prefix_" just like "My_ORM_Prefix_BookTableMap".
                'classPrefix' => '', //string

                # Identifier quoting may result in undesired behavior (especially in Postgres),
                # it can be disabled in DDL by setting this property to true.
                'disableIdentifierQuoting' => false,

                # Whether the generated `doSelectJoin*()` methods use LEFT JOIN or INNER JOIN
                # (see ticket:491 and ticket:588 to understand more about why this might be
                # important).
                'useLeftJoinsInDoJoinMethods' => true,

                # Pluralizer class (used to generate plural forms)
                # Use StandardEnglishPluralizer instead of DefaultEnglishPluralizer for better pluralization
                # (Handles uncountable and irregular nouns)
                'pluralizerClass' => '\Propel\Common\Pluralizer\StandardEnglishPluralizer',

                # Builder classes
                'builders' => [
                    'object' => '\Propel\Generator\Builder\Om\ObjectBuilder',
                    'objectstub' => '\Propel\Generator\Builder\Om\ExtensionObjectBuilder',
                    'objectmultiextend' => '\Propel\Generator\Builder\Om\MultiExtendObjectBuilder',
                    'tablemap' => '\Propel\Generator\Builder\Om\TableMapBuilder',
                    'query' => '\Propel\Generator\Builder\Om\QueryBuilder',
                    'querystub' => ' \Propel\Generator\Builder\Om\ExtensionQueryBuilder',
                    'queryinheritance' => '\Propel\Generator\Builder\Om\QueryInheritanceBuilder',
                    'queryinheritancestub' => '\Propel\Generator\Builder\Om\ExtensionQueryInheritanceBuilder',
                    'interface' => '\Propel\Generator\Builder\Om\InterfaceBuilder',
                    # SQL builders
                    'datasql' => '\Propel\Generator\Builder\Sql\PgsqlDataSQLBuilder',
                ],
            ],
        ],
    ],
];
