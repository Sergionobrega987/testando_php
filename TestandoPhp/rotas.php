<?php

use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
use Pecee\SimpleRouter\SimpleRouter;
use sistema\Controlador\ErroControlador;

SimpleRouter::setDefaultNamespace('sistema\Controlador');

SimpleRouter::get('/sobre', 'SiteControlador@sobre');

SimpleRouter::get('/', 'SiteControlador@index'); 


// SimpleRouter::error(function($request, \Exception $exception) {

//     if ($exception instanceof \Pecee\SimpleRouter\Exceptions\NotFoundHttpException) {

//         http_response_code(404);

//         echo (new \sistema\Controlador\ErroControlador)
//             ->erro404();

//         return;
//     }

//     throw $exception;
// });
try {
    SimpleRouter::start();

} catch (NotFoundHttpException $e) {

    http_response_code(404);

    echo (new ErroControlador)->erro404();

}

// SimpleRouter::start();