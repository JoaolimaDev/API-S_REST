<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
  
require_once("model/Database.php");

class Pessoas extends Database
{
  

  public function __construct()
  {
      
    if(isset($_SERVER["HTTP_AUTHORIZATION"])){

    $token = $_SERVER["HTTP_AUTHORIZATION"];


    $part = explode(".",$token);
    $header = trim($part[0], "Bearer ");
    $payload = $part[1];
    $signature = $part[2];

    function base64ErlEncode($data)
    {
      return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    $valid = hash_hmac('sha256',"$header.$payload",md5('98-54jdfi$'),true);
    $valid = base64ErlEncode($valid);

    $x = json_decode(
      base64_decode($part[1])
    );

    if ($x->exp < date("Y-m-d")) {
      http_response_code(403);
      echo json_encode([
              'Sucesso' => 0,
              'Mensagem' => 'Token expirado!',
              ]);
              exit;
    }

    $y='pmg';

    if($x->name !== $y){

    http_response_code(403);
    echo json_encode([
            'Sucesso' => 0,
            'Mensagem' => 'Token invalido!',
        ]);
        return;
    }
    if($signature == $valid){

    $menuop = isset($_GET["menuop"])? filter_input(INPUT_GET, 'menuop', FILTER_SANITIZE_SPECIAL_CHARS): null;

          switch ($menuop) {
              case 'read':
              if ($_SERVER['REQUEST_METHOD'] == 'GET') :
                return $this->read();
                elseif($_SERVER['REQUEST_METHOD'] !== 'GET'):
                  http_response_code(403);
                echo json_encode([
                        'Sucesso' => 0,
                        'Mensagem' => 'Metódo invalido!',
                      ]);
                      exit;
              endif;
              break;

              case 'post':
              if ($_SERVER['REQUEST_METHOD'] == 'POST'):
                return $this->create();
              elseif($_SERVER['REQUEST_METHOD'] !== 'POST'):
                http_response_code(403);
              echo json_encode([
                      'Sucesso' => 0,
                      'Mensagem' => 'Metódo invalido!',
                    ]);
                    exit;
            endif;
            break;

            case 'update':
            if ($_SERVER['REQUEST_METHOD'] == 'PUT'):
              return $this->update();
            elseif($_SERVER['REQUEST_METHOD'] !== 'PUT'):
              http_response_code(403);
              echo json_encode([
                      'Sucesso' => 0,
                      'Mensagem' => 'Metódo invalido!',
                  ]);
                  exit;
          endif;
            break;

            case 'delete':
            if ($_SERVER['REQUEST_METHOD'] == 'DELETE'):
              return $this->deletec();
            elseif($_SERVER['REQUEST_METHOD'] !== 'DELETE'):
              http_response_code(403);
            echo json_encode([
                    'Sucesso' => 0,
                    'Mensagem' => 'Metodo invalido!',
                  ]);
                  exit;
          endif;
          break;

              default:
              http_response_code(403);
              echo json_encode([
                      'Sucesso' => 0,
                      'Mensagem' => 'Insira um metódo!',
                  ]);
                  exit;
                  break;
        }

  }else{
    http_response_code(400);
    echo json_encode([
            'Sucesso' => 0,
        'Mensagem' => 'Token invalido!',
    ]);
    exit;
  }
  }else{
    http_response_code(400);
    echo json_encode([
            'Sucesso' => 0,
      'Mensagem' => 'Insira um Token!',
    ]);
    exit;
  }

  }

  public function read()
  {

    $cpf_pessoa = null; // id igual null para preenchemento se houver ou não esse campo
            //filtro de qualquer consulta por ID,vali como int e vali da var na url
      // Sanatização constante FILTER_VALIDATE_INT valida o valor como um número inteiro.
    if (isset($_GET['cpf_pessoa'])) {
        $cpf_pessoa = filter_var($_GET['cpf_pessoa'], FILTER_SANITIZE_SPECIAL_CHARS);
      }

  try {

      $sql = is_numeric($cpf_pessoa) ? "SELECT nome_pessoa
      , fone_pessoa, foto_pessoa, cpf_pessoa FROM `pessoas` WHERE cpf_pessoa='$cpf_pessoa'" : "SELECT nome_pessoa
      , fone_pessoa, cpf_pessoa FROM `pessoas`";

      $conn = $this->connect(); 
      $stmt = $conn->prepare($sql);
      // EXECUÇÃO
      $stmt->execute();
        // contagem dos id para retorno com fun reserv rowCount
      if ($stmt->rowCount() > 0) :

          $dados = null; // váriavél para o assoc dos result
          // output da váriavel dados em array assoc json encode
          if (is_numeric($cpf_pessoa)) {
              $dados = $stmt->fetch(PDO::FETCH_ASSOC);  // pdo fetch dados únicos por cpf + img
              echo json_encode([
                'Sucesso' => 1,
                'nome' => $dados["nome_pessoa"],
                'fone'=>$dados["fone_pessoa"],
                'cpf'=> $dados["cpf_pessoa"],
                'img'=> base64_encode($dados["foto_pessoa"])
            ]);
          } else {
              $dados = $stmt->fetchAll(PDO::FETCH_ASSOC); // pdo fetchAll
              echo json_encode([
                'Sucesso' => 1,
                'dados'=> $dados
            ]);
          }
          
        
          // erro caso id inválido ou db vazio
      else :
          echo json_encode([
              'Sucesso' => 0,
              'Mensagem' => 'Nenhum resultado encontrado!',
          ]);
      endif;
      // tratamento de erro por conexão PDO com catch subsequente ao try


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

    if ($_FILES['image']['tmp_name']) {

        $imageData = addslashes(file_get_contents($_FILES['image']['tmp_name']));
  }

    if ( !isset($data->cpf_pessoa) && !isset($data->nome_pessoa)
    && !isset($data->fone_pessoa)) : // válidação

        echo json_encode([
            'sucesso' => 0,
            'mensagem' => 'Preencha todos os campos'
        ]);
        exit;

    elseif ( empty(trim($data->cpf_pessoa)) || empty(trim($data->nome_pessoa))
    || empty(trim($data->fone_pessoa))) :  //sanatização com trim e empty trim () remove ambos
      //os lados de uma sequência de caracteres em branco ou outros caracteres predefinidos.

        echo json_encode([
            'sucesso' => 0,
            'mensagem' => 'Campo vazio',
        ]);
        exit;

    endif;

      //declaração de ação com try após os IF
    try {
          //sanatização
        $cpf_pessoa = htmlspecialchars(trim($data->cpf_pessoa));
        $nome_pessoa = htmlspecialchars(trim($data->nome_pessoa));
        $fone_pessoa = htmlspecialchars(trim($data->fone_pessoa));

        // query
        $query = "INSERT INTO `pessoas`(cpf_pessoa, nome_pessoa
        , fone_pessoa, foto_pessoa) VALUES(:cpf_pessoa, :nome_pessoa, :fone_pessoa, '{$imageData}')";

       
        $conn = $this->connect(); 
        $stmt = $conn->prepare($query);
        //bind dos valores
        $stmt->bindValue(':cpf_pessoa', $cpf_pessoa, PDO::PARAM_STR);
        $stmt->bindValue(':nome_pessoa', $nome_pessoa, PDO::PARAM_STR);
        $stmt->bindValue(':fone_pessoa', $fone_pessoa, PDO::PARAM_INT);


        //executando o insert
        if ($stmt->execute()) {

            http_response_code(201);
            echo json_encode([
                'sucesso' => 1,
                'mensagem' => 'Dado inserido com sucesso.'
            ]);
            exit;
        }

        //mensagem de erro
        echo json_encode([
            'sucesso' => 0,
            'mensagem' => 'Dado não inserido.'
        ]);
        exit;
        //erro pdoe de execusão/conexão ao DB com catch após o try
    } catch (PDOException $e) {
        http_response_code(500);   //Erro do Servidor Interno
        echo json_encode([
            'sucesso' => 0,
            'mensagem' => $e->getMessage()
        ]);
        exit;
    }
  }

  public function update()
  {

    $data = json_decode(file_get_contents("php://input"));
    // válidação
    if (!isset($data->cod_pessoa)) {
        echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
        exit;
        }

    if ($_FILES['image']['tmp_name']) {

        $imageData = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        }


    try {
        // selecionando o id da DB para o rowcount
        $query = "SELECT cod_pessoa FROM `pessoas` WHERE cod_pessoa=:cod_pessoa";
        $conn = $this->connect(); 
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':cod_pessoa', $data->cod_pessoa, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) :

        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        $cpf_pessoa = isset($data->cpf_pessoa) ? $data->cpf_pessoa : $row['cpf_pessoa'];

        $nome_pessoa = isset($data->nome_pessoa) ? $data->nome_pessoa : $row['nome_pessoa'];

        $fone_pessoa = isset($data->fone_pessoa) ? $data->fone_pessoa : $row['fone_pessoa'];

        $update_query = "UPDATE `pessoas` SET cpf_pessoa = :cpf_pessoa, nome_pessoa = :nome_pessoa, fone_pessoa = :fone_pessoa,
        foto_pessoa = '$imageData'
        WHERE cod_pessoa = :cod_pessoa";

        $conn = $this->connect();
        $update_stmt  = $conn->prepare($update_query);

        $update_stmt->bindValue(':cpf_pessoa', htmlspecialchars(strip_tags($cpf_pessoa)), PDO::PARAM_STR);
        $update_stmt->bindValue(':nome_pessoa', htmlspecialchars(strip_tags($nome_pessoa)), PDO::PARAM_STR);
        $update_stmt->bindValue(':fone_pessoa', htmlspecialchars(strip_tags($fone_pessoa)), PDO::PARAM_STR);
        $update_stmt->bindValue(':cod_pessoa', $data->cod_pessoa, PDO::PARAM_INT);


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
          http_response_code(400);
            echo json_encode(['sucesso' => 0, 'mensagem' => 'Id invalido.']);
            exit;
        endif;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => 0,
            'mensagem' => $e->getMessage()
        ]);
        exit;
    }
  }

  public function deletec()
  {

    $data = json_decode(file_get_contents("php://input"));

    // válidação
    if (!isset($data->cod_pessoa)) {
      echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
      exit;
    }

    try {
      // selecionando o id da DB para o rowcount
      $query = "SELECT cod_pessoa FROM `pessoas` WHERE cod_pessoa=:cod_pessoa";
      $conn = $this->connect(); 
      $stmt = $conn->prepare($query);
      $stmt->bindValue(':cod_pessoa', $data->cod_pessoa, PDO::PARAM_INT);
     
      $stmt->execute();
      // contagem dos id / declaração se for true retorna o delete
      if ($stmt->rowCount() > 0) :
        
          $delete_post = "DELETE FROM `pessoas` WHERE cod_pessoa=:cod_pessoa";
          $delete_post_stmt = $conn->prepare($delete_post);
          $delete_post_stmt->bindValue(':cod_pessoa', $data->cod_pessoa,PDO::PARAM_INT);
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

    } catch (PDOException $e) {
      http_response_code(500);
      echo json_encode([
          'success' => 0,
          'message' => $e->getMessage()
      ]);
      exit;
    }
  }

 
}

new Pessoas;

?>
