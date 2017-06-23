<?php
/*
Plugin Name: Database Admin by DigitialPixies
Plugin URI: http://wordpress.digitalpixies.com/dpt-dnsmanager
Description: Friendly UI for browsing database contents. Administrative actions will be available in future version
Version: 0.1.0
Author: Robert Huie
Author URI: http://DigitalPixies.com
License: GPLv2
*/

if(!class_exists("dpt_dbadmin")) {
	class dpt_dbadmin {
		public static $data = null;
		public static function RegisterHooks() {
      add_action('rest_api_init', 'dpt_dbadmin::RESTAPIEndpoints');
      if(is_admin()) {
        add_action('admin_menu', 'dpt_dbadmin::AdminMenu');
  			add_action('admin_enqueue_scripts', 'dpt_dbadmin::EnableCSSJS');
      }
		}
    public static function RESTAPIEndpoints() {
      register_rest_route('dpt-dbadmin/v1', '/queries', array(
        'methods' => 'GET',
        'callback' => 'dpt_dbadmin::ListQueries',
        'permission_callback' => 'dpt_dbadmin::PermissionForQuery'
      ));
			register_rest_route('dpt-dbadmin/v1', '/queries', array(
        'methods' => 'POST',
        'callback' => 'dpt_dbadmin::Query',
        'permission_callback' => 'dpt_dbadmin::PermissionForQuery'
      ));
			register_rest_route('dpt-dbadmin/v1', '/tables', array(
        'methods' => 'GET',
        'callback' => 'dpt_dbadmin::ListTables',
        'permission_callback' => 'dpt_dbadmin::PermissionForQuery'
      ));
			register_rest_route('dpt-dbadmin/v1', '/tables/(?P<id>[A-Za-z0-9\-\_]+)', array(
        'methods' => 'GET',
        'callback' => 'dpt_dbadmin::GetTable',
        'permission_callback' => 'dpt_dbadmin::PermissionForQuery'
      ));
    }
    public static function PermissionForQuery($request) {
      if(current_user_can('administrator')) {
        return true;
      }
      return false;
    }
		public static function ListTables(WP_REST_Request $request) {
			global $wpdb;
			function Label($obj) {
				return array("id"=>$obj[0]);
			}
			return array_map(Label, $wpdb->get_results("SHOW TABLES", ARRAY_N));
    }
		public static function GetTable(WP_REST_Request $request) {
			global $wpdb;
			function Reformat($obj) {
				return array("id"=>$obj["Field"],"_raw"=>$obj);
			}
			$output = array();
			$output["page_size"]=10;
			$output["offset"]=0;
			$output["columns"]=array_map(Reformat, $wpdb->get_results("DESCRIBE ".$request["id"], ARRAY_A));
			$output["availableColumns"]=$output["columns"];
			$output["count"]=$wpdb->get_var("SELECT count(1) AS count FROM ".$request["id"], 0, 0);
			return $output;
    }
		public static function Query(WP_REST_Request $request) {
			global $wpdb;
			include_once("includes/QueryParser.Class.php");
			$output=$request->get_json_params();
			$queryParser = QueryParser::FromStructure($output);
			$output["results"]=$wpdb->get_results($queryParser->toString(), ARRAY_A);
			$output["sql"]=$queryParser->toString();
			$output["count"]=$wpdb->get_var("SELECT count(1) AS count FROM ".$queryParser->GetTable(), 0, 0);
			header('X-WP-Total: '.$output["count"]);
			return $output;
		}
    public static function ListQueries(WP_REST_Request $request) {
			global $wpdb;
			function Label($obj) {
				return array("id"=>$obj[0]);
			}
			return array_map(Label, $wpdb->get_results("SHOW TABLES", ARRAY_N));
    }
		public static function EnableCSSJS($hook) {
      wp_register_style('localizedbootstrap', plugin_dir_url(__FILE__).'includes/css/localizedbootstrap.css');
      wp_register_style('localizedbootstrap', plugins_url('includes/css/localizedbootstrap-theme.css', __FILE__));
      wp_enqueue_style('localizedbootstrap');

			wp_register_style(__CLASS__, plugin_dir_url(__FILE__).'includes/css/styles.css');
      wp_enqueue_style(__CLASS__);

      wp_register_script('angular-ui', plugin_dir_url(__FILE__).'includes/js/vendor.js', array(), "2.5.0", true);
      wp_enqueue_script('angular-ui');

			wp_register_script(__CLASS__, plugin_dir_url(__FILE__).'includes/js/scripts.js', array("angular-ui"), "2.5.0", true);
			wp_enqueue_script(__CLASS__);
			$params['ajax_url']=admin_url('admin-ajax.php');
      $params['rest_url']=esc_url_raw(rest_url());
      $params['nonce']=wp_create_nonce('wp_rest');
			wp_localize_script(__CLASS__, __CLASS__, $params);
    }
		public function dpt_dbadmin() {
      session_start();
//			if(!isset($_SESSION[__CLASS__]))
				$_SESSION[__CLASS__]=array(
				);
			dpt_dbadmin::$data=&$_SESSION[__CLASS__];
			add_action('init', 'dpt_dbadmin::RegisterHooks');
		}
		public static function AdminMenu() {
			add_menu_page('Database Admin by DigitalPixies', 'DB Admin', 'manage_options', __CLASS__, 'dpt_dbadmin::AdminHTML');
    }
		public static function AdminHTML() {
			$callbackurl = get_home_url(null, 'dpt-oauth-callback');
			print <<<EOF
<div class="wrap">
  <h1>Database Admin by DigitalPixies</h1>
EOF;
			include_once(dirname(__FILE__).'/admin.html');
			print <<<EOF
</div>
EOF;
		}
  }
}

$dpt_dbadmin = new dpt_dbadmin();
