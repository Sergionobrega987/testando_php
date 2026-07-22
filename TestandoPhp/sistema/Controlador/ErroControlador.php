<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;

class ErroControlador extends Controlador
{
    public function __construct()
    {
        parent::__construct('sistema/templates/site/views');
    }

    /**
     * Página 404
     */
    public function erro404(): string
    {
        http_response_code(404);

        return $this->template->renderizar('404.html', [
            'titulo' => 'Página não encontrada'
        ]);
    }

    /**
     * Página 500 (opcional)
     */
    public function erro500(): string
    {
        http_response_code(500);

        return $this->template->renderizar('500.html', [
            'titulo' => 'Erro interno do servidor'
        ]);
    }
}