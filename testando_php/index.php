 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
     integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

 <?php 

// declare(strict_types=1);
require_once 'sistema/configuracao.php';
include 'helpers.php';
include './sistema/Nucleo/Mensagem.php';
include './sistema/Nucleo/PintarCarro.php';


// $msg = new Mesagem();

// echo $msg->sucesso();
// echo '<hr>';
echo (new Mensagem())->erro("esse e o metodo magico");


// var_dump($msg);
$msg = new Mensagem();
echo $msg->sucesso('ola mundo')
    ->renderizar();


// echo (new Mensagem())->erro('mensagem de erro')->renderizar();

// echo "<div class='alert alert-primary'>ola</div>";

// $pintaCarro = new PintarCarro();

// $carro = $pintaCarro->escolhaCarro("amarelo","corsa",2022)->construirCarro();

// echo $carro;