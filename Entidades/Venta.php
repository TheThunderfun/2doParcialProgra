<?php

class Venta{
    public $id;
    public $email;
    public $marca;
    public $tipo;
    public $modelo;
    public $stock;
    public $color;
    public $fecha;
    public $imagen;



    private static function GenerarNumeroPedido(){
        return uniqid("Pedido_");
    }

    public function GuardarVenta(){
        $db = DataBase::obtenerInstancia();
        $nPedido=self::GenerarNumeroPedido();
        $stmt = $db->prepararConsulta("INSERT INTO ventas (email, Marca, Tipo, Modelo, Stock,Color, Fecha, NumeroPedido) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->execute([$this->email, $this->marca, $this->tipo, $this->modelo, $this->stock, $this->color,$this->fecha,$nPedido]);

        if($this->imagen){
            $this->GuardarimagenVenta($nPedido);
        }
    }

    private function GuardarImagenVenta($nPedido) {
        $directorioImagenes = 'ImagenesDeVenta/2024/';
        if (!file_exists($directorioImagenes)) {
            mkdir($directorioImagenes, 0777, true);
        }
        if (!is_writable($directorioImagenes)) {
            throw new Exception("El directorio de imágenes no es escribible.");
        }

        $emailUsuario = explode('@', $this->email)[0];
        $nombreImagen = $this->marca . $this->tipo . $this->modelo . $emailUsuario . $this->fecha . '.jpg';
        $rutaImagen = $directorioImagenes . $nombreImagen;

        $this->imagen->moveTo($rutaImagen);

        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("UPDATE ventas SET Imagen = ? WHERE NumeroPedido = ?");
        $stmt->execute([$rutaImagen, $nPedido]);
    
    }

}