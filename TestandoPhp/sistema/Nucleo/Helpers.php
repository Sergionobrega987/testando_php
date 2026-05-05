<?php

namespace sistema\nucleo;

class Helpers
{
    // public static function validarCPF(string $cnpj): bool{

    

    //   return $; 

    // }
    public static function slug(string $string) : string{
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
    public static function formatValor(float $valor) : string
    {
        return number_format($valor,2,',','.');
    }


    public static function retornarCpf(string $valor): string{

      
      $cpf = self::limparNumero($valor);

      return $cpf;

    }

   public static function limparNumero(string $valor) : string {
        $numero = preg_replace('/[A-Z, a-z, \-, .,@]/','',$valor);
        
        return $numero;
    }

    public static function saudacao():string
    {
        date_default_timezone_set('America/Sao_Paulo');
        echo date("H") . "<hr>";
            $hora = date("H");
        
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

    public static function resumirTexto(string $numero,$limite, $continue = '...') 
    {
        return $numero ;  
    }

    public static function validarEmail(string $email) :bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validarURL(string $url):bool{

        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function localhost():bool
    {
        $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
        if($servidor =='localhost'){
            return true;
        }
        return false;
    }

    public static function  url(string $url):string
    {
        $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $ambiente = ($servidor == 'localhost' ? URL_DESENVOLVIMENTO : URL_PRODUCAO);


        if(str_starts_with($url, '/')){
            return $ambiente.$url;
        }
        return $ambiente. '/' . $url;

    }

    public static function dataAtual(): string{

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

    
    }

    
}