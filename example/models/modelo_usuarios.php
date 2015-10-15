<?php

class ModeloUsuarios extends \Singular\Model {
      protected $table = "usuarios";
      protected $query = "SELECT id, cuenta, nombre
                                     FROM usuarios";
      protected $order = array("nombre ASC");

      protected $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "cuenta" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "clave" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "nombre" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "creacion" => array(
          "type" => "timestamp",
          "default" => "CURRENT_TIMESTAMP"
        )
      );

      protected $primary_key = "id";

      // No obligatorio
      public function process($data) {
        return $data;
      }

      public function valida_usuario($usuario, $clave) {
        $usuario = $this->get_all("cuenta = '$usuario' AND clave = '$clave'");

        return $usuario;
      }
  }
