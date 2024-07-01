<?php 

Class Usuario{
    public $id;
    public $email;
    public $usuario;
    public $clave;
    public $perfil;
    public $foto;
    public $fecha_de_alta;
    public $fecha_de_baja;


    public function GuardarUsuario()
    {
        $hashClave = password_hash($this->clave, PASSWORD_DEFAULT);
            $db = DataBase::obtenerInstancia();
            $stmt = $db->prepararConsulta(
                "INSERT INTO usuarios (mail, usuario, clave, perfil, fecha_de_alta) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$this->email, $this->usuario, $hashClave, $this->perfil, $this->fecha_de_alta]);

             if ($this->foto) {
                 $this->GuardarImagen($this->email);
            }
            
        }
    private function GuardarImagen($mail)
    {
        if ($this->foto) {
            $directorioImagenes = 'ImagenesDeUsuarios/2024/';
    
            
            if (!file_exists($directorioImagenes)) {
                mkdir($directorioImagenes, 0777, true);
            }
    
            
            $nombreImagen = $this->usuario . '_' . $this->perfil . '_' . date('Ymd_His') . '.jpg';
            $rutaImagen = $directorioImagenes . $nombreImagen;
    
           
            move_uploaded_file($this->foto->getStream()->getMetadata('uri'), $rutaImagen);
    
            
            $rutaCompletaImagen = $rutaImagen; 
    
            $db = DataBase::obtenerInstancia();
            $stmt = $db->prepararConsulta("UPDATE usuarios SET foto = ? WHERE mail = ?");
            $stmt->execute([$rutaCompletaImagen, $mail]);
        }
    }

}