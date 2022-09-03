<?php 
namespace Controller;

require_once("api/model/Database.php");


use model\Database;
use \PDO;
use PDOException;


class Log extends Database
{
    
    public function valida($data)
    {
       
       $id_log = htmlspecialchars($data);
        
        try {

            $sql = "SELECT * FROM `user` WHERE id_log = :id_log";

            $conn = $this->connect(); 
            $stmt = $conn->prepare($sql);

            $stmt->bindValue(':id_log', $id_log, PDO::PARAM_STR);
            

            $stmt->execute();

            if ($stmt->rowCount() > 0){

                $dados = $stmt->fetch(PDO::FETCH_ASSOC);

               
                if ($dados['role'] == 'admin') {
                        
                    return true;
                        
                }else{
                    http_response_code(403);
                    echo json_encode([
                    'Sucesso' => 0,
                    'Mensagem' => 'Você não possui as credênciais necessárias!']);
                    exit;
                }

            }else{
                http_response_code(403);
                echo json_encode(['sucesso' => 0, 'mensagem' => 'Confirme o login para executar está função 1']);
                exit;
            }


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'Sucesso' => 0,
                'Mensagem' => $e->getMessage()
            ]);
            exit;
        }
    

    }

    public function Getid($data)
    {
       
        $id_log = htmlspecialchars($data);

        $decoded = base64_decode($id_log);

         $part = explode(":",$decoded);

            if ($part[1] < date("Y-m-d h:i:sa")) {

                http_response_code(403);
                    echo json_encode([
                        'Sucesso'=> 0,
                        'Mensagem'=>'Por favor realize o login novamente!'
                    ]);
                exit;
            }
            
        $sql = "SELECT * FROM `user` WHERE id_log = :id_log";

        $conn = $this->connect(); 
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id_log', $id_log, PDO::PARAM_STR);

        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {

            return true;
        }else{
            http_response_code(403);
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Confirme o login para executar está função']);
            exit;
        }


    }
}



?>