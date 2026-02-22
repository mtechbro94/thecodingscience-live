<?php
// router.php - Router for PHP Built-in Server
// This MUST be used with: php -S 127.0.0.1:8000 router.php

// Always route all requests through index.php
$docroot = dirname(__FILE__);
chdir($docroot);
$_SERVER['SCRIPT_NAME'] = '/index.php';

require $docroot . '/index.php';
