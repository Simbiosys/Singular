<?php
  class AppAuthentication extends \Singular\AuthenticationMethod  {
  	protected static $identifier = "mis_notas";
  	
    public static function log_in($user, $password) {
      $modelo = new ModeloUsuarios();
      $usuario = $modelo->valida_usuario($user, $password);

      if (!empty($usuario)) {
        self::set_user($user);

        return TRUE;
      }

      return FALSE;
    }

    public static function get_log_in() {
      $host = \Singular\Configuration::get_host();
      return  "$host/login";
    }
  }
