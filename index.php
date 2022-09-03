<?php 
header("Content-Type: application/json; charset=UTF-8");

require_once("vendor/autoload.php");
require_once("api/controller/Ctrl.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Controller\Ctrl;

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(false, true ,true);

$app->get('/',function (Request $request, Response $response) {
    $response->getBody()->write(
    json_encode([
        'Sucesso'=>0,
        'Mensagem'=>'Sistema_Rest_PMG'
    ]));
    return $response;
    
});

$app->get('/user',function (Request $request, Response $response) 
{

    Ctrl::setLog($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop = 'read', $id = null
    , $data = null);

    $response->getBody()->write(json_encode(['Sucesso' => 1]));
    return $response;
});


$app->get('/user/{id}',function (Request $request, Response $response) 
{

    $id =  is_numeric($request->getAttribute('id')) ? htmlspecialchars($request->getAttribute('id')) : null;

    Ctrl::setLog($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop = 'read', $id
    , $data = null);

    $response->getBody()->write(json_encode(['Sucesso' => 1]));
    return $response;
   
});

$app->addBodyParsingMiddleware();

$app->post('/user/{menuop}', function (Request $request) {

    $menuop = is_string($request->getAttribute('menuop')) ? htmlspecialchars($request->getAttribute('menuop')) : null;
    $data = $request->getParsedBody();

	new Login($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop, $id = null, $data);
    
});

$app->put('/user/update/{id}',function (Request $request) 
{
    $data = $request->getParsedBody();

    $id =  is_numeric($request->getAttribute('id')) ? htmlspecialchars($request->getAttribute('id')) : null;

	new Login($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop = 'update', $id, $data);
    
});

$app->delete('/user/delete/{id}', function (Request $request) {

    $menuop = 'delete';

    $id =  is_numeric($request->getAttribute('id')) ? htmlspecialchars($request->getAttribute('id')) : null;

	new Login($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop, $id);
    
});   

$app->get('/logout', function (Request $request, Response $response) {

    new Login($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['ID_LOG'], $menuop = 'logout', $id = null);

});

$app->run();



?>