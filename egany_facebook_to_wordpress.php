<?php
/*
Plugin Name: EGANY Facebook to WordPress importer
Plugin URI: http://egany.com/
Description: Import Facebook group/page posts to WordPress
Version: 1.0
Author: EGANY
Author URI: http://egany.com/ 
License: GNU General Public License v3.0
*/

/**
 * Copyright (c) 2015 EGANY (email: support@egany.com). All rights reserved.
 * 
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-11  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/includes/admin.php';
}

// Egany_FB_Group_To_WP::init()->trash_all();

/**
 * Egany_FB_Group_To_WP class
 *
 * @class Egany_FB_Group_To_WP The class that holds the entire Egany_FB_Group_To_WP plugin
 */
class Egany_FB_Group_To_WP {

    private $post_type = 'egany_fb2wp_post';

    /**
     * Constructor for the Egany_FB_Group_To_WP class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
		define( 'EGANY_PLUGIN_FILE', __FILE__ );
		
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'init', array( $this, 'register_post_type' ) );

        add_action( 'init', array( $this, 'debug_run' ) );
        add_action( 'init', array( $this, 'historical_import' ) );
        add_action( 'egany_fb2wp_import', array( $this, 'do_import' ) );

        add_filter( 'cron_schedules', array($this, 'cron_schedules') );

        add_filter( 'get_avatar_comment_types', array( $this, 'avatar_comment_type' ) );
        add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 3 );

        add_filter( 'the_content', array( $this, 'the_content' ) );
		
        if ( is_admin() ) { 
            new Egany_FB_Group_To_WP_Admin();
			
			// phong.nguyen 20150416: register js/style such as Admin, Admin-Head  
			add_action('admin_enqueue_scripts', array($this,'register_scripts_styles_admin'));
			// add_action('admin_head', array($this,'register_scripts_admin_head')); 
        }
    }

    /**
     * Registers our custom post type
     *
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name'                => _x( 'Facebook Posts', 'Post Type General Name', 'EGANY' ),
            'singular_name'       => _x( 'Facebook Post', 'Post Type Singular Name', 'EGANY' ),
            'menu_name'           => __( 'Facebook Posts', 'EGANY' ),
            'parent_item_colon'   => __( 'Parent Post:', 'EGANY' ),
            'all_items'           => __( 'All Posts', 'EGANY' ),
            'view_item'           => __( 'View Post', 'EGANY' ),
            'add_new_item'        => __( 'Add New Post', 'EGANY' ),
            'add_new'             => __( 'Add New', 'EGANY' ),
            'edit_item'           => __( 'Edit Post', 'EGANY' ),
            'update_item'         => __( 'Update Post', 'EGANY' ),
            'search_items'        => __( 'Search Post', 'EGANY' ),
            'not_found'           => __( 'Not found', 'EGANY' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'EGANY' ),
        );

        $rewrite = array(
            'slug'                => 'fb-post',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => false,
        );

        $args = array(
            'label'               => __( 'egany_fb2wp_post', 'EGANY' ),
            'description'         => __( 'WordPress Facebook Post', 'EGANY' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'post-formats', 'comments' ),
            'taxonomies'          => array( 'category', 'post_tag' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => false, //phong.nguyen 20150430: change to "false" 
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'             => $rewrite,
            'capability_type'     => 'post',
        );

        register_post_type( $this->post_type, $args );
    }

    /**
     * Initializes the Egany_FB_Group_To_WP() class
     *
     * Checks for an existing Egany_FB_Group_To_WP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Egany_FB_Group_To_WP();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        if ( false == wp_next_scheduled( 'egany_fb2wp_import' ) ){
            wp_schedule_event( time(), 'half-hour', 'egany_fb2wp_import' );
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'egany_fb2wp_import' );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'EGANY', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add new cron schedule
     *
     * @param  array $schedules
     * @return array
     */
    function cron_schedules( $schedules ) {
        $schedules['half-hour'] = array(
            'interval' => MINUTE_IN_SECONDS * 30,
            'display' => __( 'In every 30 Minutes', 'EGANY' )
        );

        return $schedules;
    }

    /**
     * Manually trigger the cron
     *
     * @return void
     */
    function debug_run() {
        if ( !isset( $_GET['fb2wp_test'] ) ) {
            return;
        }  

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $this->do_import();

        die();
    }

    /**
     * Get the facebook settings
     *
     * @param string $strPage Ex: fb2wp_general,... 
     * @return array
     */
    function get_settings($strPage) {
        $option = get_option( $strPage, array() );
		$source_id = $this->get_source_id($option, 'page'); 
		
		//phong.nguyen 20150429 removed this 
        // // return if no configuration found
        // if ( !isset( $option['app_id'] ) || !isset( $option['app_secret'] ) || !isset( $source_id ) ) {
            // return false;
        // }

		//phong.nguyen 20150429 removed this 
        // // no app id or app secret  
        // if ( empty( $option['app_id'] ) || empty( $option['app_secret'] ) ) {  
            // return false;
        // }  

        // // no group id
        // if ( empty( $source_id ) ) {
            // return false;
        // }

        return $option;
    }

    /**
     * Do a historical or paginated import
     *
     * This is a clever approach to import all the posts from a group.
     * When you visit the url http://example.com/?fb2wp_hist, it'll start it's process.
     *
     * The plugin will start from the recent to next page without any interaction from
     * your end. It'll build the url and reload the page in every 5/10 seconds and impport
     * the next posts.
     *
     * As it doesn't do any blocking in the server, your server will not be overloaded
     * and any timeout wouldn't happen.
     *
     * @return void
     */
    function historical_import() {

        if ( ! isset( $_GET['fb2wp_hist'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $root_page    = add_query_arg( array( 'fb2wp_hist' => '' ), home_url() );
        $page_num     = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
		
		
		//check existing post_tag 
		$arr_post_tag = array(); 
		$arr_post_tag['page'] = array(
			'name' => 'Page', 
			'description' => 'Facebook Page', 
			'slug' => 'page', 
		); 
		$arr_post_tag['group'] = array(
			'name' => 'Group', 
			'description' => 'Facebook Group', 
			'slug' => 'group', 
		); 
		$str_taxonomy = 'post_tag';   // post_tag: Page/Post  
		foreach($arr_post_tag as $key => $value)
		{ 
			$str_term_id = $this->get_term($str_taxonomy, $value['slug'], $value['name'], $value['description'] ); // nguyễn asd01 => (auto transfer)  nguyen-asd01    
			// var_dump($str_term_id);     
		}
		
        $option_general       = $this->get_settings('fb2wp_general'); 
        // $option_page       = $this->get_settings('fb2wp_page'); 
        // $option_group       = $this->get_settings('fb2wp_group'); 
        $arrOptions = array();  
		$arrOptions['page'] = $this->get_settings('fb2wp_page'); 
		$arrOptions['group'] = $this->get_settings('fb2wp_group'); 
		
		foreach($arrOptions as $key => $option)
		{  
			$access_token = $this->get_access_token($option_general, $option);   
			// phong.nguyen 20150415 comment old code
			//$access_token = $option['app_id'] . '|' . $option['app_secret'];
			
			//phong.nguyen 20150416 get token, source ....
			$source_id    = $this->get_source_id($option, $key);  // $option['page_id']; // 
			$limit        = $this->get_limit($option_general, $option);   
			$comment_max  = $this->get_comment_max($option_general, $option);   
			 
			//phong.nguyen 20150429 get token, source 
			//add terms if not exist 
			
			//grant terms for current post. 
			var_dump('fetch...'. $key);  
			
			$this->fetch_facebook_data_history($key, $source_id, $limit, $access_token, $page_num, $comment_max);  
			break; return; 
		} 
		
		
        exit;
    }

    /**
     * get_term
     * Insert term by params(slug, name...)  if there's not existing. 
     *
     * @author phong.nguyen 20150430 
     * @param string $source_type 
     * @param string $source_id  
     */
    function get_term($str_taxonomy, $str_term_slug, $str_term_name, $str_term_desc=null) {  
		
		// $term = array(7, 8);  
		// $str_term_name = 'name...';  
		// $str_term_slug = 'cat-name03'; // architecture... decoy-portfolio  
		// $str_taxonomy = 'category'; 
		$objTerm = get_term_by('slug', $str_term_slug, $str_taxonomy);   
		$str_term_id = null;  
		if(isset($objTerm) & $objTerm!=false) 
		{
			//return termID... 
			$str_term_id = $objTerm->term_id; 
			// var_dump('old: '. $str_term_id); 
		} 
		else 
		{
			// create/insert term   
			$objTerm = wp_insert_term(
			  $str_term_name, // the term  
			  $str_taxonomy, // the taxonomy  
			  array( 
				'description'=> $str_term_desc,  
				'slug' => $str_term_slug,  
				'parent'=> null, // $parent_term_id    
			  )  
			); 
			
			// var_dump('new: '); 
			// var_dump($str_term_name); 
			// var_dump($objTerm); 
			// maybe ERROR: Cannot use object of type WP_Error as array...???  
			// 	+ 'empty_term_name'=>...
			// 	+ "A name is required for this term..."   
			$str_term_id = $objTerm['term_id']; 
		}  
		 
		
		// //update term 
		// $str_term_id = wp_update_term(1, $str_taxonomy, array(
			// 'name' => 'Cat Name...asd01',  
			// 'slug' => 'cat-name',  
		// ));  
		
		return $str_term_id; 
		
	}
    /**
     * fetch_facebook_data_history
     *
     * @author phong.nguyen 20150429 
     * @param string $source_type 
     * @param string $source_id 
     * @param string $limit  
     * @param string $access_token   
     * @param string $page_num    
     * @param string $comment_max    
     * @return none 
     */
    function fetch_facebook_data_history($source_type, $source_id, $limit, $access_token, $page_num, $comment_max) { 
		
		//fetch Facebook data... 
		$count = $this->fetch_facebook_data($source_type, $source_id, $limit, $access_token, $page_num, $comment_max); 
		 
		 
		// var_dump($source_id);   
		// var_dump($option_page);   
        // // show debug info
        printf( '<strong>%d</strong> posts imported<br>', $count );
        printf( 'Showing Page: %d<br>', $page_num );
        printf( 'Per Page: %d<br>', $limit );
        printf( 'Group/Page ID: %s<br>', $source_id );

        // Build the next page URL
        // Reload the page automatically after few seconds
        // and do it's thing without killing the server
		if(isset($decoded) ) // phong.nguyen 20150417 fixed bug != null  
		{
			if ( $page_num && property_exists( $decoded, 'paging' ) ) {

				$paging = $decoded->paging;
				parse_str( $paging->next, $next_page );

				$next_page_url = add_query_arg( array(
					'page'         => ($page_num + 1),
					'until'        => $next_page['until'],
					'paging_token' => $next_page['__paging_token']
				), $root_page );
			
			// // if($page_num >= 2) 
			// // {
				// // // var_dump($decoded);  
				// var_dump($fb_url);  
				// var_dump(admin_url().'asd01...');   
				// return; 
			// // }  
				?>
				<script type="text/javascript">
					setTimeout(function(){
						window.location.href = '<?php echo $next_page_url; ?>';  
						// window.open('<?php echo $next_page_url; ?>', '_blank');  
					}, 5000);
				</script>
				<?php
			} 
		} 
		
		return true;  
		
	} 
	
    /**
     * fetch_facebook_data
     *
     * @author phong.nguyen 20150429 
     * @param string $source_type 
     * @param string $source_id 
     * @param string $limit  
     * @param string $access_token   
     * @param string $page_num    
     * @param string $comment_max    
     * @return none 
     */
    function fetch_facebook_data($source_type, $source_id, $limit, $access_token, $page_num, $comment_max) {
		
        // Hardcode.20150401 choose API Version 2.3   
        //$fb_url       = 'https://graph.facebook.com/' . $source_id . '/feed/?limit=' . $limit . '&access_token=' . $access_token;  
        $fb_url = 'https://graph.facebook.com/v2.3/' . $source_id . '/feed/?limit=' . $limit . '&access_token=' . $access_token;  
		//https://graph.facebook.com/v2.3/$page_id/feed/?limit=250&access_token=$API|$SecretKey

        // build the query URL for next page
        if ( $page_num > 1 ) {
            $until        = isset( $_GET['until'] ) ? $_GET['until'] : '';
            $paging_token = isset( $_GET['paging_token'] ) ? $_GET['paging_token'] : '';

            $fb_url = add_query_arg( array(
                'until'          => $until,
                '__paging_token' => $paging_token, 
            ), $fb_url );
        }

        // do the import
        $json_posts  = $this->fetch_stream( $fb_url );
        $decoded     = json_decode( $json_posts );
        $group_posts = $decoded->data; 
		
		// phong.nguyen 20150430: get page_name + page_id. Ex: truyen-thong-123456...  
		$fb_url = 'https://graph.facebook.com/v2.3/' . $source_id . '/?access_token=' . $access_token;  
        $json_posts  = $this->fetch_stream( $fb_url );
        $decoded     = json_decode( $json_posts );
        $page_group_info = $decoded;  
		
		//add category (by FB name + FB id) 
		$str_taxonomy = 'category'; 
		$str_category_slug = $page_group_info->name . '-'. (string)$page_group_info->id;  
		// // var_dump($str_category_slug);  
		$this->get_term($str_taxonomy, $str_category_slug, $page_group_info->name, $page_group_info->name . '; id = '. (string) $page_group_info->id  ); // nguyễn asd01 => (auto transfer)  nguyen-asd01    
		
		
		// var_dump($page_group_info);  
		// var_dump($fb_url);   
		// return;  
		
		
		//http://issues.egany.com/view.php?id=116 (Cho phép chọn get Comments / Post	)  ...  
		//paging inside: https://graph.facebook.com/v2.3/{_fb_post_id}/comments?access_token=... 
		//next id: $json_comments->paging->cursors->after
		//next link: $json_comments->paging->next 
		//exp.: https://graph.facebook.com/v2.3/183711941649962_885739388113877/comments?... 
		
		// (rotate to first one AAA!!!-ok, it's not like this )  =>  {"data": [ ]}  
		
		
		//phong.nguyen 20150419 collect more comments 
		//EGANY get comments with param "comment_max", update FB posts 
		if(isset($group_posts) && $comment_max=='unlimited')  
		{
			foreach($group_posts as $post) 
			{  
				$fb_post_id = $post->id; 
				$fb_url_comment = 'https://graph.facebook.com/v2.3/' . $fb_post_id . '/comments/?access_token=' .  $access_token;  
				// // $fb_url_comment = 'https://graph.facebook.com/v2.3/183711941649962_885739388113877/comments?access_token=' .  $access_token;   // hard-code 
				
				$arr_comment_data = array(); 
				$json_comments = $this->fetch_stream($fb_url_comment);   
				$decoded_comments     = json_decode( $json_comments );   
				$arr_comment_data = $decoded_comments->data; 
				// var_dump($fb_url_comment);  
				//collect more comments...  
				while(isset($decoded_comments->paging->next))  // maybe equal "NULL".. 
				{
					$fb_url_comment = $decoded_comments->paging->next;   
					$json_comments = $this->fetch_stream($fb_url_comment);  
					$decoded_comments     = json_decode( $json_comments );
					
					if(isset($decoded_comments))  
					{
						$arr_comment_data = array_merge($arr_comment_data, $decoded_comments->data); 
					}  
				}  
				
				//show testing result ... 
				$post->comments->data = $arr_comment_data; 
				// // var_dump($post->comments->data[0]->id );  
				// var_dump(count($post->comments->data ));  
				// return;  
				
			}  
		} 
		
        $count       = $this->insert_posts( $group_posts, $source_id, $source_type, $page_group_info ); 

		return $count; 
	}
	
	
    /**
     * get_comment_max
     *
     * @author phong.nguyen 20150429 
     * @param array $option_general 
     * @param array $option_page_or_group 
     * @return string 
     */
    function get_comment_max($option_general, $option_page_or_group) { 
		
		$str_max_general =  $option_general['comment_max']; 
		$str_max_page_or_group =  $option_page_or_group['comment_max']; 
		
		return (trim($str_max_page_or_group)!='' & isset($str_max_page_or_group)) ? $str_max_page_or_group : $str_max_general;  
	}
	
    /**
     * get_limit
     *
     * @author phong.nguyen 20150429 
     * @param array $option_general 
     * @param array $option_page_or_group 
     * @return integer  
     */
    function get_limit($option_general, $option_page_or_group) { 
		
		$str_limit_general = isset( $option_general['limit'] ) ? intval( $option_general['limit'] ) : 30; 
		$str_limit_page_or_group = isset( $option_page_or_group['limit'] ) ? intval( $option_page_or_group['limit'] ) : 30;  
		
		return ($str_limit_page_or_group != 0) ? $str_limit_page_or_group : $str_limit_general;   
	}
    /**
     * get_access_token
     *
     * @param array $option_general 
     * @param array $option_page_or_group 
     * @return id string 
     */
    function get_access_token($option_general, $option_page_or_group) { 
		$str_token_general = ''; 
		$str_token_page_or_group = ''; 
		
		// get General token 
		if($option_general['access_type'] == 'fb_app')
		{
			$str_token_general =  $option_general['app_id'] . '|' . $option_general['app_secret'];
		}
		else if($option_general['access_type'] == 'fb_access_token') 
			$str_token_general = $option_general['access_token'];   
			
		// get group/page token  	
		if($option_page_or_group['access_type'] == 'fb_app')
		{
			$str_token_page_or_group =  $option_page_or_group['app_id'] . '|' . $option_page_or_group['app_secret'];
		}
		else if($option_page_or_group['access_type'] == 'fb_access_token') 
			$str_token_page_or_group = $option_page_or_group['access_token'];   
		 
		return (trim($str_token_page_or_group)!='' & isset($str_token_page_or_group)) ? $str_token_page_or_group : $str_token_general;  
		
	}
	
    /**
     * get_source_id
     *
     * @author phong.nguyen 20150429 
     * @param array $option 
     * @param array $source_type 
     * @return id string 
     */
    function get_source_id($option, $source_type) {
		$page_id     = $option['page_id'];
		$group_id     = $option['group_id'];
		
		if($source_type == 'page') // $option['source_type'] == st_page
		{
			return $page_id;  
		}
		else 
			return $group_id;   
	}
    /**
     * Do the actual import via cron
     *
     * @return boolean
     */
    function do_import() {
        $count = 0; 
		
		//get options for fetching data... 
        $option_general       = $this->get_settings('fb2wp_general'); 
        // $option_page       = $this->get_settings('fb2wp_page'); 
        // $option_group       = $this->get_settings('fb2wp_group'); 
        $arrOptions = array();  
		$arrOptions['page'] = $this->get_settings('fb2wp_page'); 
		$arrOptions['group'] = $this->get_settings('fb2wp_group'); 
		
		foreach($arrOptions as $key => $option)
		{  
			$access_token = $this->get_access_token($option_general, $option);   
			// phong.nguyen 20150415 comment old code
			//$access_token = $option['app_id'] . '|' . $option['app_secret'];
			
			//phong.nguyen 20150416 get token, source ....
			$source_id    = $this->get_source_id($option, $key);  // $option['page_id']; // 
			$limit        = $this->get_limit($option_general, $option);   
			$comment_max  = $this->get_comment_max($option_general, $option);   
			 
			//phong.nguyen 20150429 get token, source 
			// var_dump('fetch...'. $key);  
			$count += $this->fetch_facebook_data($key, $source_id, $limit, $access_token, $page_num, $comment_max);   
			// break; return; 
		} 
		
        // $count       = $this->insert_posts( $group_posts, $source_id );

        printf( '%d posts imported', $count );
    }

    /**
     * Fetch posts from facebook API
     *
     * @param  string $url
     * @return string
     */
    function fetch_stream( $url ) {
        self::log( 'debug', 'Fetching data from facebook' );

        $request = wp_remote_get( $url );
        $json_posts = wp_remote_retrieve_body( $request );

        if ( is_wp_error( $request ) ) {
            self::log( 'error', 'Fetching failed with code. WP_Error' );
            return;
        }

        if ( $request['response']['code'] != 200 ) {
            self::log( 'error', 'Fetching failed with code: ' . $request['response']['code'] );
            return false;
        }

        return $json_posts;
    }

    /**
     * Loop through the facebook feed and insert them
     *
     * @param array $group_posts
     * @param string $source_id 
     * @param string $source_type  (page/post) 
     * @param string $page_group_info (id, name; founder)
     * @return int
     */
    function insert_posts( $group_posts, $source_id, $source_type, $page_group_info ) {
        $count = 0;

        if ( $group_posts ) {
            foreach ($group_posts as $fb_post) {
				 
                $post_id = $this->insert_post( $fb_post, $source_id, $source_type, $page_group_info );
                if ( $post_id ) { 
					
					//phong.nguyen 20150420: do inserting comments...  
					//http://issues.egany.com/view.php?id=118 (Kiểm tra có update mới	... ) 
					$post = get_post($post_id);  
					$_fb_updated_time_OLD = get_post_meta($post_id, '_fb_updated_time', true);  
					// var_dump('post_id '. $post_id. ' _fb_updated_time_OLD'. $_fb_updated_time_OLD ); 
					
					if( ($_fb_updated_time_OLD != $fb_post->updated_time ) 
					| ($post->comment_count != count($fb_post->comments->data)) )  
					{  	
						if ( property_exists( $fb_post, 'comments' ) ) {
							$comment_count = $this->insert_comments( $post_id, $fb_post->comments->data);  
						}
					}

                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Insert comments for a post
     *
     * @param  int $post_id
     * @param  array $comments
     * @return int
     */
    function insert_comments( $post_id, $comments ) {
        $count = 0;

        if ( $comments ) {
            foreach ($comments as $comment) {
                $comment_id = $this->insert_comment( $post_id, $comment );

                if ( $comment_id ) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Check if the post already exists
     *
     * Checks via guid. guid = fb post link
     *
     * @global object $wpdb
     * @param string $fb_link_id facebook post link
     * @return boolean
     */
    function is_post_exists( $fb_link_id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $fb_link_id ) );

        if ( $row ) {
            return $row->ID;
        }

        return false;
    }

    /**
     * Check if a comment already exists
     *
     * Checks via meta key in comment
     *
     * @global object $wpdb
     * @param string $post_id //phong.nguyen 20150419 
     * @param string $fb_comment_id facebook comment id
     * @return boolean
     */
    function is_comment_exists($post_id, $fb_comment_id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT meta_id FROM $wpdb->commentmeta cme  WHERE meta_key = '_fb_comment_id' AND meta_value = %s", $fb_comment_id ) ); // // the old one: 
		

        if ( $row ) {
            return true;
        }

        return false;
    }

    /**
     * set_category_for_post... 
     *
     * @author phong.nguyen 20150430  
     * @param string $post_id 
     * @param string $str_category_slug 
     * @param string $str_tag_slug 
     * @return none
     */
    function set_category_for_post($post_id, $str_category_slug, $str_tag_slug){
		//set TAG for current post...  
		$str_taxonomy = 'post_tag';     
		$str_term_id = $this->get_term($str_taxonomy, $str_tag_slug, $str_tag_slug, $str_tag_slug ); // nguyễn asd01 => (auto transfer)  nguyen-asd01    
		// // var_dump('wp_set_post_terms...');     
		// // var_dump($str_term_id);     
		wp_set_post_terms( $post_id, array($str_term_id), $str_taxonomy ); // okok- 'page'   
		
		// // set att. for current post: category (truyen-thong-123456...)   
		$str_taxonomy = 'category';   
		$str_term_id = $this->get_term($str_taxonomy, $str_category_slug, $str_category_slug, $str_category_slug ); // nguyễn asd01 => (auto transfer)  nguyen-asd01   
		wp_set_post_terms( $post_id, array($str_term_id), $str_taxonomy );  
	}
    /**
     * Insert a new imported post from facebook
     *
     * @param object $fb_post
     * @param string $source_id
     * @param string $source_type
     * @param string $page_group_info
     * @return int|WP_Error
     */
    function insert_post( $fb_post, $source_id, $source_type, $page_group_info ) {  

        $option = get_option( 'fb2wp_general', array(
            'post_status'    => 'publish',
            'comment_status' => 'open'
        ) );
		
        $meta = array(
            '_fb_author_id'   => $fb_post->from->id,
            '_fb_author_name' => $fb_post->from->name,
            '_fb_link'        => $fb_post->actions[0]->link,
            '_fb_group_id'    => $source_id,
            '_fb_post_id'     => $fb_post->id, 
            '_fb_updated_time'     => $fb_post->updated_time, 
        );
		
		// phong.nguyen 20150419: find out an existing _fb_post_id 
		$args1 = array(
			'post_type'      => 'egany_fb2wp_post',
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'), 

			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'		=>	'post_date',
			'order'			=>	'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_fb_post_id',
					'value'   => $fb_post->id, // like as 147975405395810_358780530981962  
					'compare' => '='  //  LIKE 
				)
			),
		); 
		
		//unified any results
		$posts = array_unique( array_merge(  get_posts( $args1 ) ) );   
		
		// check any matching results. 
		if(count($posts) > 0)  
		{
			// EGNAY 20150420: update post if matching conditions 
			// //http://issues.egany.com/view.php?id=118 (Kiểm tra có update mới	... ) 
			$post_id = $posts[0]; 
			$_fb_updated_time_OLD = get_post_meta($post_id, '_fb_updated_time', true);  
			if($_fb_updated_time_OLD != $fb_post->updated_time )  // can NOT use "post_modified"  and "post_date_gmt"
			{
				$postarr = $this->get_post_data_from_fb_post($fb_post, $option);  
				$postarr['ID'] = $post_id; 
				wp_update_post( $postarr ); 
				
				foreach ($meta as $key => $value) {
					update_post_meta( $post_id, $key, $value );
				}  
			}
			
			// phong.nguyen 20150430: before return, set att. for current post: post_tag (Page/Post)  + category(truyen-thong-123456) 
			$str_category_slug = $page_group_info->name . '-'. (string)$page_group_info->id;  
			$this->set_category_for_post($post_id, $str_category_slug, $source_type);   
			
			return $post_id; 
		}  
		
        // // bail out if the post already exists .  phong.nguyen 20150419, removed; it caused some missing 
        // if ( $post_id = $this->is_post_exists( $fb_post->actions[0]->link ) ) {
            // return $post_id;
        // }
		
		
		$postarr = $this->get_post_data_from_fb_post($fb_post, $option); 
        
        $post_id = wp_insert_post( $postarr );

        if ( $post_id && !is_wp_error( $post_id ) ) {

            if ( $fb_post->type !== 'status' ) {
                set_post_format( $post_id, $fb_post->type );
            }

            foreach ($meta as $key => $value) {
                update_post_meta( $post_id, $key, $value );
            }
        }
		
		// phong.nguyen 20150430: before return, set att. for current post: post_tag (Page/Post)  + category(truyen-thong-123456)  
		$str_category_slug = $page_group_info->name . '-'. (string)$page_group_info->id;  
		$this->set_category_for_post($post_id, $str_category_slug, $source_type);   
		
        return $post_id;
    }

    /**
     * get_post_data_from_fb_post
     *
     * @author phong.nguyen 20150420 
     * @param  stdClass $fb_post Facebook data 
     * @param  array $option 
     * @return array post-data 
     */
    function get_post_data_from_fb_post($fb_post, $option)
	{ 
		$postarr = array(); 
	
        $postarr = array(
            'post_type'      => $this->post_type,
            'post_status'    => $option['post_status'],
            'comment_status' => isset( $option['comment_status'] ) ? $option['comment_status'] : 'open',
            'ping_status'    => isset( $option['comment_status'] ) ? $option['comment_status'] : 'open',
            'post_author'    => 1,
            'post_date'      => gmdate( 'Y-m-d H:i:s', ( strtotime( $fb_post->created_time ) ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
            'post_date_gmt'      => gmdate( 'Y-m-d H:i:s', ( strtotime( $fb_post->updated_time ) ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ), // can NOT use "post_modified" and "post_date_gmt"
            'guid'           => $fb_post->actions[0]->link
        );
		 
		switch ($fb_post->type) {
            case 'status':
                $postarr['post_title']   = wp_trim_words( strip_tags( $fb_post->message ), 10, '...' );
                $postarr['post_content'] = $fb_post->message;
                break;

            case 'photo':

                if ( !isset( $fb_post->message ) ) {
                    $postarr['post_title']   = wp_trim_words( strip_tags( $fb_post->story ), 10, '...' );
                    $postarr['post_content'] = sprintf( '<p>%1$s</p> <div class="image-wrap"><img src="%2$s" alt="%1$s" /></div>', $fb_post->story, $fb_post->picture );
                } else {
                    $postarr['post_title']   = wp_trim_words( strip_tags( $fb_post->message ), 10, '...' );
                    $postarr['post_content'] = sprintf( '<p>%1$s</p> <div class="image-wrap"><img src="%2$s" alt="%1$s" /></div>', $fb_post->message, $fb_post->picture );
                }

                break;

            case 'link':
                parse_str( $fb_post->picture, $parsed_link );

                $postarr['post_title'] = wp_trim_words( strip_tags( $fb_post->message ), 10, '...' );
                $postarr['post_content'] = '<p>' . $fb_post->message . '</p>';

                if ( !empty( $parsed_link['url']) ) {
                    $postarr['post_content'] .= sprintf( '<a href="%s"><img src="%s"></a>', $fb_post->link, $parsed_link['url'] );
                } else {
                    $postarr['post_content'] .= sprintf( '<a href="%s">%s</a>', $fb_post->link, $fb_post->name );
                }

                break;

            default:
                # code...
                break;
        }
		
		return $postarr; 
	}
	
    /**
     * Insert a comment in a post
     *
     * @param  int $post_id
     * @param  stdClass $fb_comment
     * @return void
     */
    function insert_comment( $post_id, $fb_comment ) {

		// //phong.nguyen 20150419 removed it... no need!!! 
        // // bail out if the comment already exists
        if ( $this->is_comment_exists($post_id, $fb_comment->id ) ) {
            return;
        }

        $commentarr = array(
            'comment_post_ID'    => $post_id,
            'comment_author'     => $fb_comment->from->name,
            'comment_author_url' => 'https://facebook.com/' . $fb_comment->from->id,
            'comment_content'    => $fb_comment->message,
            'comment_date'       => gmdate( 'Y-m-d H:i:s', ( strtotime( $fb_comment->created_time ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ),
            'comment_approved'   => 1,
            'comment_type'       => 'egany_fb2wp_post'
        );

        $meta = array(
            '_fb_author_id'   => $fb_comment->from->id,
            '_fb_comment_id'  => $fb_comment->id
        );

        $comment_id = wp_insert_comment( $commentarr );

        if ( $comment_id && !is_wp_error( $comment_id ) ) {
            foreach ($meta as $key => $value) {
                update_comment_meta( $comment_id, $key, $value );
            }
        }

        self::log( 'debug', 'comment is being inserted with FBID '.$fb_comment->id);

        return $comment_id;
    }

    /**
     * Trash all imported posts
     *
     * @return void
     */
    function trash_all() {
        $query = new WP_Query( array( 'post_type' => $this->post_type, 'posts_per_page' => -1 ) );

        if ( $query->have_posts()) {
            $all_posts = $query->get_posts();

            foreach ($all_posts as $post) {
                wp_delete_post( $post->ID, true );
            }
        }
    }

    /**
     * Adds author, post and group link to the end of the post
     *
     * @global object $post
     * @param string $content
     * @return string
     */
    function the_content( $content ) {
        global $post;

        if ( $post->post_type == $this->post_type ) {
            $author_id   = get_post_meta( $post->ID, '_fb_author_id', true );
            $author_name = get_post_meta( $post->ID, '_fb_author_name', true );
            $link        = get_post_meta( $post->ID, '_fb_link', true );
            $source_id    = get_post_meta( $post->ID, '_fb_group_id', true );

			//phong.nguyen 20150416: corrected "id=%d"  =>  "id=%s"  
            $author_link = sprintf( '<a href="https://facebook.com/profile.php?id=%s" target="_blank">%s</a>', $author_id, $author_name );
			
			$source_link = 'https://facebook.com';  
			$option = get_option( 'fb2wp_general', array() );
			if($option['source_type'] == 'st_page')
			{
				//EGANY removed "/pages"  OKOK!!!
				$source_link = sprintf( '<a href="https://facebook.com/%s" target="_blank">%s</a>', $source_id, __( 'View Page', 'EGANY' ) );  
			}
			else // if($option['source_type'] == 'st_group') 
			{ 
				//EGANY removed "/groups"  OKOK!!! 
				$source_link = sprintf( '<a href="https://facebook.com/%s" target="_blank">%s</a>', $source_id, __( 'View Group', 'EGANY' ) ); 
			}
			
            $custom_data = '<div class="fb-group-meta">';
            $custom_data .= sprintf( __( 'Posted by %s', 'EGANY' ), $author_link );
            $custom_data .= '<span class="sep"> | </span>';
            $custom_data .= sprintf( '<a href="%s" target="_blank">%s</a>', $link, __( 'View Post', 'EGANY' ) );
            $custom_data .= '<span class="sep"> | </span>';
            $custom_data .= $source_link; 
            $custom_data .= '</div>';

            $custom_data = apply_filters( 'egany_fb2wp_content', $custom_data, $post, $author_id, $author_name, $link, $source_id );

            $content .= $custom_data;
        }

        return $content;
    }

    /**
     * Add support for avatar in egany_fb2wp_post comment type
     *
     * @param  array $types
     * @return array
     */
    function avatar_comment_type( $types ) {
        $types[] = 'egany_fb2wp_post';

        return $types;
    }

    /**
     * Adds avatar image from facebook in comments
     *
     * @param  string $avatar
     * @param  string $id_or_email
     * @param  int $size
     * @return string
     */
    function get_avatar( $avatar, $id_or_email, $size ) {

        // it's not a comment
        if ( ! is_object( $id_or_email ) ) {
            return $avatar;
        }

        if ( empty( $id_or_email->comment_type ) || $id_or_email->comment_type != 'egany_fb2wp_post' ) {
            return $avatar;
        }

        $profile_id = get_comment_meta( $id_or_email->comment_ID, '_fb_author_id', true );
// var_dump(profile_id);  
// var_dump($profile_id); //ok Dien: 761009407251931 ; Cuong: 4886988508121 
        if ( ! $profile_id ) {
            return $avatar;
        }

        $image  = sprintf( 'http://graph.facebook.com/%1$s/picture?type=square&height=%2$s&width=%2$s', $profile_id, $size );
		
        $avatar = sprintf( '<img src="%1$s" class="avatar avatar-44 photo avatar-default" height="%2$s" width="%2$s" />', $image, $size );

        return $avatar;
    }

    /**
     * The main logging function
     *
     * @uses error_log
     * @param string $type type of the error. e.g: debug, error, info
     * @param string $msg
     */
    public static function log( $type = '', $msg = '' ) {
        if ( WP_DEBUG == true ) {
            $msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg );
            error_log( $msg, 3, dirname( __FILE__ ) . '/debug.log' );
        }
    }
	
	
	/**
	 * Register any scripts, styles 
	 *
	 * phong.nguyen 20150416 
	 * @return void
	 */
	public function register_scripts_styles_admin( ) {
		// // wp_enqueue_script('jquery');
		
		// //Load ADMIN scripts, styles.
		// // add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		// wp_enqueue_style( 'admin-style-fbwp', plugins_url('/assets/css/style_admin.css' , __FILE__ )); // ok
		wp_enqueue_script( 'admin-script-fbwp', plugins_url('/assets/js/fbwp_admin.js' , __FILE__ ), array( 'jquery' )); // ok
	}
	
	
	// /**
	 // * Register any scripts, styles at admin section 
	 // *
	 // * phong.nguyen 20150416 
	 // * @return void
	 // */
	// public function register_scripts_admin_head( ) {
	
		// //it's OK!!! 
		// //register common functions at <head> 
		// include_once('assets/js/fbwp_admin_head.php');  
	// }

} // Egany_FB_Group_To_WP

$wp_fb_import = Egany_FB_Group_To_WP::init();


