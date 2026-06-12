<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Anträge (CRUD + Workflow)
        ['name' => 'request#index', 'url' => '/api/requests', 'verb' => 'GET'],
        ['name' => 'request#create', 'url' => '/api/requests', 'verb' => 'POST'],
        ['name' => 'request#update', 'url' => '/api/requests/{id}', 'verb' => 'PUT'],
        ['name' => 'request#destroy', 'url' => '/api/requests/{id}', 'verb' => 'DELETE'],
        ['name' => 'request#submitAll', 'url' => '/api/submit_all', 'verb' => 'POST'],
        ['name' => 'request#decide', 'url' => '/api/requests/{id}/decide', 'verb' => 'POST'],

        // Admin-Einstellungen
        ['name' => 'settings#setAdminGroup', 'url' => '/api/settings/adminGroup', 'verb' => 'POST'],
        ['name' => 'settings#setSubjects', 'url' => '/api/settings/subjects', 'verb' => 'POST'],
        ['name' => 'settings#setClasses', 'url' => '/api/settings/classes', 'verb' => 'POST'],
    ],
];
