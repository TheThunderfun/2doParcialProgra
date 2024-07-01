<?php
require_once  '../Entidades/Producto.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Factory\AppFactory;
Class ControllerProducto {
    public function altaProducto($request, $response) {

        $data = $request->getParsedBody(); // Datos del postman
        $files = $request->getUploadedFiles(); // Archivos(imagen)
        
        $producto = new Producto();
        $producto->id=Producto::GenerarId(5);
         $producto->marca=strtolower($data['Marca']);
         $producto->precio= $data['Precio'];
         $producto->tipo=$data['Tipo'] ;
         $producto->modelo=strtolower($data['Modelo']); 
         $producto->color=strtolower($data['Color']);
         $producto->stock=$data['Stock'];
         $producto->imagen=$files['Imagen'];

        $productoId = $producto->GuardarProducto();

        $responseData = [
            'id' => $productoId,
            'mensaje' => 'Producto guardado exitosamente.'
        ];       
            
            $response->getBody()->write(json_encode($responseData));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }



        public function Consulta($request, $response){
            $data = $request->getParsedBody(); 

            $producto = new Producto();
            $producto->marca=strtolower($data['Marca']);
            //$producto->precio= $data['Precio'];
            $producto->tipo=strtolower($data['Tipo']) ;
            //$producto->modelo=$data['Modelo']; 
            $producto->color=strtolower($data['Color']);
           // $producto->stock=$data['Stock'];

            $resultadoVerificacion = $producto->ConsultarExistencia();
            
            $response->getBody()->write(json_encode(['mensaje' => $resultadoVerificacion]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }

    }

