<?php
  //////////////////////////////////////////////////////////////////////////////
  //                                  RAÍZ
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/", function() {
    $is_logged_in = AppAuthentication::is_logged_in();

    if ($is_logged_in) {
      \Singular\Controller::redirect("/notas");
      return;
    }

    \Singular\Controller::redirect("/login");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             INICIAR SESIÓN
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_public("/iniciar_sesion", function() {
    $is_logged_in = AppAuthentication::is_logged_in();

    if ($is_logged_in) {
      \Singular\Controller::redirect("/notas");
      return;
    }

    $usuario = \Singular\Controller::get_post_variable("usuario");
    $clave = \Singular\Controller::get_post_variable("clave");

    $exito = AppAuthentication::log_in($usuario, $clave);

    if ($exito) {
      \Singular\Controller::redirect("/notas");

      return;
    }

    \Singular\Controller::redirect("/login");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              CERRAR SESIÓN
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/cerrar_sesion", function() {
    AppAuthentication::log_out();
    \Singular\Controller::redirect("/login");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              LISTAR NOTAS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas", "notas", "ver", function() {
//    \Singular\Controller::debug(ModeloNotas::get_all());

    \Singular\View::render(array(
        "template" => "notas",
        "data" => ModeloNotas::get_all()
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             CREAR NUEVA NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas/nuevo", "notas", "editar", function() {
    \Singular\View::render(array(
        "template" => "notas_nuevo",
        "data" => ModeloNotas::get_all()
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                            GUARDAR NUEVA NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/notas/nuevo", "notas", "editar", function() {
    $nota = \Singular\Controller::get_post_variable("nota");

    ModeloNotas::create(array(
      "mensaje" => $nota
    ));

    \Singular\Controller::flash("Nota guardada correctamente");

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                BORRAR NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas/:nota/borrar", "notas", "editar", function($nota_id) {
    ModeloNotas::delete($nota_id);

    \Singular\Controller::flash("Nota borrada correctamente");

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                GUARDAR NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/notas/:nota/guardar", "notas", "editar", function($nota_id) {
    $nota = ModeloNotas::find($nota_id);

    if (!empty($nota)) {
      $mensaje = \Singular\Controller::get_post_variable("mensaje");

      ModeloNotas::update($nota_id, array(
        "mensaje" => $mensaje
      ));

      \Singular\Controller::flash("Nota actualizada correctamente");
    }

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              INICIO DE SESIÓN
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/login", function() {
    \Singular\View::render(array(
        "template" => "login"
    ));
  });
