<?php 

function slug(string $string) : string{

// negócio platão


   $mapa = [
        'ó' => "o",
        'ã' => "a"
        ];
        
 $slug = strtr($string, $mapa);

   



return $slug;
}


function contarTempo(string $data){
$agora = strtotime(date("Y/m/d H:i:s"));

echo $agora;
echo "<hr>";
var_dump($data);
}




/**
 *  * essa funçao formata para moeda um numero float
 * @param float $valor
 * @return string
 * 
 */
function formatValor(float $valor) : string
{
    return number_format($valor,2,',','.');
}


function saudacao():string
{
date_default_timezone_set('America/Sao_Paulo');
   echo date("H") . "<hr>";
    $hora = date("H");
// $hora = 3;

    // if($hora >=0 AND $hora <=5){
    //     $saudacao = 'boa madrugada';

    // }elseif($hora >6 AND $hora <=12){
    //     $saudacao = 'boa dia';
    // }elseif($hora >12 AND $hora <=18){
    //     $saudacao = 'boa tarde';

    // }else{
    //      $saudacao ="boa noite";
    // }
    switch ($hora){
        case $hora >=0 AND $hora <=5:
            $saudacao = 'boa madrugada';
            break;
        case $hora >=6 AND $hora <=12:
             $saudacao = 'boa dia';    
         break;    
        case $hora >=13 AND $hora <=18:
             $saudacao ="boa Tarde";
          break; 
        default:
            $saudacao ="noite";
            break;
    }


    
 return $saudacao;
}

function resumirTexto(string $numero,$limite, $continue = '...') 
{
return $numero ;  
}

function validarEmail(string $email) :bool
{
return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarURL(string $url):bool{

return filter_var($url, FILTER_VALIDATE_URL);
}

function localhost():bool
{
    $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
    if($servidor =='localhost'){
        return true;
    }
    return false;
}

function  url(string $url):string
{
    $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
    $ambiente = ($servidor == 'localhost' ? URL_DESENVOLVIMENTO : URL_PRODUCAO);


    if(str_starts_with($url, '/')){
        return $ambiente.$url;
    }
    return $ambiente. '/' . $url;

}

function dataAtual(): string{

    $diaMes = date('d');
    $diaSemana = date('w');
    $mes = date('n')-1;
    $ano = date('Y');

    $nomeDiaSemana = 
        [
            "domingo",
            "segunda",
            "terça-feira",
            "quarta-feira",
            "quinta-feira",
            "sexta-feira",
            "sábado"
        ];

    $nomeMesAno = 
        [
            "janeiro",
            "fevereiro",
            "março",
            "abril",
            "maio",
            "junho",
            "julho",
            "agosto",
            "setembro",
            "outubro",
            "novembro",
            "dezembro"
        ];

    $dataCompleta = "Rio de janeiro, $nomeDiaSemana[$diaSemana], $diaMes de  $nomeMesAno[$mes] de $ano.";
   
    return $dataCompleta;

// retornar a data formatada: exemplo rio, 12 de janeiro de 2026.

// return  "o dia do mes e: $diaMes. <br>
//          o dia da semana e: $diaSemana . <br>
//          o dia Mes do ano e: $mes <br>
//          o ano e: $ano";
}

// function slug(string $string) : string{
// function testarOMatch(string $string):string{


//     $grupoAmarela =['melao','banana'],
//     $grupoVermelho$ = ['morango','melancia']



// return $string;
// }