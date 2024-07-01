<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;

Class Upper implements MiddlewareInterface{
   
    public function process(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        
        foreach ($data as $field) {
            if (isset($data[$field])) {
                $data[$field] = ucfirst($data[$field]);
            }
        }
        

        $files = $request->getUploadedFiles();
        if (isset($files['Imagen'])) {
            $data['Imagen'] = $files['Imagen'];
        }


        $request = $request->withParsedBody($data);
        

        $response = $handler->handle($request);
        
        return $response;
    }
}