<?php
return [
    'backend' => [
        'frontName' => 'admin'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => '${CRYPT_KEY}'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'db',
                'dbname' => 'magento',
                'username' => 'magento',
                'password' => 'magento',
                'active' => '1',
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => 'redis',
            'port' => '6379',
            'password' => '',
            'timeout' => '3',
            'persistent_identifier' => '',
            'database' => '2',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '6',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000'
        ]
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '05c_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => 'redis',
                    'database' => '0',
                    'port' => '6379'
                ]
            ],
            'page_cache' => [
                'id_prefix' => '05c_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => 'redis',
                    'database' => '1',
                    'port' => '6379'
                ]
            ]
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1
    ],
    'install' => [
        'date' => 'Tue, 09 Aug 2022 08:35:03 +0000'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'http_cache_hosts' => [
        [
            'host' => 'varnish',
            'port' => '80'
        ]
    ],
    'downloadable_domains' => [
        '${PROJECT_HOST}',
    ],
    'system' => [
        'default' => [
            'smile_elasticsuite_core_base_settings' => [
                'es_client' => [
                    'servers' => 'elasticsearch:9200',
                    'enable_https_mode' => 0,
                    'http_auth_user' => '',
                    'http_auth_pwd' => '',
                    'enable_http_auth' => false
                ]
            ]
        ]
    ]
];
