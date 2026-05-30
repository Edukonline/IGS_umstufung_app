<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'request#test', 'url' => '/api/test', 'verb' => 'GET'],
        ['name' => 'request#index', 'url' => '/api/requests', 'verb' => 'GET'],
        ['name' => 'request#create', 'url' => '/api/requests', 'verb' => 'POST'],
        ['name' => 'request#update', 'url' => '/api/requests/{id}', 'verb' => 'PUT'],
        ['name' => 'request#destroy', 'url' => '/api/requests/{id}', 'verb' => 'DELETE'],
        ['name' => 'request#submitAll', 'url' => '/api/submit_all', 'verb' => 'POST'],
        ['name' => 'settings#setAdminGroup', 'url' => '/api/settings/adminGroup', 'verb' => 'POST'],
    ]
];
