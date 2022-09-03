<?php 
namespace Controller;

require_once("api/model/Database.php");


use model\Database;
use \PDO;


class Auth
{

    public static function auth($token)
    {  

        if (!preg_match('/Bearer/', $token))
        {
            http_response_code(403);
            echo json_encode([
                'Sucesso'=>0,
                'Mensagem'=>'Metódo Inválido!'
            ]);
            exit;
        }

       
        $tk = htmlspecialchars($token);
        $part = explode(".",$tk);
        $data = json_decode(
            base64_decode($part[1])
        );


        if ($data->exp < date("Y-m-d")) {
            http_response_code(403);
            echo json_encode([
                'Sucesso'=>0,
                'Mensagem'=>'Insira um token válido 1 '
            ]);
            exit;
        }elseif (empty($data->name)) {
            http_response_code(403);
            echo json_encode([
                'Sucesso'=>0,
                'Mensagem'=>'Insira um token válido 2 '
            ]);
            exit;
        }

        $sql = "SELECT * FROM `token` WHERE entities = :nome";

        $database = new Database;
        $conn = $database->connect(); 
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':nome', $data->name, PDO::PARAM_STR);
        
        $stmt->execute();
    
            if ($stmt->rowCount() > 0) {

                $dados = $stmt->fetch(PDO::FETCH_ASSOC);

                $valid = $dados['tk'];
                
                $tokenVal = trim($token, 'Bearer');


                if ($valid == trim($tokenVal)) {
                return true;
                }else{
                    http_response_code(403);
                    echo json_encode([
                        'Sucesso'=>0,
                        'Mensagem'=>'Insira um token válido 3'
                    ]);
                    exit;
                }

            }else{
                http_response_code(403);
                echo json_encode([
                    'Sucesso'=>0,
                    'Mensagem'=>'Insira um token válido 4'
                ]);
                exit;
            
            }

        
    }
    

    
}
?>
