<?php
require_once '../DB/DataBase.php';
require_once '../Entidades/Usuario.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ControllerUsuario {
    public function AgregarUsuario(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $email = $data['Email'];
        $usuario = $data['Usuario'];
        $contrasenia = $data['Clave'];
        $perfil = $data['Perfil'];
        $foto = $files['Foto'];


        strtolower($perfil);
        if (!in_array($perfil, ['cliente', 'empleado', 'admin'])) {
            $response->getBody()->write(json_encode(['error' => 'Perfil no permitido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }


        $user = new Usuario();
        $user->email=$email;
        $user->usuario=$usuario;
        $user->clave=$contrasenia;
        $user->perfil=$perfil;
        $user->foto=$foto;
        $user->fecha_de_alta=date('Y-m-d');

        
        $user->GuardarUsuario();

        $responseData = ['mensaje' => 'Usuario agregado exitosamente'];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

}