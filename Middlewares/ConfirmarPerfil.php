<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;;
use Firebase\JWT\JWT;
include_once '../Middlewares/AutentificadorJWT.php';

class ConfirmarPerfil
{
    private $perfiles;


    public function __construct(array $perfiles)
    {
        $this->perfiles = $perfiles;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            $token = $this->obtenerTokenDesdeHeader($request);

            // Verificar y decodificar el token JWT
            AutentificadorJWT::VerificarToken($token);
            $payload = AutentificadorJWT::ObtenerData($token);
            $perfilUsuario = $payload->perfil;
            $aux=0;
            var_dump($perfilUsuario);
        $permisosSuficientes = false;
        foreach ($this->perfiles as $perfilPermitido) {
            if ($perfilUsuario === $perfilPermitido) {
                
                $permisosSuficientes = true;
                break;
            }
        }
        if (!$permisosSuficientes) {
            throw new Exception("No tiene permisos suficientes para acceder a este recurso");
        }

            // Propagar al siguiente middleware o controlador
            $request = $request->withAttribute('tokenPayload', $payload); // Agregar el payload del token como atributo del request
            $response = $handler->handle($request);
            return $response;
        } catch (Exception $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }

   private function obtenerTokenDesdeHeader(Request $request): string
   {
       $header = $request->getHeaderLine('Authorization');
       if (empty($header)) {
           throw new Exception('Token no encontrado en el header Authorization');
       }
       if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
           throw new Exception('Formato de token inválido');
       }
       return $matches[1];
   }
}
