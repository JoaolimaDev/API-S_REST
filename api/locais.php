<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

require_once("model/Database.php");

class Locais extends Database
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

$valid = hash_hmac('sha256',"$header.$payload",md5('%89yzi5kl'),true);
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

$menuop = (isset($_GET["menuop"]))?$_GET["menuop"]:"read";

      switch ($menuop) {
           case 'read':
           if ($_SERVER['REQUEST_METHOD'] == 'GET'):
             return $this->read();
           elseif($_SERVER['REQUEST_METHOD'] !== 'GET'):
            http_response_code(403);
           echo json_encode([
                   'Sucesso' => 0,
                   'Mensagem' => 'Metodo invalido!',
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
                   'Mensagem' => 'Metodo invalido!',
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
                  'Mensagem' => 'Metodo invalido!',
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


    $nome_local = isset($_GET['nome_local']) ? filter_input(INPUT_GET, 'nome_local', FILTER_SANITIZE_SPECIAL_CHARS) : null;

try {
    $sql = is_string($nome_local) ? "SELECT * FROM `locais`
    WHERE nome_local='$nome_local'" : "SELECT * FROM `locais`";
    $conn = $this->connect(); 
    $stmt = $conn->prepare($sql);
    // EXECUÇÃO
    $stmt->execute();
      // contagem dos id para retorno com fun reserv rowCount
    if ($stmt->rowCount() > 0) :

        $dados = is_string($nome_local) ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC); // váriavél para o assoc dos result

        echo json_encode([
            'Sucesso' => 1,
            'dados' => $dados,
        ]);
        // erro caso id inválido ou db vazio
    else :
        echo json_encode([
            'Sucesso' => 0,
            'Mensagem' => 'Nenhum resultado encontrado!',
        ]);
        exit;
    endif;
    // tratamento de erro por conexão PDO com catch subsequente ao try
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'Sucesso' => 0,
        'Mensagem' => $e->getMessage()
    ]);
    exit;
}
}

public function update()
{
  
  $data = json_decode(file_get_contents("php://input"));
  // válidação
  if (!isset($data->cod_local)) {
      echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
      exit;
  }


  try {
      // selecionando o id da DB para o rowcount
      $query = "SELECT * FROM `locais` WHERE cod_local=:cod_local";
      $conn = $this->connect(); 
      $stmt = $conn->prepare($query);
      $stmt->bindValue(':cod_local', $data->cod_local, PDO::PARAM_INT);
      $stmt->execute();

      if ($stmt->rowCount() > 0) :

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $post_nome = isset($data->nome_local) ? $data->nome_local : $row['nome_local'];


      $update_query = "UPDATE `locais` SET nome_local = :nome_local
      WHERE cod_local = :cod_local";

      $update_stmt = $conn->prepare($update_query);

      $update_stmt->bindValue(':nome_local', htmlspecialchars(strip_tags($post_nome)), PDO::PARAM_STR);
      $update_stmt->bindValue(':cod_local', $data->cod_local, PDO::PARAM_INT);


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
  } catch (PDOException $e) {
      http_response_code(500);
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

  if (!isset($data->nome_local)) : // válidação

      echo json_encode([
          'sucesso' => 0,
          'mensagem' => 'Preencha todos os campos',
      ]);
      exit;

  elseif (empty(trim($data->nome_local))) :  //sanatização com trim e empty trim () remove ambos
    //os lados de uma sequência de caracteres em branco ou outros caracteres predefinidos.

      echo json_encode([
          'sucesso' => 0,
          'mensagem' => 'Campo vazio',
      ]);
      exit;

  endif;


  try {
        //sanatização
      $nome_local = htmlspecialchars(trim($data->nome_local));

      // query
      $query = "INSERT INTO `locais`(nome_local) VALUES(:nome_local)";
      $conn = $this->connect(); 
      $stmt = $conn->prepare($query);
      //bind dos valores
      $stmt->bindValue(':nome_local', $nome_local, PDO::PARAM_STR);

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


public function deletec()
{
 
  $data = json_decode(file_get_contents("php://input"));

  // válidação
  if (!isset($data->cod_local)) {
     echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
     exit;
  }

  try {
    // selecionando o id da DB para o rowcount
     $query = "SELECT * FROM `locais` WHERE cod_local=:cod_local";
     $conn = $this->connect(); 
     $stmt = $conn->prepare($query);
     $stmt->bindValue(':cod_local', $data->cod_local, PDO::PARAM_INT);
     //bind dos valores cod_local ao decode e a pdo do db param int
     $stmt->execute();
     // contagem dos id / declaração se for true retorna o delete
     if ($stmt->rowCount() > 0) :
       // DECLARAÇÃO DELETE COM COD_LOCAL JÁ INSTANCIADO
         $delete_post = "DELETE FROM `locais` WHERE cod_local=:cod_local";
         $delete_post_stmt = $conn->prepare($delete_post);
         $delete_post_stmt->bindValue(':cod_local', $data->cod_local,PDO::PARAM_INT);
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

new Locais;

?>
