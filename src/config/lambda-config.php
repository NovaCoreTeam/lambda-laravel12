<?php

return [

    /*
     | Websocket URL exposed to the Template/paper view.
     | env() belongs in config files only — never in runtime code, because
     | `php artisan config:cache` stops the .env file from being loaded.
     */
    'ws_url' => env('WS_URL'),

    /*
     | Lambda platform tables (api_config, krud, vb_schemas ...) live in this
     | schema on drivers that have schemas. Leave null to use the connection
     | default (PostgreSQL: first entry of search_path; SQL Server: dbo).
     */
    'schema' => env('LAMBDA_DB_SCHEMA'),

];
