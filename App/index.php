<?php

error_reporting(-1);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require '../vendor/autoload.php';
require_once '../DB/DataBase.php';
require_once '../Controller/ControllerProducto.php';
require_once '../Controller/ControllerVenta.php';
require_once '../Middlewares/TipoProducto.php';
require_once '../Middlewares/Login.php';
require_once '../Middlewares/ConfirmarPerfil.php';
require_once '../Controller/ControllerUsuario.php';



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->addBodyParsingMiddleware();
$middlewarePerfilAdmin = new ConfirmarPerfil(['admin']);
//$middlewarePerfilEmpleado=new ConfirmarPerfil(['empleado']);
$middlewareAmbosPerfiles = new ConfirmarPerfil(['empleado','admin'],);






$app->group('/tienda', function ($app) use ($middlewarePerfilAdmin) {

    $app->post('/alta', function (Request $request, Response $response, array $args){

        $controller = new ControllerProducto();
        $result = $controller->altaProducto($request, $response);

        return $result;
    })->add(new TipoProducto())
      ->add($middlewarePerfilAdmin);

    $app->post('/consultar', function (Request $request, Response $response, $args) {

        $controller = new ControllerProducto();
        $result = $controller->Consulta($request, $response);

        return $result;
    });
});
$app->post('/registro', function (Request $request, Response $response, $args) {

    $controller = new ControllerUsuario();
    $result = $controller->AgregarUsuario($request, $response);

    return $result;
});

$app->post('/login', function (Request $request, Response $response, $args) {

    $log = new Login();
    $result = $log->login($request, $response);

    return $result;
});

$app->group('/ventas', function ($app) use($middlewarePerfilAdmin,$middlewareAmbosPerfiles){

    $app->group('/consultar', function ($app) use($middlewarePerfilAdmin,$middlewareAmbosPerfiles){

        $app->get('/productos/vendidos', function (Request $request, Response $response,) {
            $controller = new ControllerVenta();
            $result = $controller->productosVendidos($request, $response);
            return $result;
        })->add($middlewareAmbosPerfiles);

        $app->get('/ventas/porUsuario', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->VentasPorUsuario($request, $response);
            return $result;
        })->add($middlewareAmbosPerfiles);
        $app->get('/ventas/porProducto', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->VentasPorProducto($request, $response);
            return $result;
        })->add($middlewareAmbosPerfiles);
        $app->get('/ventas/ingresos', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->IngresosPorDia($request, $response);
            return $result;
        })->add($middlewarePerfilAdmin);
        $app->get('/productos/entreValores', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->ProductosEntreValores($request, $response);
            return $result;
        })->add($middlewareAmbosPerfiles);
        $app->get('/productos/masVendido', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->productoMasVendido($request, $response);
            return $result;
        })->add($middlewareAmbosPerfiles);

        $app->get('/ventas/descargar', function (Request $request, Response $response, $args) {
            $controller = new ControllerVenta();
            $result = $controller->DescargarVentas($request, $response);
            return $result;
        })->add($middlewarePerfilAdmin);
    });

    $app->post('/alta', function (Request $request, Response $response, array $args) {

        $controller = new ControllerVenta();
        $result = $controller->AltaVenta($request, $response);

        return $result;
    })->add($middlewareAmbosPerfiles);
    $app->put('/modificar', function (Request $request, Response $response, $args) {
        $controller = new ControllerVenta();
        $result = $controller->ModificarVenta($request, $response);
        return $result;
    })->add($middlewarePerfilAdmin);
});

$app->run();
