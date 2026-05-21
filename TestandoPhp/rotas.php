<?php

use Pecee\SimpleRouter\SimpleRouter;

SimpleRouter::setDefaultNamespace('sistema\Controlador');

SimpleRouter::get('/index.php', 'SiteControlador@index');

SimpleRouter::get('/sobre', 'SiteControlador@sobre');





// SimpleRouter::setDefaultNamespace('sistema\Controlador');

// SimpleRouter::get('/index.php', 'SiteControlador@index');

// SimpleRouter::start();