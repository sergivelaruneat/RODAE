<?php
return [
  'paths' => ['api/*','login','logout','register','refresh','password/*','email/*'],
  'allowed_methods' => ['*'],
  'allowed_origins' => ['http://localhost:5173','http://127.0.0.1:5173'],
  'allowed_headers' => ['*'],
  'exposed_headers' => ['Authorization'],
  'max_age' => 0,
  'supports_credentials' => false,
];