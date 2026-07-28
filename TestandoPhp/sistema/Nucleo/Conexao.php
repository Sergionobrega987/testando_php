<?php
namespace sistema\Nucleo;

use Exception;
Use PDO;
Use PDOException;


Class Conexao
{
    private static $instacia;

    public static function getInstacia():PDO
    {
        if(empty(self::$instacia)){
            try {
                    // self::$instacia  = new PDO('mysql:host=db;port=3306;dbname=dbkprev', 'root', 'root',
                    self::$instacia  = new PDO('mysql:host='. DB_HOST . ';port='. DB_PORT. ';dbname='.DB_NAME.'', DB_USER, DB_PASSWORD,
                                                [
                                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                                                    // converte qualquer resultado com um objeto anonimo
                                                    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ,
                                                    // garante que o mesmo nome das colunas do banco seja utilizado
                                                    PDO::ATTR_CASE=>  PDO::CASE_NATURAL
                                                ]);
                                                // mysql:host=db;dbname=dbkprev
            } catch (PDOException $e) {
                die("erro de conexao:: " . $e->getMessage());
            }
        }
        return self::$instacia;
    }
    protected function __construct()
    {
    }
    private function __clone():void
    {
        
    }

}