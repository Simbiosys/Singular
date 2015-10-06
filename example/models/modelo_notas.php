<?php

class ModeloNotas extends \Singular\Model {
      protected static $table = "notas";
      protected static $sql_query = "SELECT id, mensaje, creacion
                                     FROM notas";
      protected static $order = "mensaje DESC";

      protected static $fields = array(
        "id" => array(
          "type" => "integer", // Si no se especifica se define como string
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "mensaje" => array(
          "type" => "string", // Si no se especifica se define como string
          "size" => 200,
          "null" => FALSE,
          "default" => "Mensaje"
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
}
