<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Psr7Response;

class TipoProducto
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        $tipo = strtolower($data['Tipo'] ?? ''); 

        
        if (($tipo !== 'impresora' && $tipo !== 'cartucho')) {
            $response = new Psr7Response();
            $response->getBody()->write(json_encode(['error' => 'Tipo de producto no permitido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $tipo = ucfirst($tipo);

        $data['Tipo'] = $tipo;
        $request = $request->withParsedBody($data);
        return $handler->handle($request);
    }
}