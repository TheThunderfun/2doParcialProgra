<?php


Class Producto{
    public $id;
    public $marca;
    public $precio;
    public $tipo;
    public $modelo;
    public $color;
    public $stock;
    public $imagen;

    public function GuardarProducto(){

        $db= DataBase::obtenerInstancia();

        $stmt=$db->PrepararConsulta("SELECT * FROM producto WHERE Marca = ? AND Tipo = ?");
        $stmt->execute([$this->marca,$this->tipo]);
        $producto=$stmt->fetch();

        if($producto){
            $stmt = $db->prepararConsulta("UPDATE producto SET Precio = ?, Stock = Stock + ? WHERE id = ?");
            $stmt->execute([$this->precio, $this->stock, $producto['id']]);
            $this->id = $producto['id'];
        }else{
            $stmt = $db->prepararConsulta("INSERT INTO producto (id, Marca, Precio, Tipo, Modelo, Color, Stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
           $stmt->execute([self::GenerarId(5), $this->marca, $this->precio, $this->tipo, $this->modelo, $this->color, $this->stock]);
        }

        $this->GuardarImagen();
        return $this->id;
    }
    public static function GenerarId($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }


    private function GuardarImagen() {
        if ($this->imagen) {
  
            $directorioImagenes = 'ImagenesDeProductos/2024/';

            
            if (!file_exists($directorioImagenes)) {
                mkdir($directorioImagenes, 0777, true); 
            }
            if (!is_writable($directorioImagenes)) {
                throw new Exception("El directorio de imágenes no es escribible.");
            }
            $nombreImagen = $this->marca .$this->tipo . '.jpg'; 
            $rutaImagen = $directorioImagenes . $nombreImagen;


            move_uploaded_file($this->imagen->getStream()->getMetadata('uri'), $rutaImagen);

            $db = DataBase::obtenerInstancia();
            $stmt = $db->prepararConsulta("UPDATE producto SET Imagen = ? WHERE id = ?");
            $stmt->execute([$rutaImagen, $this->id]);
        }
    }

    public function ConsultarExistencia(){
        
        $db = DataBase::obtenerInstancia();

        $stmt = $db->PrepararConsulta("SELECT * FROM producto WHERE Marca = ? AND Tipo = ? AND Color = ?");
        $stmt->execute([$this->marca, $this->tipo, $this->color]);
        $producto = $stmt->fetch();

        if ($producto) {
            return "existe";
        }

        $stmtMarca = $db->PrepararConsulta("SELECT * FROM producto WHERE Marca = ?");
        $stmtMarca->execute([$this->marca]);
        $marcaExiste = $stmtMarca->fetch();
        
        $stmtTipo = $db->PrepararConsulta("SELECT * FROM producto WHERE Tipo = ?");
        $stmtTipo->execute([$this->tipo]);
        $tipoExiste = $stmtTipo->fetch();
        
        $stmtColor = $db->PrepararConsulta("SELECT * FROM producto WHERE Color = ?");
        $stmtColor->execute([$this->color]);
        //$colorExiste = $stmtTipo->fetch();

        if (!$marcaExiste && !$tipoExiste) {
            return "No hay productos de la marca $this->marca ni del tipo $this->tipo.";
        } elseif (!$marcaExiste) {
            return "No hay productos de la marca $this->marca.";
        } elseif (!$tipoExiste) {
            return "No hay productos del tipo $this->tipo.";
        } else{
             return "No hay productos del color $this->color.";
        }
    }

    public static function ObtenerProducto($marca, $tipo, $modelo,$color) {
        
        $db = DataBase::obtenerInstancia();

        $stmt = $db->prepararConsulta("SELECT * FROM producto WHERE Marca = ? AND Tipo = ? AND Modelo = ? AND Color= ?");
        $stmt->execute([$marca, $tipo, $modelo,$color]);
        
        $producto = $stmt->fetchObject('Producto'); 
        //resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $producto;
        
    }
    public function actualizarStock($stock) {
        $db = DataBase::obtenerInstancia();
        $stmt = $db->prepararConsulta("UPDATE producto SET Stock = ? WHERE id = ?");
        $stmt->execute([$stock, $this->id]);
    }

}