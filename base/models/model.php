<?php

class MyModel extends \Singular\Model {
      protected static $table = "model";
      protected static $sql_query = "SELECT *
                                     FROM model";
      protected static $order = "id DESC";

      protected static $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "description" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE,
          "default" => "My model"
        )
      );

      protected static $primary_key = "id";
 
      public function process($data) {
        return $data;
      }
}
