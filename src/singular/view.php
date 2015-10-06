<?php
	namespace Singular;

	class View {
    ////////////////////////////////////////////////////////////////////////////
    //                            Render template
    ////////////////////////////////////////////////////////////////////////////
    public static function render($parameters) {
			$template = isset($parameters["template"]) ? $parameters["template"] : "";
			$page_title = isset($parameters["page_title"]) ? $parameters["page_title"] : NULL;
			$page_navigation = isset($parameters["page_navigation"]) ? $parameters["page_navigation"] : NULL;
			$data = isset($parameters["data"]) ? $parameters["data"] : array();

      $view_config = Configuration::get_view_path();
      $view_path = $view_config["server"];
      $partial_path = $view_config["partials"];

      if (Configuration::debug_enabled() || !file_exists($template_path)) {
        self::check_compiled_folder($view_path);

        $template_path = "$view_path/compiled/$template";
        $compiled = NULL;

        $source = file_get_contents("$view_path/$template.hbs");

        $compiled = \LightnCandy::compile($source, Array(
          'flags' => \LightnCandy::FLAG_STANDALONE | \LightnCandy::FLAG_RUNTIMEPARTIAL | \LightnCandy::FLAG_PARENT | \LightnCandy::FLAG_SPVARS | \LightnCandy::FLAG_HANDLEBARS,
          'basedir' => Array(
            $view_path,
            $partial_path
          ),
          'fileext' => Array(
            '.hbs'
        	),
          'helpers' => Array(
            'select_equals' => function ($args) {
              return $args[0] == $args[1] ? "selected" : "";
            }
          )
        ));

        file_put_contents($template_path, $compiled);
      }
      else {
        $compiled = file_get_contents($template_path);
      }

      $renderer = include($template_path);

      echo $renderer(array(
        "page" => self::get_page_info($page_title, $page_navigation),
        "data" => $data
      ));
    }

    ////////////////////////////////////////////////////////////////////////////
    //                     Check compiled folder exists
    ////////////////////////////////////////////////////////////////////////////
    private static function check_compiled_folder($view_path) {
      $compiled_path = "$view_path/compiled";

      if (!file_exists($compiled_path)) {
        mkdir($compiled_path);
      }
    }

    ////////////////////////////////////////////////////////////////////////////
    //                            Get page info
    ////////////////////////////////////////////////////////////////////////////
    private static function get_page_info($subtitle = "", $navigation = "") {
      $usuario = isset($_SESSION["login"]) ? $_SESSION["login"] : NULL;

      $title = Configuration::get_app_settings("title");
      $full_title = $title;

      if (!empty($subtitle))
        $full_title = "$title :: $subtitle";

			$host = Configuration::get_host();

      $options = array(
        "version" => Configuration::get_app_settings("version"),
        "host" => $host,
				"web" => "$host/web",
        "title" => $title,
        "full_title" => $full_title,
        "navigation" => array($navigation => TRUE),
        "user" => Authentication::get_user(),
				"flash" => Flash::get_message(),
				"debug" => Debug::get_message()
      );

      return $options;
    }
  }
