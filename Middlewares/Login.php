<?php
require_once 'AutentificadorJWT.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
class Login{
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $mail = $data['Mail'] ;
        $clave = $data['Clave'] ;

        if (empty($mail) || empty($clave)) {
            $response->getBody()->write(json_encode(['error' => 'Mail y contraseña son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

       
        $usuarioValido = $this->verificarCredenciales($mail, $clave);

        if (!$usuarioValido) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales invalidas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

     
        $perfilUsuario = $this->obtenerPerfilUsuario($mail);

        
        $datosToken = [
            'usuario' => $mail,
            'perfil' => $perfilUsuario,
        ];

        try {
            
            $tokenJWT = AutentificadorJWT::CrearToken($datosToken);
            $response->getBody()->write(json_encode(['token' => $tokenJWT]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Error al generar el token']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

    }   
    private function verificarCredenciales($usuario, $contrasenia)
    {
        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("SELECT id, clave FROM usuarios WHERE mail = ?");
        $stmt->execute([$usuario]);
        $usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$usuarioDB) {
            return false; 
        }
    
       
        if (password_verify($contrasenia, $usuarioDB['clave'])) {
            return true; 
        } else {
            return false; 
        }
    }

    private function obtenerPerfilUsuario($usuario)
{

    $db = DataBase::obtenerInstancia();
    $stmt = $db->prepararConsulta("SELECT perfil FROM usuarios WHERE mail = ?");
    $stmt->execute([$usuario]);
    $perfil = $stmt->fetchColumn();

    if (!$perfil) {
        return 'cliente'; 
    }

    return $perfil;
}
    
}
