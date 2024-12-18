<?php
define('ROOT_PATH', __DIR__);
define('SITE_URL', 'http://localhost/GreenLearn');

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'greenlearn_db');

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}