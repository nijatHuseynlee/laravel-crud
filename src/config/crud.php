<?php

return [
   "contract_form" => "{class}Interface",

   "repositories" => [
   //  "App\Repositories\Contracts" => "App\Repositories\",
   ],

   "services" => [
   //  "App\Services\Contracts" => "App\Services\",
   ],

   'items_count_per_page' => env("CRUD_ITEMS_COUNT_PER_PAGE", 20),

   'max_items_count_per_page' => env("CRUD_MAX_ITEMS_COUNT_PER_PAGE", 100),
];
