<?php


namespace sistema\Suporte;

use Twig\Lexer as TwigLexer;
use Twin\lexer;
use sistema\Nucleo\Helpers;



Class Template {

    private \Twig\Environment $twig;


    public function __construct(string $diretorio)
    {
        $loader =  new \Twig\Loader\FilesystemLoader($diretorio);
        
        $this->twig = new \Twig\Environment($loader);
        
        $lexer = new TwigLexer($this->twig, array(
            $this->helpers()

        ));
        $this->twig->setLexer($lexer);
    }

    public function renderizar(string $view,array $dados = []):string
    {
        return $this->twig->render($view, $dados);
    }

    private function helpers():void{
        array(
            $this->twig->addFunction(
            new \Twig\TwigFunction('url', function(string $url ){
                return Helpers::url($url);
            })
            ),
             $this->twig->addFunction(
            new \Twig\TwigFunction('saudacao', function(){
                return Helpers::saudacao();
            }))

            );
        
    }
}