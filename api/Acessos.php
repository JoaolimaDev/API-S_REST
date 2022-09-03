<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

require_once("model/Database.php");

class Acessos extends Database
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

$y='pmg';

if($x->name !== $y){

  http_response_code(403);
 echo json_encode([
         'Sucesso' => 0,
         'Mensagem' => 'Token invalido!',
    ]);
     exit;
}

if ($x->exp < date("Y-m-d")) {
    http_response_code(403);
    echo json_encode([
            'Sucesso' => 0,
            'Mensagem' => 'Token expirado!',
            ]);
            exit;
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

    $entrada_acesso = isset($_GET['entrada_acesso']) ? filter_input(INPUT_GET, 'entrada_acesso', FILTER_SANITIZE_SPECIAL_CHARS) : null;

try {
        // sanatização do filtro com is_numeric e query
    $sql = is_string($entrada_acesso) ? "SELECT * FROM `acesso`
    WHERE entrada_acesso='$entrada_acesso'" : "SELECT * FROM `acesso`";
    $conn = $this->connect(); 
    $stmt = $conn->prepare($sql);
    // EXECUÇÃO
    $stmt->execute();
      // contagem dos id para retorno com fun reserv rowCount
    if ($stmt->rowCount() > 0) :

        $dados = is_string($entrada_acesso) ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC); // váriavél para o assoc dos result
     
        // output da váriavel dados em array assoc json encode
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

function update()
{

  $data = json_decode(file_get_contents("php://input"));
  // válidação
  if (!isset($data->cod_acesso)) {
      echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
      exit;
  }

  try {
      // selecionando o id da DB para o rowcount
      $query = "SELECT * FROM `acesso` WHERE cod_acesso=:cod_acesso";
      $conn = $this->connect(); 
      $stmt = $conn->prepare($query);
      $stmt->bindValue(':cod_acesso', $data->cod_acesso, PDO::PARAM_INT);
      $stmt->execute();

      if ($stmt->rowCount() > 0) :

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $entrada = isset($data->entrada_acesso) ? $data->entrada_acesso : $row['entrada_acesso'];

      $saida = isset($data->saida_acesso) ? $data->saida_acesso : $row['saida_acesso'];

      $update_query = "UPDATE `acesso` SET entrada_acesso = :entrada_acesso, saida_acesso = :saida_acesso
      WHERE cod_acesso = :cod_acesso";

      $update_stmt = $conn->prepare($update_query);

      $update_stmt->bindValue(':entrada_acesso', htmlspecialchars(strip_tags($entrada)), PDO::PARAM_STR);
      $update_stmt->bindValue(':saida_acesso', htmlspecialchars(strip_tags($saida)), PDO::PARAM_STR);
      $update_stmt->bindValue(':cod_acesso', $data->cod_acesso, PDO::PARAM_INT);


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

function create()
{

  $data = json_decode(file_get_contents("php://input")); // leitura do json

  if ( !isset($data->entrada_acesso) && !isset($data->saida_acesso)
  && !isset($data->cod_local) && !isset($data->cod_pessoa) ) : // válidação

      echo json_encode([
          'sucesso' => 0,
          'mensagem' => 'Preencha todos os campos'
      ]);
      exit;

  elseif ( empty(trim($data->entrada_acesso)) || empty(trim($data->saida_acesso))
  || empty(trim($data->cod_local)) || empty(trim($data->cod_pessoa))) :  //sanatização com trim e empty trim () remove ambos
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
      $entrada_acesso = htmlspecialchars(trim($data->entrada_acesso));
      $saida_acesso = htmlspecialchars(trim($data->saida_acesso));
      $cod_local = htmlspecialchars(trim($data->cod_local));
      $cod_pessoa = htmlspecialchars(trim($data->cod_pessoa));



      // query
      $query = "INSERT INTO `acesso`(entrada_acesso, saida_acesso
      , cod_local, cod_pessoa) VALUES(:entrada_acesso, :saida_acesso, :cod_local, :cod_pessoa)";
      $conn = $this->connect(); 
      $stmt = $conn->prepare($query);
      //bind dos valores
      $stmt->bindValue(':entrada_acesso', $entrada_acesso, PDO::PARAM_STR);
      $stmt->bindValue(':saida_acesso', $saida_acesso, PDO::PARAM_STR);
      $stmt->bindValue(':cod_local', $cod_local, PDO::PARAM_INT);
      $stmt->bindValue(':cod_pessoa', $cod_pessoa, PDO::PARAM_INT);


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

function deletec()
{

  $data = json_decode(file_get_contents("php://input"));

  // válidação
  if (!isset($data->cod_acesso)) {
     echo json_encode(['sucesso' => 0, 'mensagem' => 'Adicione o ID.']);
     exit;
  }

  try {
    // selecionando o id da DB para o rowcount
     $query = "SELECT * FROM `acesso` WHERE cod_acesso=:cod_acesso";
     $conn = $this->connect(); 
     $stmt = $conn->prepare($query);
     $stmt->bindValue(':cod_acesso', $data->cod_acesso, PDO::PARAM_INT);
     //bind dos valores cod_local ao decode e a pdo do db param int
     $stmt->execute();
     // contagem dos id / declaração se for true retorna o delete
     if ($stmt->rowCount() > 0) :
       // DECLARAÇÃO DELETE COM COD_LOCAL JÁ INSTANCIADO
         $delete_post = "DELETE FROM `acesso` WHERE cod_acesso=:cod_acesso";
         $delete_post_stmt = $conn->prepare($delete_post);
         $delete_post_stmt->bindValue(':cod_acesso', $data->cod_acesso,PDO::PARAM_INT);
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

new Acessos;

?>
