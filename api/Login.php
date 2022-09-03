<?php 
;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");


require_once("api/model/Database.php");
require_once("api/controller/Ctrl.php");
use Controller;
use model\Database;



session_start();
class Login extends Database
{
   

    public function __construct($token, $session, $menu, $id)
    {

        if (empty($token)) {
            http_response_code(403);
            echo json_encode([
            'Sucesso'=>0,
            'Mensagem'=>'Insira um token valido 01'
            ]);
            exit;
        }

        $valida = controller\Ctrl::sethAuth($token);

    
            if($valida == true){
                    

                switch ($menu) {
                    case 'read':
                    if ($_SERVER['REQUEST_METHOD'] == 'GET'):
                            return $this->read($session, $id);
                            elseif($_SERVER['REQUEST_METHOD'] !== 'GET'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;
                    break;
                

                    case 'login':
                        if ($_SERVER['REQUEST_METHOD'] == 'POST'):
                            return $this->login();
                            elseif($_SERVER['REQUEST_METHOD'] !== 'POST'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;

                    case 'create':
                        if ($_SERVER['REQUEST_METHOD'] == 'POST'):
                            return $this->create();
                            elseif($_SERVER['REQUEST_METHOD'] !== 'POST'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;

                    case 'update':
                        if ($_SERVER['REQUEST_METHOD'] == 'PUT'):
                            return $this->update($id);
                            elseif($_SERVER['REQUEST_METHOD'] !== 'PUT'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;

                    case 'logout':
                        if ($_SERVER['REQUEST_METHOD'] == 'GET'):
                            return $this->logout();
                            elseif($_SERVER['REQUEST_METHOD'] !== 'GET'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;


                    case 'delete':
                        if ($_SERVER['REQUEST_METHOD'] == 'DELETE'):
                            return $this->deleteUser($id);
                            elseif($_SERVER['REQUEST_METHOD'] !== 'DELETE'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;

                    case 'logout':
                        if ($_SERVER['REQUEST_METHOD'] == 'GET'):
                            return $this->logout();
                            elseif($_SERVER['REQUEST_METHOD'] !== 'GET'):
                            http_response_code(403);
                            echo json_encode([
                                'Sucesso'=>0,
                                'Mensagem'=>'Metódo inválido'
                            ]);
                            exit;
                        endif;  
                    break;
                    
                    default:
                        
                        http_response_code(403);
                        echo json_encode([
                            'Sucesso'=>0,
                            'Mensagem'=>'Rest_Login'
                        ]);
                        exit;

                    
                    break;


                }
            
        
    
        
        }
        
        
    }


    public function read($session, $idx)
    {
            
        try { 

            $id = empty($idx) ? null : $idx;
            
            if (controller\Ctrl::setID($session) == true) {
                    

                    if(controller\Ctrl::getRole($session) == true){

                        $sql01 = is_numeric($id) ? "SELECT email, nome, id FROM `user`
                        WHERE id='$id'" : "SELECT email, nome, id FROM `user`";
                        $conn = $this->connect(); 
                        $stmt_query = $conn->prepare($sql01);

                        $stmt_query->execute();

                        if ($stmt_query->rowCount() > 0) {

                            $dados_query = is_numeric($id) ? $stmt_query->fetch(PDO::FETCH_ASSOC) : $stmt_query->fetchAll(PDO::FETCH_ASSOC);
                            echo json_encode([
                                'dados' => $dados_query
                            ]);
                        }else{
                            http_response_code(403);
                            echo json_encode([
                            'Sucesso' => 0,
                            'Mensagem' => 'Nenhum resultado encontrado!'
                            ]);
                            exit;
                        }
                    }
                
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

    public function deleteUser($id){

        if (empty($id)) {
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
            exit;
        }

        try {  
          
            if (controller\Ctrl::setID($_SERVER["ID_LOG"]) == true) {
                    

                if(controller\Ctrl::getRole($_SERVER["ID_LOG"]) == true){


                    $query = "SELECT * FROM `user` WHERE id=:id";
                    $conn = $this->connect();
                    $stmt = $conn->prepare($query);
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                    
                    $stmt->execute();
                    // contagem dos id / declaração se for true retorna o delete
                    if ($stmt->rowCount() > 0) :
                      
                        $delete_post = "DELETE FROM `user` WHERE id=:id";
                        $delete_post_stmt = $conn->prepare($delete_post);
                        $delete_post_stmt->bindValue(':id', $id,PDO::PARAM_INT);
                        //bind dos valores
                        if ($delete_post_stmt->execute()) {
             
                            echo json_encode([
                                'sucesso' => 1,
                                'mensagem' => 'Deletado com sucesso.'
                            ]);
                            exit;
                        }
             
                        echo json_encode([
                            'sucesso' => 0,
                            'mensagem' => 'Não foi possível deletar.'
                        ]);
                        exit;
             
                    else :
                        echo json_encode(['sucesso' => 0, 'mensagem' => 'ID inválido.']);
                        exit;
                    endif;
                }
            }


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
           'success' => 0,
           'message' => $e->getMessage()
            ]);
            exit;
        }

    }

    private function Backlog($back_log, $email)
    {
        
        $query = "INSERT INTO `user_log`(user_log, email) VALUES(:user_log, :email)";

        $conn = $this->connect();
        $stmt = $conn->prepare($query);
        //bind dos valores
        $stmt->bindValue(':user_log', $back_log, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();
            
    }
   

    private function login()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty(trim($data->email))):
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o email.']);
            exit;
            elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)):
                echo json_encode(['sucesso' => 0, 'mensagem' => 'Email inválido.']);
                exit;
        endif;

        if (empty(trim($data->senha))):
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione a senha.']);
            exit;
        endif;
    
    
    try {    
        $email = htmlspecialchars(strip_tags($data->email));
        $senha = htmlspecialchars(strip_tags($data->senha));

        $sql = "SELECT * FROM `user` WHERE email = :email";

        
        $conn = $this->connect(); 
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        

        $stmt->execute();

        if ($stmt->rowCount() > 0){

            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if(password_verify($senha, $dados['senha'])){

                $sql = "UPDATE `user` SET id_log = :id_log
                WHERE email = :email";
        
                
                $stmt = $conn->prepare($sql);
        
                $stmt->bindValue(':email', $dados['email'], PDO::PARAM_STR);
                session_regenerate_id();
                $id = password_hash(session_id(), PASSWORD_DEFAULT);
                $log = base64_encode($id.":". date("Y-m-d h:i:sa", strtotime("+1 day")));
                $stmt->bindValue(':id_log', $log , PDO::PARAM_STR);

            
                if ($stmt->execute()) {

                    $this->Backlog(date("Y-m-d h:i:sa"), $email);
                    
                    http_response_code(200);  //HTTP 200 OK
                    echo json_encode([
                    'Sucesso' => 1,
                    'Mensagem' => 'Usuário autenticado '. $dados['email'],
                    'Session_id' => $log]);
                    exit; 
                }

                
            }else{
                http_response_code(403);
                    echo json_encode([
                    'Sucesso' => 0,
                'Mensagem' => 'Email ou Senha inválidos'
                ]);
                exit;
                
            }

        }else{
            http_response_code(403);
            echo json_encode([
                'Sucesso' => 0,
               'Mensagem' => 'Email ou Senha inválidos'
            ]);
            exit;

        }

    } catch (PDOException $e) {
        http_response_code(500);   //Erro do Servidor Interno
        echo json_encode([
            'sucesso' => 0,
            'mensagem' => $e->getMessage()
        ]);
        exit;
    }
    }

    public function create()
    {

    $data = json_decode(file_get_contents("php://input")); // leitura do json


    if (empty($data->email)):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o email.']);
        exit;
        elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Email inválido.']);
        exit;
    endif;

    if (empty(trim($data->senha))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione a senha.']);
        exit;
    endif;

    if (empty(trim($data->nome))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o nome do usuário.']);
        exit;
    endif;

    if (empty(trim($data->role))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione a hierarquia do usuário.']);
        exit;
    endif;

    if (!isset($data->rSenha)) : // validação da senha

        echo json_encode([
            'sucesso' => 0,
            'mensagem' => 'Preencha todos os campos',
        ]);
        exit;

    elseif (empty(trim($data->rSenha))) : 
        //os lados de uma sequência de caracteres em branco ou outros caracteres predefinidos.
    
          echo json_encode([
              'sucesso' => 0,
              'mensagem' => 'Campo vazio',
          ]);
          exit;
    endif;

    
    try {

    
        if (controller\Ctrl::setID($_SERVER["ID_LOG"]) == true) {
                    

            if(controller\Ctrl::getRole($_SERVER["ID_LOG"]) == true){

            $email = htmlspecialchars(strip_tags($data->email));
            $senha = htmlspecialchars(strip_tags($data->senha));
            $nome = htmlspecialchars(strip_tags($data->nome));
            $role = htmlspecialchars(strip_tags($data->role));
            $rSenha = htmlspecialchars(strip_tags($data->rSenha));

            $sql = "SELECT * FROM `user` WHERE email= :email";


            $conn = $this->connect();
            $stmt_query = $conn->prepare($sql);

            $stmt_query->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt_query->execute();

            if ($stmt_query->rowCount() > 0) {

                http_response_code(403);
                echo json_encode([
                    'sucesso' => 1,
                    'mensagem' => 'Email já cadastrado, Por favor utilize outro.'
                ]);
                exit;
                
            }

            if($senha == $rSenha){
                // query
                $id_log = session_id();

                $query = "INSERT INTO `user`(email, senha, nome, role, id_log) VALUES(:email, :senha, :nome, :role, :id_log)";
                
                
                $stmt = $conn->prepare($query);
                //bind dos valores
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->bindValue(':senha', password_hash($senha, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindValue(':role', $role, PDO::PARAM_STR);
                $stmt->bindValue(':id_log', $id_log, PDO::PARAM_STR);



        
                //executando o insert
                if ($stmt->execute()) {
        
                    http_response_code(200);
                    echo json_encode([
                        'sucesso' => 1,
                        'mensagem' => 'Dado inserido com sucesso.'
                    ]);
                    exit;
                }
        
            }else{
                http_response_code(400);
                //mensagem de erro
                echo json_encode([
                    'sucesso' => 0,
                    'mensagem' => 'Senhas não Correspondem.'
                ]);
                exit;
            }

        }
   }

    } catch (PDOException $e) {
        http_response_code(500);   //Erro do Servidor Interno
      echo json_encode([
          'sucesso' => 0,
          'mensagem' => $e->getMessage()
      ]);
      exit;
    }
    

    }

    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        // válidação
         if (empty($id)) {
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
            exit;
        }

    if (empty($data->newEmail)):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o novo email.']);
        exit;
        elseif (!filter_var($data->newEmail, FILTER_VALIDATE_EMAIL)):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Email inválido.']);
        exit;
    endif;

    if (empty(trim($data->newNome))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o nome do usuário.']);
        exit;
    endif;

    if (empty(trim($data->newSenha))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione a nova senha.']);
        exit;
    endif;

    if (empty(trim($data->newRole))):
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione a hierarquia do usuário.']);
        exit;
    endif;

    if (!isset($data->rSenha)) : // validação da senha

        echo json_encode([
            'sucesso' => 0,
            'mensagem' => 'Preencha todos os campos',
        ]);
        exit;

    elseif (empty(trim($data->rSenha))) : 
        
    
          echo json_encode([
              'sucesso' => 0,
              'mensagem' => 'Campo vazio',
          ]);
          exit;
    endif;

    if ($data->newSenha !== $data->rSenha) {

        echo json_encode([
            'sucesso' => 1,
            'mensagem' => 'Senhas não correspondem'
        ]);
        exit;

    }
         

    try {

    
        if (controller\Ctrl::setID($_SERVER["ID_LOG"]) == true) {
                    

            if(controller\Ctrl::getRole($_SERVER["ID_LOG"]) == true){
            
                
                // selecionando o id da DB para o rowcount
                $query = "SELECT * FROM `user` WHERE id=:id";
                $conn = $this->connect();
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                        
                if ($stmt->rowCount() > 0) :

                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $post_nome = isset($data->newNome) ? $data->newNome : $row['nome'];
                        $post_senha = isset($data->newSenha) ? password_hash($data->newSenha, PASSWORD_DEFAULT) : $row['senha'];
                        $post_role = isset($data->newRole) ? $data->newRole : $row['role'];
                        $post_email = isset($data->newEmail) ? $data->newEmail : $row['email'];
                        



                        $update_query = "UPDATE `user` SET email = :email, senha = :senha, role = :role, nome = :nome
                        WHERE id = :id";

                        $update_stmt = $conn->prepare($update_query);

                        $update_stmt->bindValue(':email', htmlspecialchars(strip_tags($post_email)), PDO::PARAM_STR);
                        $update_stmt->bindValue(':senha', htmlspecialchars(strip_tags($post_senha)), PDO::PARAM_STR);
                        $update_stmt->bindValue(':nome', htmlspecialchars(strip_tags($post_nome)), PDO::PARAM_STR);
                        $update_stmt->bindValue(':role', htmlspecialchars(strip_tags($post_role)), PDO::PARAM_STR);

                        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);


                            if ($update_stmt->execute()) {

                                echo json_encode([
                                    'sucesso' => 1,
                                    'mensagem' => 'Update realizado'
                                ]);
                                exit;
                            }

                            echo json_encode([
                                'sucesso' => 0,
                                'mensagem' => 'Update não realizado.'
                            ]);
                            exit;
                else :
                    echo json_encode(['sucesso' => 0, 'mensagem' => 'Id inválido.']);
                    exit;
                endif;
            }
        }
    
    } catch (PDOException $e) {
            http_response_code(500);   //Erro do Servidor Interno
            echo json_encode([
          'sucesso' => 0,
          'mensagem' => $e->getMessage()
      ]);
      exit;
        }
    }
    

    public function logout()
    {
      
        $id_log = $_SERVER["ID_LOG"];

            $query = "SELECT * FROM `user` WHERE id_log=:id_log";
            $conn = $this->connect();
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id_log', $id_log, PDO::PARAM_STR);
            $stmt->execute();

            if($stmt->rowCount() > 0){

                $dados_query = $stmt->fetch(PDO::FETCH_ASSOC);

                $sql = "UPDATE `user` SET id_log = :id_log
                WHERE email = :email";
        
                $stmt_update = $conn->prepare($sql);
        
                $stmt_update->bindValue(':email', $dados_query['email'], PDO::PARAM_STR);
                session_regenerate_id();
                $id = password_hash(session_id(), PASSWORD_DEFAULT);
                $log = base64_encode($id.":".date("Y-m-d h:i:sa")."Logout");
                $stmt_update->bindValue(':id_log', $log , PDO::PARAM_STR);

                if($stmt_update->execute()):

                    $this->Backlog(date("Y-m-d h:i:sa")." Logout", $dados_query['email']);

                    session_unset();
                    session_destroy();

                    http_response_code(200);
                    echo json_encode([
                        'sucesso' => 1,
                        'mensagem' => 'Sessão encerrada!',
                        'Session_status'=> session_status()
                    ]);
                    exit;

                endif;

            }else{

                http_response_code(400);
                echo json_encode([
                    'sucesso' => 0,
                    'mensagem' => 'Não foi possível executar a operação.'
                ]);
                exit;

            }

        

    }




}


?>