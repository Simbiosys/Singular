<?php
  namespace Singular;

  abstract class AuthorisationMethod {
    abstract public static function is_allowed($entity, $action);
  }
