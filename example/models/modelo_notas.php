<?php
////////////////////////////////////////////////////////////////////////////////
//                                Modelo Notas
////////////////////////////////////////////////////////////////////////////////
class ModeloNotas extends \Singular\Model {
  protected $table = "notas";
  protected $order = array("creacion ASC");
  protected $filter = NULL;

  protected $query_fields = array("*");

  protected $fields = array(
    "id" => array(
      "type" => "integer", // Si no se especifica se define como string
      "null" => FALSE,
      "auto_increment" => TRUE
    ),
    "creacion" => array(
      "type" => "timestamp",
      "default" => "CURRENT_TIMESTAMP"
    )
  );

  protected $primary_key = "id";

  protected $dependencies = array(
    "notas_traducciones" => array(
      "entity" => "ModeloNotasTraducciones",
      "key" => "notas_id",
      "filter" => NULL,
      "order" => "",
      "dependent" => TRUE // Si se borra una nota se borran sus traducciones
    )
  );
  
/*
  protected function init() {
    $idioma = AppAuthentication::get_language();
    $this->dependencies["notas_traducciones"]["filter"] = "idioma = '$idioma'";
  }
*/
  // No obligatorio
  public function process($data) {
    return $data;
  }
}

////////////////////////////////////////////////////////////////////////////////
//                      Modelo Notas (traducciones)
////////////////////////////////////////////////////////////////////////////////

class ModeloNotasTraducciones extends \Singular\Model {
  protected $table = "notas_traducciones";

  protected $fields = array(
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

  protected $primary_key = "id";
}
