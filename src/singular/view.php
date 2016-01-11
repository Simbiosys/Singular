<?php
	/**
	* Singular's view class.
	* Every application view should inherit from this class
	*/
	namespace Singular;

	/**
  * Singular's view class.
	* Every application view should inherit from this class
  */
	class View {
		/**
      * This method renders a template.
      *
      * @param Array $parameters Method parameters.
			* <ul>
			*		<li><strong>template:</strong> template to render.</li>
			*		<li><strong>page_title:</strong> HTML document title.</li>
			*		<li><strong>page_navigation:</strong> Application navigation for this page.</li>
			*		<li><strong>data:</strong> Data to show in the view (JSON).</li>
			*		<li><strong>extra:</strong> Extra data to send to the view.</li>
			*		<li><strong>layout:</strong> HTML layout to apply.</li>
			*	</ul>
      *
      * @return void
      */
    public static function render($parameters) {
			$template = isset($parameters["template"]) ? $parameters["template"] : "";
			$page_title = isset($parameters["page_title"]) ? $parameters["page_title"] : NULL;
			$page_navigation = isset($parameters["page_navigation"]) ? $parameters["page_navigation"] : NULL;
			$data = isset($parameters["data"]) ? $parameters["data"] : array();
			$extra = isset($parameters["extra"]) ? $parameters["extra"] : array();
			$layout = isset($parameters["layout"]) ? $parameters["layout"] : NULL;

      $view_config = Configuration::get_view_path();

      $view_path = is_array($view_config["server"]) ?
					$view_config["server"] : array($view_config["server"]);

      $partial_path = is_array($view_config["partials"]) ?
					$view_config["partials"] : array($view_config["partials"]);

			$compiled_path = $view_config["compiled"];

			$all_paths = array();
			$all_paths = array_merge($all_paths, $view_path);
			$all_paths = array_merge($all_paths, $partial_path);

			self::check_compiled_folder($view_config["compiled"]);
			$sufix = empty($layout) ? "" : "-$layout";

			$template_path = NULL;
			$source = NULL;

			// Loop all posible paths
			foreach ($view_path as $path) {
				if (file_exists("$path/$template.hbs")) {
					$template_path = "$compiled_path/$template$sufix";
					$source = "$path/$template.hbs";
				}
			}

      if (Configuration::debug_enabled() || !file_exists($template_path)) {
        $compiled = NULL;

				$source = file_get_contents($source);

				// Set layout
				if (!empty($layout)) {
					$layout_path = Configuration::get_layout_path();
					$layout_full_path = "$layout_path/$layout";

					if (file_exists($layout_full_path)) {
						$layout_contents = file_get_contents($layout_full_path);

						if ($layout_contents) {
							$source = str_replace("{{@content}}", $source, $layout_contents);
						}
					}
				}

				$compile_options = Array(
          'flags' => \LightnCandy::FLAG_STANDALONE | \LightnCandy::FLAG_RUNTIMEPARTIAL | \LightnCandy::FLAG_PARENT | \LightnCandy::FLAG_SPVARS | \LightnCandy::FLAG_HANDLEBARS,
          'basedir' => $all_paths,
          'fileext' => Array(
            '.hbs'
        	)
        );

				$helpers = Helper::get_helpers();
				$custom_helpers = Configuration::get_helpers();

				if ($custom_helpers) {
					$helpers = call_user_func(array($custom_helpers, 'get_helpers'));
				}

				$compile_options['helpers'] = $helpers;

        $compiled = \LightnCandy::compile($source, $compile_options);

				// Check if directory exists
				self::check_compiled_folder(dirname($template_path));

        file_put_contents($template_path, $compiled);
      }
      else {
        $compiled = file_get_contents($template_path);
      }

      $renderer = include($template_path);

      echo $renderer(array(
        "page" => self::get_page_info($page_title, $page_navigation),
        "data" => $data,
        "labels" => self::get_labels(),
				"extra" => $extra
      ));
    }

		/**
      * Checks that compiled-view folder exists, creates if not.
      *
      * @param string $compiled_path Path for compiled views.
      *
      * @return void
      */
    private static function check_compiled_folder($compiled_path) {
			if (empty($compiled_path)) {
				return;
			}

	    if (!file_exists($compiled_path)) {
	      mkdir($compiled_path, 0777, TRUE);
	    }
    }

		/**
      * Generates page info to send to the view
      *
      * @param string $subtitle This page's title.
			* @param string $navigation This page's navigation.
      *
      * @return Array
      */
    private static function get_page_info($subtitle = "", $navigation = "") {
      $usuario = isset($_SESSION["login"]) ? $_SESSION["login"] : NULL;

      $title = Configuration::get_app_settings("title");
      $full_title = $title;

      if (!empty($subtitle)) {
        $full_title = "$title :: $subtitle";
		  }

		  $host = Configuration::get_host();

	    $options = array(
	      "version" => Configuration::get_app_settings("version"),
	      "host" => $host,
				"web" => "$host/web",
	      "title" => $title,
	      "full_title" => $full_title,
	      "navigation" => array($navigation => TRUE),
	      "user" => Authentication::get_user(),
				"user_data" => Authentication::get_user_data(),
				"language" => Authentication::get_language(),
				"languages" => Configuration::get_available_languages(),
				"flash" => Flash::get_message(),
				"debug" => Debug::get_message()
	    );

		  $class = get_called_class();
	      $obj = new $class();

		  $custom_options = $obj->add_custom_page_info($options);

      return array_merge($options, $custom_options);
    }

		/**
      * This method when overwritten in child classes allows the programmer to
			* add custom data to 'page' object that is sent to the view
      *
      * @param string $defaults Default data.
      *
      * @return Array
      */
		protected function add_custom_page_info($defaults) {
		  return array();
		}

		/**
      * Returns language labels for the user-selected language.
      *
      * @return Array
      */
		public static function get_labels() {
		  $language = Authentication::get_language();
		  $path = Configuration::get_languages_path();
		  $language_path = "$path/$language.json";

		  if (file_exists($language_path)) {
		  	return json_decode(file_get_contents($language_path), true);
		  }
		  else {
		  	return array();
		  }
		}

		/**
      * Returns the value for a label in the user-selected language.
      *
      * @param string $key Label key to obtain.
      *
      * @return string
      */
		public static function get_label($key) {
			$labels = self::get_labels();

			return isset($labels[$key]) ? $labels[$key] : NULL;
		}
  }
