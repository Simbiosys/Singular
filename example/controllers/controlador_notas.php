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

    \Singular\Controller::flash("Usuario y/o contraseña incorrectos");

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
  //                              IDIOMA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/language/:language", function($language) {
    AppAuthentication::set_language($language);
    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              LISTAR NOTAS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas", "notas", "ver", function() {
    $modelo_notas = new ModeloNotas();

  //  \Singular\Controller::debug($modelo_notas->get_all());

    \Singular\View::render(array(
        "template" => "notas",
        "data" => $modelo_notas->get_all()
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             CREAR NUEVA NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas/nuevo", "notas", "editar", function() {
    \Singular\View::render(array(
        "template" => "notas_nuevo"
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                            GUARDAR NUEVA NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/notas/nuevo", "notas", "editar", function() {
    $nota_en = \Singular\Controller::get_post_variable("mensaje_en");
    $nota_es = \Singular\Controller::get_post_variable("mensaje_es");

    $modelo_notas = new ModeloNotas();

    $modelo_notas->create(array(
      "notas" => array(

      ),
      "notas_traducciones" => array(
        array(
          "mensaje" => $nota_es,
          "idioma" => "es"
        ),
        array(
          "mensaje" => $nota_en,
          "idioma" => "en"
        )
      )
    ));

    \Singular\Controller::flash("Nota guardada correctamente");

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                BORRAR NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas/:nota/borrar", "notas", "editar", function($nota_id) {
    $modelo_notas = new ModeloNotas();
    $modelo_notas->delete($nota_id);

    \Singular\Controller::flash("Nota borrada correctamente");

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                GUARDAR NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/notas/:nota/guardar", "notas", "editar", function($nota_id) {
    $modelo_notas = new ModeloNotas();
    $nota = $modelo_notas->find($nota_id);

    if (!empty($nota)) {
      $traduccion_id = \Singular\Controller::get_post_variable("traduccion_id");

      $mensaje_es = \Singular\Controller::get_post_variable("mensaje_es");
      $mensaje_en = \Singular\Controller::get_post_variable("mensaje_en");

      $mensaje = empty($mensaje_es) ? $mensaje_en : $mensaje_es;

      $modelo_notas->update($nota_id, array(
        "notas_traducciones" => array(
          "id" => $traduccion_id,
          "mensaje" => $mensaje
        )
      ));

      \Singular\Controller::flash("Nota actualizada correctamente");
    }

    \Singular\Controller::redirect("/notas");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                EDITAR NOTA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/notas/:nota", "notas", "editar", function($nota_id) {
    $modelo_notas = new ModeloNotas();
    $nota = $modelo_notas->find($nota_id);

  //   \Singular\Controller::debug($nota);

    \Singular\View::render(array(
        "template" => "notas_editar",
        "data" => $nota
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              INICIO DE SESIÓN
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/login", function() {
    \Singular\View::render(array(
        "template" => "login"
    ));
  });
