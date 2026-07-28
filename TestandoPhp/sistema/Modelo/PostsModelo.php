<?php

namespace sistema\Modelo;

use ReturnTypeWillChange;
use sistema\Nucleo\Conexao;

Class PostsModelo
{


   public function ler(?int $id = null):array{
      // limite exite a quantidade de registro e offiset e o inicio do registro exibido
      // limit 1 offset 3

      $where = $id ? "where id={$id}" : '';
      $query = "SELECT * FROM `posts` {$where } ";
      $stmt = Conexao::getInstacia()->query($query);
      $resultado = $stmt->fetchAll();
      // var_dump($resultado);
      // echo '<hr>';
      return $resultado;  
   }
}



