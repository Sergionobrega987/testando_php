<?php


// arquivo de configuração do sistema

// echo "arquivo de configuraçao";

// AULA 31 DEFINE

// mysql:host=db;port=3306;dbname=dbkprev', 'root', 'root'
define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_NAME', 'dbkprev');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');

date_default_timezone_set('America/Sao_Paulo');
define('SITE_NOME','SITE  Sergio Testando' );
define('SITE_DESCRICAO','sergio site de alegria' );
define('URL_PRODUCAO','https://sergio.com.br' );
define('URL_DESENVOLVIMENTO','http://localhost:8005' );