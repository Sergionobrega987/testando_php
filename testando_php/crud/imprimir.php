<?php

echo "chegou aqui<br>";

include("conexao.php");

if (!isset($conn)) {
    die("❌ conexão não existe");
}

$sql = "SELECT * FROM pessoa";

$teste = $conn->query($sql);

// if (!$teste) {
//     die("❌ erro SQL: " . $conn->error);
// }

// $result = mysqli_query($conn, $sql);

// var_dump($teste); // Process the result set
if ($teste->num_rows > 0) {
  // Output data of each rowtest
  while($row = $teste->fetch_assoc()) {
    echo "id: " . $row["id"]. "<br>". "Nome: " . $row["nome"]. "<br>" . $row["endereco"]. "<br>".  $row["email"]."<br>";
  }
} else {
  echo "0 results";
}

mysqli_close($conn);