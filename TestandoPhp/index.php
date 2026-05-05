 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
     integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

 <?php 


include 'sistema/Nucleo/Mensagem.php';
include 'sistema/Nucleo/Helpers.php';
include './sistema/Nucleo/PintarCarro.php';
require_once 'sistema/configuracao.php';
include 'sistema/Nucleo/Controlador.php';
use sistema\Nucleo\Mensagem;
use sistema\Nucleo\PintarCarro;
use sistema\Nucleo\Helpers;
use sistema\Nucleo\Controlador;




// echo (Helpers::retornarCpf('018.592.127-27@'));
// echo (Helpers::dataAtual());


$controlador = new Controlador('ols');
echo '<hr>';
var_dump($controlador);