<?php

class MyModel extends \Singular\Model {
  protected $table = "model";
  protected $order = array("creation ASC");
  protected $filter = NULL;

  protected $query_fields = array("*");

  protected $fields = array(
    "id" => array(
      "type" => "integer",
      "null" => FALSE,
      "auto_increment" => TRUE
    ),
    "creation" => array(
      "type" => "timestamp",
      "default" => "CURRENT_TIMESTAMP"
    )
  );

  protected $primary_key = "id";

  public function process($data) {
    return $data;
  }
}
