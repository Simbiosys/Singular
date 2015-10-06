<?php
  //////////////////////////////////////////////////////////////////////////////
  //                            singular Framework
  //////////////////////////////////////////////////////////////////////////////

  session_start();

  // Load LightnCandy
  require_once('lightncandy/src/lightncandy.php');

  // Load minimum singular files
  require_once('singular/utils.php');
  require_once('singular/configuration.php');

  $root = \Singular\Configuration::get_root();

  // Load Slim framework files
  \Singular\Utils::load_files("$root/base/slim/Slim");

  // Load all the files from singular framework
  \Singular\Utils::load_files("$root/base/singular");

  // Run the router
  \Singular\Router::run();
