<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */
    
    // Package name is root namespace by default
    'namespace' => null,
    
    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */
    
    'stubs' => [
        'enabled' => false,
        'path' => base_path() . '/vendor/nwidart/laravel-modules/src/Commands/stubs',
        
        // TODO: duplicate this section to a "default" key that's used when generating a module?
        'files' => [
            //            'start' => 'start.php',
            //            'routes' => 'Http/routes.php',
            'module.json' => 'module.json',
            //            'views/index' => 'Resources/views/index.blade.php',
            //            'views/master' => 'Resources/views/layouts/master.blade.php',
            
            // TODO: move to e.g. config/modules/blog.php dir
            //            'scaffold/config' => 'src/config/config.php',
            //            'composer' => 'composer.json',
        ],
        //        'replacements' => [
        //            'start' => ['LOWER_NAME'],
        //            'routes' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
        //            'module.json' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
        //            'views/index' => ['LOWER_NAME'],
        //            'views/master' => ['STUDLY_NAME'],
        //            'scaffold/config' => ['STUDLY_NAME'],
        //            'composer' => [
        //                'LOWER_NAME',
        //                'STUDLY_NAME',
        //                'VENDOR',
        //                'AUTHOR_NAME',
        //                'AUTHOR_EMAIL',
        //                'MODULE_NAMESPACE',
        //            ],
        //        ],
    ],
    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path used for save the generated module. This path also will added
        | automatically to list of scanned folders.
        |
        */
        
        'modules' => base_path('modules'),
        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules assets path.
        |
        */
        
        'assets' => public_path('modules'),
        /*
        |--------------------------------------------------------------------------
        | The migrations path
        |--------------------------------------------------------------------------
        |
        | Where you run 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */
        
        'migration' => base_path('database/migrations'),
        
        /*
         * The directories to create when generating a module.
         */
        
        'generator' => [
            'provider' => 'src/Providers',
            //            'assets' => 'Assets',
            // TODO: move to e.g. config/modules/blog.php dir
            //            'config' => 'config',
            
            //            'command' => 'src/Console',
            //            'event' => 'Events',
            //            'listener' => 'Events/Handlers',
            //            'migration' => 'Database/Migrations',
            //            'model' => 'Entities',
            //            'repository' => 'Repositories',
            //            'seeder' => 'Database/Seeders',
            //            'controller' => 'Http/Controllers',
            //            'filter' => 'Http/Middleware',
            //            'request' => 'Http/Requests',
            //            'lang' => 'Resources/lang',
            //            'views' => 'Resources/views',
            //            'test' => 'Tests',
            //            'jobs' => 'Jobs',
            //            'emails' => 'Emails',
            //            'notifications' => 'Notifications',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Scan Path
    |--------------------------------------------------------------------------
    |
    | Here you define which folder will be scanned. By default will scan vendor
    | directory. This is useful if you host the package in packagist website.
    |
    */
    
    'scan' => [
        'enabled' => false,
        'paths' => [
            base_path('vendor/*/*'),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Composer File Template
    |--------------------------------------------------------------------------
    |
    | Here is the config for composer.json file, generated by this package
    |
    */
    
    // TODO: ditch composer wrapper and update in ModuleGenerator to use root vendor and author config keys
    'composer' => [
        'vendor' => 'nwidart',
        'author' => [
            'name' => 'Nicolas Widart',
            'email' => 'n.widart@gmail.com',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled' => false,
        'key' => 'laravel-modules',
        'lifetime' => 60,
    ],
    /*
    |--------------------------------------------------------------------------
    | Choose what laravel-modules will register as custom namespaces.
    | Setting one to false will require to register that part
    | in your own Service Provider class.
    |--------------------------------------------------------------------------
    */
    'register' => [
        'translations' => true,
    ],
];
