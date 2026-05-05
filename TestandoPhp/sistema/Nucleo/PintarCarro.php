<?php
namespace sistema\nucleo;

class PintarCarro
{

private  $cor;
private  $modelo;
private $ano;
private $texto;


public function escolhaCarro(string $cor,string $modelo, string $ano):PintarCarro{
    $this->cor = $cor;
    $this->modelo = $modelo;
    $this->ano = $ano;
    
    return $this;

}

public function construirCarro(){

    $carro = "a cor é: $this->cor <br>".
            "o Modelo é: $this->modelo <br>".
            "o ano é: $this->ano ";



    return $carro;
}



public function colocarTextoMaiuscula(string $texto){
    $textoMaiscula = mb_strtoupper($texto, 'UTF-8');
    return $textoMaiscula;
}








}