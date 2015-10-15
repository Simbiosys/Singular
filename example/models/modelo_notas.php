<?php
////////////////////////////////////////////////////////////////////////////////
//                                Modelo Notas
////////////////////////////////////////////////////////////////////////////////
class ModeloNotas extends \Singular\Model {
  protected static $table = "notas";
  //protected static $query = "SELECT id, mensaje, creacion
  //                               FROM notas";
  protected static $order = array("creacion ASC");
  protected static $filter = NULL;

  protected static $fields = array(
    "id" => array(
      "type" => "integer", // Si no se especifica se define como string
      "null" => FALSE,
      "auto_increment" => TRUE
    ),
/*    "mensaje" => array(
      "type" => "string", // Si no se especifica se define como string
      "size" => 200,
      "null" => FALSE,
      "default" => "Mensaje"
    ),*/
    "creacion" => array(
      "type" => "timestamp",
      "default" => "CURRENT_TIMESTAMP"
    )
  );

  protected static $primary_key = "id";

  protected static $dependencies = array(
    "notas_traducciones" => array(
      "entity" => "ModeloNotasTraducciones",
      "key" => "notas_id",
      "filter" => NULL,
      "order" => "",
      "dependent" => TRUE // Si se borra una nota se borran sus traducciones
    )
  );

  protected function init() {
    $idioma = AppAuthentication::get_language();
    self::$dependencies["notas_traducciones"]["filter"] = "idioma = '$idioma'";
  }

  // No obligatorio
  public function process($data) {
    return $data;
  }
}

////////////////////////////////////////////////////////////////////////////////
//                      Modelo Notas (traducciones)
////////////////////////////////////////////////////////////////////////////////

class ModeloNotasTraducciones extends \Singular\Model {
  protected static $table = "notas_traducciones";

  protected static $fields = array(
    "id" => array(
      "type" => "integer",
      "null" => FALSE,
      "auto_increment" => TRUE
    ),
    "notas_id" => array(
      "type" => "integer",
      "null" => FALSE
    ),
    "mensaje" => array(
      "type" => "string",
      "size" => 200,
      "null" => FALSE,
      "default" => "Mensaje"
    ),
    "idioma" => array(
      "type" => "string",
      "size" => "3"
    )
  );

  protected static $primary_key = "id";
}
