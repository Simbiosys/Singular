<?php
  /**
  * Singular's Abstract Authorisation Method
  */
  namespace Singular;

  /**
  * Singular's Abstract Authorisation Method Class
  */
  abstract class AuthorisationMethod {
    /**
      * Returns True if the user has access to an action in an entity.
      *
      * @param string $entity Entity's name.
      * @param string $action Action's name.
      *
      * @return boolean
      */
    abstract public static function is_allowed($entity, $action);
  }
