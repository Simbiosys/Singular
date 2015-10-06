<?php

class ModeloUsuarios extends \Singular\Model {
      protected static $table = "usuarios";
      protected static $sql_query = "SELECT id, cuenta, nombre
                                     FROM usuarios";
      protected static $order = "nombre ASC";

      protected static $fields = array(
        "id" => array(
          "type" => "integer", // Si no se especifica se define como string
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "cuenta" => array(
          "type" => "string", // Si no se especifica se define como string
          "size" => 200,
          "null" => FALSE
        ),
        "clave" => array(
          "type" => "string", // Si no se especifica se define como string
          "size" => 200,
          "null" => FALSE
        ),
        "nombre" => array(
          "type" => "string", // Si no se especifica se define como string
          "size" => 200,
          "null" => FALSE
        ),
        "creacion" => array(
          "type" => "timestamp",
          "default" => "CURRENT_TIMESTAMP"
        )
      );

      protected static $primary_key = "id";

      // No obligatorio
      public function process($data) {
        return $data;
      }

      public function valida_usuario($usuario, $clave) {
        $usuario = $this->get_all("cuenta = '$usuario' AND clave = '$clave'");

        return $usuario;
      }
  }
