<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Anträge (CRUD + Workflow)
        ['name' => 'request#index', 'url' => '/api/requests', 'verb' => 'GET'],
        ['name' => 'request#create', 'url' => '/api/requests', 'verb' => 'POST'],
        ['name' => 'request#update', 'url' => '/api/requests/{id}', 'verb' => 'PUT'],
        ['name' => 'request#destroy', 'url' => '/api/requests/{id}', 'verb' => 'DELETE'],
        ['name' => 'request#restore', 'url' => '/api/requests/{id}/restore', 'verb' => 'POST'],
        ['name' => 'request#submitAll', 'url' => '/api/submit_all', 'verb' => 'POST'],
        ['name' => 'request#decide', 'url' => '/api/requests/{id}/decide', 'verb' => 'POST'],

        // Admin-Einstellungen (atomar)
        ['name' => 'settings#save', 'url' => '/api/settings', 'verb' => 'POST'],
    ],
];
