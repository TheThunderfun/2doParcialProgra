<?php
require_once '../Entidades/Venta.php';
require_once '../Entidades/Producto.php';
require_once '../Entidades/Usuario.php';

use Carbon\Traits\ToStringFormat;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

Class ControllerVenta{
    public function AltaVenta(Request $request, Response $response){
        
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $email = $data['Email'];
        $marca = $data['Marca'];
        $tipo = $data['Tipo'];
        $modelo = $data['Modelo'];
        $color=$data['Color'];
        $stock = $data['Stock'];
        $imagen=$files['Imagen'];

        $product = Producto::ObtenerProducto($marca, $tipo, $modelo,$color);
        //print_r($producto->stock);
        if($product){
            if ($product->stock < $stock) {
                $responseData = ['mensaje' => 'Producto no encontrado'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }else{
    
                $venta = new Venta();
                $venta->email=$email;
                $venta->marca=$marca;
                $venta->tipo=$tipo;
                $venta->modelo=$modelo;
                $venta->stock=$stock;
                $venta->color=$color;
                $venta->fecha=date('Y-m-d');
                $venta->imagen=$imagen;
                
                $venta->GuardarVenta();
                //$producto->ActualizarStock($producto->stock - $stock);
                $responseData = ['mensaje' => 'Venta Realizada'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        }else{
            $responseData = ['Error' => 'No se encontro el producto'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

    }

    public function productosVendidos(Request $request, Response $response){
        $params = $request->getQueryParams();
        $fecha = $params['Fecha'] ?? date('d-m-Y', strtotime('yesterday'));

        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("SELECT SUM(Stock) as total_vendidos FROM ventas WHERE Fecha = ?");
        $stmt->execute([$fecha]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalVendidos = $result['total_vendidos'] ?? 0;

        $responseData = [
            'fecha' => $fecha,
            'total_vendidos' => $totalVendidos
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function VentasPorUsuario(Request $request, Response $response){
        $params = $request->getQueryParams();
        $email = $params['Email'] ?? null;

        if (!$email) {
            $responseData = ['mensaje' => 'El email del usuario es requerido'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("SELECT * FROM ventas WHERE Email = ?");
        $stmt->execute([$email]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$ventas) {
            $responseData = ['mensaje' => 'No se encontraron ventas para este usuario'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $responseData = [
            'email' => $email,
            'ventas' => $ventas
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function VentasPorProducto(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $tipo = $params['Tipo'] ?? null;

        if (!$tipo) {
            $responseData = ['mensaje' => 'El tipo de producto es requerido'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("SELECT * FROM ventas WHERE Tipo = ?");
        $stmt->execute([$tipo]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$ventas) {
            $responseData = ['mensaje' => 'No se encontraron ventas para este tipo de producto'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $responseData = [
            'tipo' => $tipo,
            'ventas' => $ventas
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ProductosEntreValores(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $valorMin = $params['valorMin'] ?? null;
        $valorMax = $params['valorMax'] ?? null;

        if (is_null($valorMin) || is_null($valorMax)) {
            $responseData = ['mensaje' => 'Ambos valores (valorMin y valorMax) son requeridos'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("SELECT * FROM producto WHERE Precio BETWEEN ? AND ?");
        $stmt->execute([$valorMin, $valorMax]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$productos) {
            $responseData = ['mensaje' => 'No se encontraron productos en el rango de precios especificado'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $responseData = [
            'valorMin' => $valorMin,
            'valorMax' => $valorMax,
            'productos' => $productos
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function IngresosPorDia(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $fecha = $params['Fecha'] ?? null;
    
        $db = DataBase::obtenerInstancia();
    
        if ($fecha) {
            $stmt = $db->prepararConsulta(
               "SELECT ventas.Fecha, SUM(producto.Precio * ventas.Stock) as ingresos 
                FROM ventas 
                INNER JOIN producto ON ventas.Modelo = producto.Modelo 
                WHERE ventas.Fecha = ? 
                GROUP BY ventas.Fecha"
            );
            $stmt->execute([$fecha]);
        } else {
            $stmt = $db->prepararConsulta(
                "SELECT ventas.Fecha, SUM(producto.Precio * ventas.Stock) as ingresos 
                FROM ventas 
                INNER JOIN producto ON ventas.Modelo = producto.Modelo 
                GROUP BY ventas.Fecha"
            );
            $stmt->execute();
        }
    
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (!$ingresos) {
            $responseData = ['mensaje' => 'No se encontraron ingresos para la fecha especificada'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    
        $response->getBody()->write(json_encode($ingresos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function productoMasVendido(Request $request, Response $response) {
        $db = DataBase::obtenerInstancia();

        $stmt = $db->prepararConsulta(
            "SELECT ventas.Modelo, ventas.Marca, ventas.Tipo, SUM(ventas.Stock) as total_vendido 
            FROM ventas 
            GROUP BY ventas.Modelo, ventas.Marca, ventas.Tipo 
            ORDER BY total_vendido DESC 
            LIMIT 1"
        );
        $stmt->execute();
        $productoMasVendido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$productoMasVendido) {
            $responseData = ['mensaje' => 'No se encontraron ventas'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($productoMasVendido));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
        public function ModificarVenta(Request $request, Response $response) {
            $data = $request->getParsedBody();
            
            $numeroPedido = $data['NumeroPedido'];
            $email = $data['Email'];
            $marca = $data['Marca'];
            $tipo = ucfirst($data['Tipo']);
            $modelo = $data['Modelo'];
            $stock = $data['Stock'];
            $color=$data['Color'];
    
            $db = DataBase::obtenerInstancia();
            $stmt = $db->prepararConsulta("SELECT * FROM ventas WHERE NumeroPedido = ?");
            $stmt->execute([$numeroPedido]);
            $venta = $stmt->fetchObject('Venta');
            
            //$producto = Producto::ObtenerProducto($marca, $tipo, $modelo);
           
            if ($venta ) {
  
                $stmt = $db->prepararConsulta(
                    "UPDATE ventas 
                    SET Email = ?, Marca = ?, Tipo = ?, Modelo = ?, Stock = ? ,Color=?
                    WHERE NumeroPedido = ?"
                );
                $stmt->execute([$email, $marca, $tipo, $modelo, $stock,$color, $numeroPedido]);
                //$producto->ActualizarStock($producto->stock +  );
                $responseData = ['mensaje' => 'Venta modificada exitosamente'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                
                $responseData = ['mensaje' => 'No existe ese numero de pedido'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        }


}