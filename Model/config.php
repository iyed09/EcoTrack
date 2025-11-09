<?php 


class config{
    private static $pdo=null;

    public static function getConnexion(){

        if(!isset(self::$pdo)){
                $servername="localhost";
                $dbname="smartinnovators";
                $username="root";
                $password="";
                try{
                self::$pdo=new PDO("mysql:host=$servername;dbname=$dbname",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
                ]);
            echo ("database connected sucessfully");
            }
           
         catch(Exception $e ){
            die("erreru".$e->getMessage());
        }
        }
        
return self::$pdo;
       
    }
    
}

?>