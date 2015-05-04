<?php

require_once dirname( __FILE__ ) . '/class.settings-api.php';

/**
 * Admin options handler class
 *
 * @since 0.4  
 * @author EGANY <support@egany.com>  
 */
class Egany_FB_Group_To_WP_Admin {

    private $settings_api;

    function __construct() {
        $this->settings_api = new Egany_Settings_API();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() { 
	
		// phong.nguyen add main menu (+logo)  
		// $main_menu_id = 'edit.php?post_type=egany_fb2wp_post';  // egany_fb2wp_post
		// add_menu_page(__('EGANY Facebook2WP Settings', 'EGANY' ), __('Facebook to WP', 'EGANY' ), 'manage_options', $main_menu_id, null, plugins_url('/assets/images/logo.png', EGANY_PLUGIN_FILE_FB2WP) , 30); 
		
        // add_submenu_page( $main_menu_id, __( 'EGANY Facebook2WP Settings', 'EGANY' ), __( 'Settings', 'EGANY' ), 'manage_options', 'egany_fb2wp-settings', array( $this, 'settings_page' ) ); 
		add_object_page( __( 'EGANY Facebook2WP Settings', 'EGANY' ), __( 'Facebook to WP', 'EGANY' ), 'manage_options', 'egany_fb2wp-settings', array( $this, 'settings_page' ), plugins_url('/assets/images/logo.png', EGANY_PLUGIN_FILE_FB2WP) );
		
		
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'fb2wp_general',
                'title' => __( 'General', 'EGANY' ) 
            ), 
            array(
                'id' => 'fb2wp_page',
                'title' => __( 'Page', 'EGANY' )
            ),
            array(
                'id' => 'fb2wp_group',
                'title' => __( 'Group', 'EGANY' )
            ), 
        );

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array();
		//a:6:{s:6:"app_id";s:16:"1419291691710668";s:10:"app_secret";s:32:"ceca9b3645dbcc815b407a151618797f";s:8:"group_id";s:15:"338026176339978";s:5:"limit";s:2:"30";s:11:"post_status";s:5:"draft";s:14:"comment_status";s:4:"open";}
		// // var_dump(get_post_statuses());  
		
		//tab: Page... 
        $settings_fields['fb2wp_page'] = array(
			
            array(
                'name'    => 'page_id',
                'label'   => __( 'Facebook Page ID', 'EGANY' ),
                'default' => '',
                'desc'    => __( 'Add your facebook Page ID. e.g: 241884142616448' )
            ),
		);
		
		$settings_fields['fb2wp_page'] = array_merge($settings_fields['fb2wp_page'], $this->get_fb_info_general() ); 
		
		//tab: Group 
        $settings_fields['fb2wp_group'] = array(
			
            array(
                'name'    => 'group_id',
                'label'   => __( 'Facebook Group ID', 'EGANY' ),
                'default' => '',
                'desc'    => __( 'Add your Facebook Group ID. e.g: 241884142616448' )
            ),
		);  
		$settings_fields['fb2wp_group'] = array_merge($settings_fields['fb2wp_group'], $this->get_fb_info_general() ); 
		
		//tab: General 
        $settings_fields['fb2wp_general'] = array(
			
            // array(
                // 'name'    => 'access_type',
                // 'label'   => __( 'Access Type', 'EGANY' ),
                // 'default' => 'publish',
                // 'type'    => 'select',
                // 'options' => array(
					// 'fb_app' => 'Facebook App.', 
					// 'fb_access_token' => 'Facebook Access Token', 
					// ),
                // 'desc'    => __( 'Use App. or Token to access Facebook' ) 
            // ),
			 
            // array(
                // 'name'    => 'app_id',
                // 'label'   => __( 'Facebook App ID', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => sprintf( __( 'Insert your facebook application ID from <a href="%s" target="blank">here</a>.', 'EGANY' ), 'https://developers.facebook.com/apps/' )
            // ),
            // array(
                // 'name'    => 'app_secret',
                // 'label'   => __( 'Facebook App Secret', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => __( 'Insert your facebook App Secret' )
            // ),
			// array(
                // 'name'    => 'access_token',
                // 'label'   => __( 'Access Token', 'EGANY' ),
                // 'default' => '',
                // 'class' => 'hidden',
                // 'desc'    => sprintf( __( 'Insert your facebook access token <a href="%s" target="blank">here</a>.', 'EGANY' ), 'https://developers.facebook.com/tools/explorer/' )
            // ),
			
			
			// array(
                // 'name'    => 'source_type',
                // 'label'   => __( 'Source Type', 'EGANY' ),
                // 'default' => 'publish',
                // 'type'    => 'select',
                // 'options' => array(
					// 'st_group' => 'Facebook Group', // default is Group if there's not matching..
					// 'st_page' => 'Facebook Page', 
					// ),
                // 'desc'    => __( 'Connect to Facebook Page or Group' ) 
            // ),
			
            // array(
                // 'name'    => 'page_id',
                // 'label'   => __( 'Facebook Page ID', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => __( 'Add your facebook Page ID. e.g: 241884142616448' )
            // ),
			
            // array(
                // 'name'    => 'group_id',
                // 'label'   => __( 'Facebook Group ID', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => __( 'Add your Facebook Group ID. e.g: 241884142616448' )
            // ),
            // array(
                // 'name'    => 'limit',
                // 'label'   => __( 'List per Query', 'EGANY' ),
                // 'default' => '30',
                // 'desc'    => __( 'Posts fetched from Facebook in a single query' )
            // ),
            // array(
                // 'name'    => 'post_status',
                // 'label'   => __( 'Default Post Status', 'EGANY' ),
                // 'default' => 'publish',
                // 'type'    => 'select',
                // 'options' => get_post_statuses(),
                // 'desc'    => __( 'What will be the post status when a post is imported/created' )  
            // ),
            // array(
                // 'name'    => 'comment_max',
                // 'label'   => __( 'Get Comments per Post', 'EGANY' ),
                // 'default' => 'open',
                // 'type'    => 'select',
                // 'options' => array(
                    // '25'   => __( '25', 'EGANY' ),
                    // 'unlimited' => __( 'Unlimited', 'EGANY' )
                // ),
            // ),

        );
		$settings_fields['fb2wp_general'] = array_merge($settings_fields['fb2wp_general'], $this->get_fb_info_general() ); 

        return $settings_fields;
    }

	
	/*
	 * get_fb_info_general...
	 * 
	 * @author: phong.nguyen 20150421 
	 * @param: none
	 * @return: array of fields 
	 */ 
    function get_fb_info_general() {
		return array(
			array(
                'name'    => 'access_type',
                'label'   => __( 'Access Type', 'EGANY' ),
                'default' => 'publish',
                'type'    => 'select',
                'options' => array(
					'' => '', 
					'fb_app' => 'Facebook App.', 
					'fb_access_token' => 'Facebook Access Token', 
					),
                'desc'    => __( 'Use App. or Token to access Facebook' ) 
            ),
			 
            array(
                'name'    => 'app_id',
                'label'   => __( 'Facebook App ID', 'EGANY' ),
                'default' => '',
                'desc'    => sprintf( __( 'Insert your facebook application ID from <a href="%s" target="blank">here</a>.', 'EGANY' ), 'https://developers.facebook.com/apps/' )
            ),
            array(
                'name'    => 'app_secret',
                'label'   => __( 'Facebook App Secret', 'EGANY' ),
                'default' => '',
                'desc'    => __( 'Insert your facebook App Secret' )
            ),
			array(
                'name'    => 'access_token',
                'label'   => __( 'Access Token', 'EGANY' ),
                'default' => '',
                'class' => 'hidden',
                'desc'    => sprintf( __( 'Insert your facebook access token <a href="%s" target="blank">here</a>.', 'EGANY' ), 'https://developers.facebook.com/tools/explorer/' )
            ),
			
			
			// // array(
                // // 'name'    => 'source_type',
                // // 'label'   => __( 'Source Type', 'EGANY' ),
                // // 'default' => 'publish',
                // // 'type'    => 'select',
                // // 'options' => array(
					// // 'st_group' => 'Facebook Group', // default is Group if there's not matching..
					// // 'st_page' => 'Facebook Page', 
					// // ),
                // // 'desc'    => __( 'Connect to Facebook Page or Group' ) 
            // // ),  
			
            // array(
                // 'name'    => 'page_id',
                // 'label'   => __( 'Facebook Page ID', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => __( 'Add your facebook Page ID. e.g: 241884142616448' )
            // ),
			
            // array(
                // 'name'    => 'group_id',
                // 'label'   => __( 'Facebook Group ID', 'EGANY' ),
                // 'default' => '',
                // 'desc'    => __( 'Add your Facebook Group ID. e.g: 241884142616448' )
            // ),
            array(
                'name'    => 'limit',
                'label'   => __( 'List per Query', 'EGANY' ),
                'default' => '30',
                'desc'    => __( 'Posts fetched from Facebook in a single query' )
            ),
            // array(
                // 'name'    => 'max_page',
                // 'label'   => __( 'Max. Queries', 'EGANY' ),
                // 'default' => '10',
                // 'desc'    => __( 'How many times for fetching data.' ) 
            // ),
            array(
                'name'    => 'post_status',
                'label'   => __( 'Default Post Status', 'EGANY' ),
                'default' => 'publish',
                'type'    => 'select',
                'options' => array_merge(array('' => ''), get_post_statuses()), // get_post_statuses(), // 
                'desc'    => __( 'What will be the post status when a post is imported/created' )
            ),
            array(
                'name'    => 'comment_max',
                'label'   => __( 'Get Comments per Post', 'EGANY' ), 
                'default' => '25',
                'type'    => 'select',
                'options' => array(
                    ''   => __( '', 'EGANY' ),
                    '25'   => __( '25', 'EGANY' ), 
                ),
            ),
		
		); 
		
		
	}
	
    function settings_page() {
        echo '<div class="wrap">';
        settings_errors();

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }
}