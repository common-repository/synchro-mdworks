<?php
/*
Plugin Name: MDWorks - Synchronisation
Description: Plugin permettant de synchroniser une base de données hébergée sur MDWorks avec les utilisateurs Wordpress.
Version: 1.0.3
Author: Ediware
Author URI: http://www.ediware.net/
License: GPL2
Text Domain: mdworks-hosted
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class spHostedLists {
	
    private $tzMessage = array();
    private $tzStmt;
	
	public function __construct() {

		date_default_timezone_set('Europe/Paris');

		// actions
        add_action( 'admin_init', array( $this, 'save' ) );
        add_action( 'admin_menu', array( $this, 'add_menu_items' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'user_register', array( $this, 'newInsertUserToDb' ), 10, 1 );
        add_action( 'profile_update', array( $this, 'updateUserToDb' ), 10, 2 );
        add_action( 'edit_user_profile_update', array( $this, 'saveOldMail' ) );
        add_action( 'delete_user', array( $this, 'deleteUserToDb' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
        // filters
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
		
	}
    
    /**
	 * Deactivate the plugin.
	 */
	public function deactivation() {

	}
    
    /**
     * Ajouter un sous menu dans le menu option pour la configuration du plugin
     */
    public function add_menu_items(){        
        add_options_page( 'MDWorks', 'MDWorks', 'delete_others_pages', 'hostedlists',
            array($this, 'confighostedlists'));
    }
    
	
    /**
     * Ajouter un menu links
     */
	public function add_action_links ( $links ) {
		 $mylinks = array(
		 '<a href="' . admin_url( 'options-general.php?page=hostedlists' ) . '">'.__( 'Paramètres', 'mdworks-hosted' ).'</a>',
		 );
		 
		return array_merge( $links, $mylinks );
	}
    
    /**
     * Rendu page bandeau
     */
    public function confighostedlists(){

        $Res = $this->getLstDataBase();
        if( isset($Res[0]) && $Res[0] == 'Erreur'){
            //$this->tzMessage = array('type' => 'error', 'msg' => utf8_encode($Res[2]));
        }else{
            $oLstDataBase = $Res;
        }

        $tzMessage = $this->tzMessage;
        $tzStmt = unserialize(get_option("mdworks_hosted_stmt"));
        $iDbSelect = isset($_POST['slt_db']) ? $_POST['slt_db'] : '';
        $iDbSelect = ( !$iDbSelect ) ? get_option('mdworks_hosted_slt_db') : $iDbSelect;
        $tzMapping = unserialize(get_option("mdworks_hosted_mapping"));		
		$zLogin =  get_option( 'mdworks_hosted_login' );		

        if( empty($tzStmt) && is_numeric($iDbSelect) ){
            $Result = $this->getStmtBase($iDbSelect);
            if( isset($Result[0]) && $Result[0] == 'Erreur'){
                $this->tzMessage = array('type' => 'error', 'msg' => __( 'Erreur de récupération structure base de données', 'mdworks-hosted' ));
            }else{
                $tzStmt = $Result;
                $tzMapping = unserialize(get_option("mdworks_hosted_mapping"));
                update_option("mdworks_hosted_stmt", serialize($tzStmt));
            }
        }
        
        include_once(plugin_dir_path( __FILE__ ) . 'views/vw-settings.php');
    }
    

	
	/**
	 * Enregistrer les paramettres.
	 */
	public function save() {
            global $wpdb;

            if( isset($_GET['page']) && $_GET['page'] == 'hostedlists' && isset($_GET['logout']) && $_GET['logout'] === '1' ){
                    $this->logout();
            }
            
            if( isset($_GET['page']) && $_GET['page'] == 'hostedlists' && isset($_GET['delmapping']) && $_GET['delmapping'] === '1' ){
                delete_option( 'mdworks_hosted_stmt' );
                delete_option( 'mdworks_hosted_slt_db' );
                delete_option( 'mdworks_hosted_mapping' );
                wp_redirect(admin_url('options-general.php?page=hostedlists'));
		exit;
            }
		
            if( isset( $_POST['mdworks_wpnonce'] ) && wp_verify_nonce( $_POST['mdworks_wpnonce'], 'mdworks_login' ) ){
                $login = $wpdb->_real_escape($_POST['login']);
                $password = $wpdb->_real_escape($_POST['password']);

                $Res = $this->login($login, $password);

                if( is_array( $Res ) ){
                    $this->tzMessage = array('type' => 'error', 'msg' => __( 'Nom d\'utilisateur ou mot de passe incorrect.', 'mdworks-hosted' ));
                }else{
                    update_option("mdworks_hosted_login", $login);
                    update_option("mdworks_hosted_password", $password);
                }
                    }

            if( isset( $_POST['mdworks_wpnonce'] ) && wp_verify_nonce( $_POST['mdworks_wpnonce'], 'mdworks_db' ) ){

                if( isset($_POST['slt_db']) && is_numeric($_POST['slt_db']) ){
                    $Res = $this->getStmtBase($_POST['slt_db']);
                    if( isset($Res[0]) && $Res[0] == 'Erreur'){
                        $this->tzMessage = array('type' => 'error', 'msg' => __( 'Erreur lors de la récupération de la structure de la base de données', 'mdworks-hosted' ));
                    }else{
                        $this->tzStmt = $Res;
                        update_option("mdworks_hosted_stmt", serialize($Res));
                                            update_option( 'mdworks_hosted_slt_db', $_POST['slt_db'] );
                    }
                }

            }

            if( isset( $_POST['mdworks_wpnonce'] ) && wp_verify_nonce( $_POST['mdworks_wpnonce'], 'mdworks_insert' ) ){

                if( isset($_POST['slt_db']) && is_numeric($_POST['slt_db']) ) {
                    $this->utf8_decode_deep($_POST['fields']);
                    $Res = $this->insertData($_POST['slt_db'], $_POST['fields']);

                    if( isset($Res[0]) && $Res[0] == 'Erreur'){
                        $this->tzMessage = array('type' => 'error', 'msg' => $Res[2]);
                    }else{
                        update_option("mdworks_hosted_slt_db", $_POST['slt_db'], 'no');
                        $this->tzMessage = array('type' => 'success', 'msg' => __('Données utilisateur enregistrées.'));
                    }
                }else{
                    $this->tzMessage = array('type' => 'error', 'msg' => __('Identifiant base de données inconnu.'));
                }
            }

            if( isset( $_POST['mdworks_wpnonce'] ) && wp_verify_nonce( $_POST['mdworks_wpnonce'], 'mdworks_mapping' ) ){
                if( isset($_POST['slt_db']) && is_numeric($_POST['slt_db']) ) {
                    $tzUser_stmt = $_POST['user_stmt'];
                    $tiDb_stmt = $_POST['db_stmt'];
                    $tzMapping = array();
                    foreach ($tzUser_stmt as $k => $val) {
                        if ($val == "") {
                            $this->tzMessage = array('type' => 'error', 'msg' => __('Le mapping est vide.'));
                            break;
                        } else {
                            $tzMapping[$tiDb_stmt[$k]] = $val;
                            update_option("mdworks_hosted_mapping", serialize($tzMapping));
                        }
                    }

                    $blogusers = get_users();
                    foreach ($blogusers as $user) {
                        $Res = $this->mappingCheckAndInsertToDb($tzMapping, $user);
                        if( isset($Res[0]) && $Res[0] == 'Erreur'){
                            $this->tzMessage = array('type' => 'error', 'msg' => $Res[2]);
                        }else{
                            update_option("mdworks_hosted_slt_db", $_POST['slt_db'], 'no');
                            $this->tzMessage = array('type' => 'success', 'msg' => __('Tous les utilisateurs ont bien été enregistrés dans la base de données.'));
                        }
                    }
                }else{
                    $this->tzMessage = array('type' => 'error', 'msg' => 'Identifiant base de données innconu.');
                }
            }
	}

    /**
     * Relation mapping
     * @param $tzMapping
     * @param $user
     * @return mixed
     */
    public function mappingCheckAndInsertToDb($tzMapping, $user){
        $tzData = array();
        foreach ($tzMapping as $k => $val) {
            switch ($val) {
                case 'user_login' :
                    array_push($tzData, $user->user_login);
                    break;
                case 'user_email' :
                    array_push($tzData, $user->user_email);
                    break;
                case 'first_name' :
                    array_push($tzData, $user->first_name);
                    break;
                case 'last_name' :
                    array_push($tzData, $user->last_name);
                    break;
                case 'user_url' :
                    array_push($tzData, $user->user_url);
                    break;
                case 'user_pass' :
                    array_push($tzData, $user->user_pass);
                    break;
            }

        }
        $this->utf8_decode_deep($tzData);
        $iSltDb = isset($_POST['slt_db']) ? $_POST['slt_db'] : get_option('mdworks_hosted_slt_db');
        return  $this->insertData($iSltDb, $tzData);
    }

    /**
     * Apres action creation user on l'insert dans la base
     */
    public function newInsertUserToDb($user_id){
        if( isset( $_POST['action'] ) && $_POST['action'] == 'createuser' ){
            $oUser = get_user_by("id", $user_id);
            $tzMapping =  unserialize(get_option("mdworks_hosted_mapping"));
            if( $tzMapping ) $this->mappingCheckAndInsertToDb($tzMapping, $oUser);
        }
    }

    /**
     * Apres modification user
     * @param $user_id
     */
    public function updateUserToDb($user_id){
        if( isset( $_POST['action'] ) && $_POST['action'] == 'update' ){
            $oUser = get_user_by("id", $user_id);
            $tzMapping =  unserialize(get_option("mdworks_hosted_mapping"));
            if( $tzMapping ){
                $oldmail = get_option("mdworks_hosted_oldmail");
                $Res = $this->searchUserInDb($tzMapping, $oUser, $oldmail);
                if( isset($Res[0]['pkey_id']) && is_numeric($Res[0]['pkey_id']) ){
                    $this->deleteInDb($Res[0]['pkey_id']); //Suppression utilisateur
                    $this->mappingCheckAndInsertToDb($tzMapping, $oUser); //Insertion nouveau
                }

            }
        }
    }
    
    public function saveOldMail( $user_id ){
        if ( current_user_can('edit_user',$user_id) ){
            $oUser = get_user_by("id", $user_id);
            update_option("mdworks_hosted_oldmail", $oUser->user_email);
        }
    }

    /**
     * Recherche user dans DB
     * @param $tzMapping
     * @param $oUser
     * @return bool or Int
     */
    public function searchUserInDb($tzMapping, $oUser, $oldmail = false){
        $keySearh = array_search("user_email", $tzMapping);
        $tzStmt = unserialize(get_option("mdworks_hosted_stmt"));
        $iDbSelect = get_option('mdworks_hosted_slt_db');
        if( !$iDbSelect ) return false;
        try {
            $client = new SoapClient(null,
                                     array('location' => "https://www.eml-srv.com/_soap/control.php",
                                           'uri'      => "https://www.eml-srv.com", 'encoding'=>'ISO-8859-1'  ));

            $login = get_option('mdworks_hosted_login');
            $password = get_option('mdworks_hosted_password');
            if( $oldmail )
                $variable = $client->Recherche_BDD($login, $password, $iDbSelect, $tzStmt[$keySearh]['field'], $oldmail);
            else
                $variable = $client->Recherche_BDD($login, $password, $iDbSelect, $tzStmt[$keySearh]['field'], $oUser->user_email);

            return $variable;
        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);

        }
    }

    /**
     * Hook delete user
     * @param $user_id int
     */
    public function deleteUserToDb($user_id){
        $oUser = get_user_by("id", $user_id);
        $tzMapping =  unserialize(get_option("mdworks_hosted_mapping"));
        if( $tzMapping ){
            $Res = $this->searchUserInDb($tzMapping, $oUser);
            if( isset($Res[0]['pkey_id']) && is_numeric($Res[0]['pkey_id']) ){
                $this->deleteInDb($Res[0]['pkey_id']); //Suppression utilisateur
            }
        }
    }

    public function deleteInDb($id){
        try {
            $client = new SoapClient(null,
                array('location' => "https://www.eml-srv.com/_soap/control.php",
                    'uri'      => "https://www.eml-srv.com", 'encoding'=>'ISO-8859-1'  ));

            $login = get_option('mdworks_hosted_login');
            $password = get_option('mdworks_hosted_password');
            $iDbSelect = get_option('mdworks_hosted_slt_db');

            return $client->Supprim_BDD($login, $password, $iDbSelect, $id);
        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);

        }
    }

    /**
     * Test login et mot de pass mdworks
     * @param $login
     * @param $pwd
     * @return mixed
     */
    public function login($login, $pwd){
        try {
            $client = new SoapClient(null,
                                     array('location' => "https://www.eml-srv.com/_soap/control.php",
                                            'uri'     => "https://www.eml-srv.com",
                                            'encoding'=>'ISO-8859-1' ));

            $variable = $client->Recup_Credit($login, $pwd);

            return $variable;

        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
    }
	
    public function logout(){
        delete_option( 'mdworks_hosted_stmt' );
        delete_option( 'mdworks_hosted_slt_db' );
        delete_option( 'mdworks_hosted_mapping' );
        delete_option( 'mdworks_hosted_login' );
        delete_option( 'mdworks_hosted_password' );
        wp_redirect(admin_url('options-general.php?page=hostedlists'));
        exit;
    }

    /**
     * Get list database
     * @return mixed
     */
    public function getLstDataBase(){
        try {
            $client = new SoapClient(null,
                                     array('location' => "https://www.eml-srv.com/_soap/control.php",
                                           'uri'      => "https://www.eml-srv.com",
                                           'encoding' => 'ISO-8859-1' ));
            $login = get_option('mdworks_hosted_login');
            $password = get_option('mdworks_hosted_password');

            $variable = $client->Liste_BDD($login, $password);

            return $variable;

        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);

        }
    }

    /**
     * Get Structure database
     * @param $iDb int
     * @return mixed
     */
    public function getStmtBase($iDb){
        try {
            $client = new SoapClient(null,
                                     array('location' => "https://www.eml-srv.com/_soap/control.php",
                                           'uri'      => "https://www.eml-srv.com",
                                           'encoding' => 'ISO-8859-1' ));

            $login = get_option('mdworks_hosted_login');
            $password = get_option('mdworks_hosted_password');

            $variable = $client->Structure_BDD($login, $password, $iDb);

            return $variable;

        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);

        }
    }

    /**
     * Insertion données dans la base
     * @param $iDb int
     * @param $tzVals array
     */
    public function insertData($iDb, $tzVals){
        try {
            $client = new SoapClient(null,
                                    array('location'   => "https://www.eml-srv.com/_soap/control.php",
                                        'uri'       => "https://www.eml-srv.com",
                                        'encoding'  => 'ISO-8859-1'));

            $login = get_option('mdworks_hosted_login');
            $password = get_option('mdworks_hosted_password');

            $variable = $client->Ajout_BDD($login, $password, $iDb, $tzVals);

            return $variable;

        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);

        }
    }
	
    /**
	 * Ajout scrip
	 */
	public function admin_scripts()
	{
        wp_enqueue_script('mdworks-hosted-front-js', plugins_url( 'js/mdworks-hosted.js', __FILE__ ), array( 'jquery' ) );
	}

    /**
     * Load language.
     */
    public function load_textdomain() {
        $load = load_plugin_textdomain( 'mdworks-hosted', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Utf8 decode
     * @param $input
     */
    public function utf8_decode_deep(&$input) {
        if (is_string($input)) {
            $input = utf8_decode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                $this->utf8_decode_deep($value);
            }
            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));
            foreach ($vars as $var) {
                $this->utf8_decode_deep($input->$var);
            }
        }
    }

	
}

new spHostedLists();