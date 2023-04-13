<?php
include_once('./vendor/autoload.php');

/**
 * Chargement des variables d'environnement
 */
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1) );
$dotenv->load();

(string) $api_endpoint  = $_ENV['API_ENDPOINT'];
(string) $api_key       = $_ENV['API_KEY'];
(string) $mailer_dsn    = $_ENV['MAILER_DSN'];
const LOG_FILE          = './log/app.log';


include_once('./inc/mail.php');
include_once ('./inc/debug.php');
include_once ('./inc/download.php');
include_once ('./inc/file.php');
