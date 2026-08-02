<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\PostsModelo;

class SiteControlador extends Controlador
{

    public function __construct()
    {
    parent::__construct('sistema/templates/site/views');

    }

    public function index() :void{
        $posts = (new PostsModelo())->ler();

        
        echo  $this->template->renderizar
        ('index.html',[
            'posts' =>$posts,
        ]);
       
    }
    public function sobre() :void{
        
        echo  $this->template->renderizar('sobre.html');
        
    }


    
}