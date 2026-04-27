<?php
/**
 * essa classe e um teste do curso php na web
 * autor sergio.
 * 
 */


/**
 * isso e uma documentaçao
 */
class Mesagem{
    private $texto;
    private $css;

    public function sucesso():string
    {

        $this->css= 'alert alert-sucess';
        $this->texto =$this->filtrar("ola mundo");
        return $this;

    }

    public function renderizar():string
    {
    
        return "<div class='{$this->css}'>{$this->texto} </div>";
    
    }

    private function filtrar(string $mensagem):string
    {
        return filter_var($mensagem,FILTER_SANITIZE_SPECIAL_CHARS);
    }
}