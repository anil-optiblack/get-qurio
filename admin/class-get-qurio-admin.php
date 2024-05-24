<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://getqurio.com/
 * @since      1.0.0
 *
 * @package    Get_Qurio
 * @subpackage Get_Qurio/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Get_Qurio
 * @subpackage Get_Qurio/admin
 * @author     AthensLive Media Solutions <hq@getqurio.com>
 */
class Get_Qurio_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('rest_api_init', array($this, 'register_get_qurio_api'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Get_Qurio_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Get_Qurio_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/get-qurio-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Get_Qurio_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Get_Qurio_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/get-qurio-admin.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Register User, Login User and Company Register in post method.
     * @param $action str 'widget' add in url
     * @param $params array fields user login, register and company register  
     * @param $headers optional field 
     * 
     * @since    1.0.0
     * 
     * @return encoded json
     */

    public function get_qurio_send_post_request($action, $params= array(), $headers = array())
    {

        $action = sanitize_text_field($action);
        $gq_api_key = sanitize_text_field($this->get_qurio_api_key());
        $url = esc_url_raw( GET_QURIO_API_ROOT . '/' . $action );
        $site_url = get_site_url(); 
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $gq_api_key;
        $headers['quriowpdomain'] = $site_url;
        
        if(!empty($url) && is_array($params) && is_array($headers) && !empty($headers)) {

            $post_fields = array(
                'method'      => 'POST',
                'headers'     => $headers,
                'cookies'     => array(),
                'timeout'     => 60,
                'redirection' => 10,
                'httpversion' => '1.1',
            );

            if(is_array($params) && !empty($params)) {
                $post_fields['body'] = wp_json_encode($params);
            } else {
                $post_fields['body'] = '';
            }
            
            $response = wp_remote_post( $url, $post_fields );

            //get_qurio_write_log('response');
            //get_qurio_write_log($response);

            if (is_wp_error($response)) {
                $error_message = sanitize_text_field($response->get_error_message());
                return wp_kses_post(wp_json_encode(array('status' => 'error', 'code' => 400, 'api_res' => $error_message)));
            } else {
                // The API call was successful
                $body = wp_remote_retrieve_body( $response );
                return wp_kses_post(wp_json_encode(array('status' => 'success', 'code' => 200, 'api_res' => $body)));
            }
        } else {
            return wp_json_encode(array('status' => 'error', 'code' => 422, 'api_res' => 'Invalid API request'));
        }
        
    }

    /**
     * Fetch API key
     */
    public function get_qurio_api_key() {
        return esc_html(get_option('get_qurio_api_key', ''));
    }

    /**
     * Create plugin settings admin menu
     */
    public function get_qurio_create_menu() {
        add_menu_page('Qurio Settings', 'Qurio Settings', 'manage_options', 'get-qurio-settings', array($this,'get_qurio_save_api_key') ,'dashicons-admin-generic');
        add_submenu_page('get-qurio-settings', 'Quick Overview ', 'Quick Overview ', 'manage_options', 'quick-overview', array($this,'get_qurio_quick_overview'));
        add_action( 'admin_init', array($this, 'get_qurio_register_api_key_settings') );
    }
    
    /*
    * Function to add a settings link on the plugin page
    */
    public function qurio_add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=get-qurio-settings') . '">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Saving the api key value in database
     */
    public function get_qurio_register_api_key_settings() {
        
        register_setting( 'qurio_settings_group', 'get_qurio_api_key' );
        register_setting( 'qurio_settings_group', 'qurio_api_status' );

    }
    
        
    public function get_qurio_all_posts_send() {

        $qurio_log = '';
        $get_qurio_api_key = $this->get_qurio_api_key();

        if($get_qurio_api_key) {
        
            $action = 'wp/verifytoken';
            $status = '-';

            $response = $this->get_qurio_send_post_request($action);

            $qurio_log .= 'action = ' . $action;
            //get_qurio_write_log('res');
            //get_qurio_write_log($response);

            if(!$response) {
                return;
            }

            $response_data = json_decode( $response , true);

            //get_qurio_write_log('response_data');
            //get_qurio_write_log($response_data);        

            // Check for errors in the response
            if ((is_array($response_data) && !empty($response_data)) || json_last_error() !== JSON_ERROR_NONE) {

                if ($response_data['status'] == 'success') {
                    $res_json = $response_data['api_res'];
                    if(!$res_json) {
                        return;
                    }

                    $res_arr = json_decode($res_json, true);

                    if (isset($res_arr['status'])) {
                        $status = $res_arr['status'];                                          
                    } else {
                        $status = 'missing';
                    }                      
                } else {
                    $status = 'invalid';
                }

                update_option('qurio_api_status', $status);

                $qurio_log .= ', qurio_api_status = ' . $status;
                //get_qurio_write_log($qurio_log);
                
                if ($status == 'enabled') {
                    $action = 'wp/posts';
                    $get_all_post_data = $this->get_qurio_all_post_data();
                    $send_article = $this->get_qurio_send_post_request($action, $get_all_post_data);

                    $qurio_log2 = 'action = ' . $action;
                    //get_qurio_write_log($qurio_log2);
                    //get_qurio_write_log($get_all_post_data);
                }
            }

        }

    }

    public function get_qurio_save_api_key() {
        // Get settings values
        $get_qurio_api_key = get_option( 'get_qurio_api_key' );
        $qurio_api_status = get_option( 'qurio_api_status' );
        ?>
        <div class="wrap ctm_main_wrap">
        <h1>Qurio Settings</h1>
        <p>To get the Qurio API key, go to your Qurio account, click on the profile icon in the top right, and copy the API key from there.</p>
        <?php ?>
        <form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
            <?php settings_fields( 'qurio_settings_group' ); ?>
            <?php do_settings_sections( 'qurio_settings_group' ); ?>
            <table class="form-table ctm_form_table">
                <tr valign="top">
                <th scope="row"><label for="get_qurio_api_key">Qurio API key</label></th>
                <td>
                <input type="text" name="get_qurio_api_key" id="get_qurio_api_key" value="<?php echo esc_attr( $get_qurio_api_key ); ?>" />
                    </td>
                </tr>                
            </table>
            <?php
            ?>
            
            <?php submit_button('Save Changes'); ?>
        
        </form>

        <hr>

        <div class="qurio_widget_instructions">
            <h2>Instructions:</h2>
            <div class="">
                <p>Qurio Engagements Widgets has 3 display options for your Wordpress website.</p>
                <ol>
                    <li><strong>Inline. </strong>The inline widget will natively appear inside the article after the 2nd paragraph. You can set a custom widget position with the shortcode shown below. Just add it to the post where you would like to see the widget.<br>
                        <input type="text" readonly disabled id="qurio_shortcode_field" value="[qurio_embed_campaign]">
                        <button type="button" id="qurio_shortcode_copy">Copy Shortcode</button></li>
                    <li><strong>Popup. </strong>Popup widgets show automatically on page load after a certain delay. To configure the delay, go to the Appearances page in your Qurio account.</li>
                    <li><strong>Standalone. </strong>Every campaign has a unique Standalone URL. You can link it directly with your posts, emails, or social media.</li>
                </ol>
            </div>
        </div>
        
        <?php $this->get_qurio_all_posts_send(); ?>
        
        </div>
        <?php }
                    
        public function get_qurio_quick_overview() {

            $this->get_qurio_campaign_stats();

            $option_key = 'qurio_overview_campaign_data';
            $campaigns = maybe_unserialize( get_option($option_key ));
        
            if (!is_array($campaigns)) {
                $campaigns = array();
            }

            // Initialize a counter for the "published" status
            $active_campaign = 0;
            $total_campaign = 0;
            $inactive_campaign = 0;
            $responses_last_month = 0;

			//get_qurio_write_log('campaigns = ');
            //get_qurio_write_log($campaigns);

            if(is_array($campaigns) && !empty($campaigns)) {
                $total_campaign = count($campaigns);

                // Iterate through each campaign and increment the counter if the status is "published"
                foreach ($campaigns as $campaign) {
                    if ($campaign['status'] === 'published') {
                        $active_campaign++;
                        $responses_last_month += absint($campaign['formResponseCount']);
                    }
                }

                $inactive_campaign = $total_campaign - $active_campaign;
            }
            
            echo wp_kses_post(sprintf(__('<div class="wrap ctm_main_wrap ctm_overview_main">
            <h1>Quick Overview</h1>', 'get-qurio')));

            echo wp_kses_post(sprintf(__("<p>Here's a quick overview of how your campaigns have been doing. Log in to the app for a more detailed breakdown.</p>", 'get-qurio')));
     
            echo wp_kses_post(sprintf(__(' <div class="ctm_form_table ctm_form_overview">
                <div class="ctm_box_model">
                    <div class="ctm_box_type">
                        <h4>Active Campaigns</h4>', 'get-qurio')));
            echo wp_kses_post(sprintf(__(' <h5>%d</h5>
                        </div>', 'get-qurio'),  esc_attr($active_campaign)));

            echo wp_kses_post(sprintf(__(' <div class="ctm_box_type border_box_left">
                            <h4>Reponses gathered during the last 30 days</h4>', 'get-qurio') ));
                        
            echo wp_kses_post(sprintf(__(' <h5>%d</h5>
                        </div>
                    </div>', 'get-qurio'), esc_attr($responses_last_month)));
            echo wp_kses_post(sprintf(__('  <div class="ctm_over_view_button">
                    <a href="%s" target="_blank" class="button-primary">Go to Qurio</a>
                    </div>
                </div>
            </div>', 'get-qurio'), esc_url(GET_QURIO_APP_URL)));

        }

        public function get_qurio_all_post_data() {
            
            $args = array(
                'posts_per_page' => -1,
                 'post_status' => 'any',
                 'post_type' => 'post',
            );
        
            $posts_query = new WP_Query($args);
            $posts_details = array();

            if ( $posts_query->have_posts() ){
            while ($posts_query->have_posts()) {
                $posts_query->the_post();
				global $post;
				$post_arr = (array) $post;

				// Get the full URL of the post thumbnail
				$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

				// Get author name
				$post_author = $post_arr['post_author'];
				$author = get_user_by( 'id', $post_author );

				$post_arr['author_name'] = $author->display_name;
				$post_arr['post_image'] = $thumbnail_url;
				$posts_details[] = $post_arr;
            }
          }
            wp_reset_postdata(); 
        
            return $posts_details;
            
        }
        
        public function get_qurio_single_post_data($post_id) {
            $single_post = array(); 
            $post = get_post($post_id);
            $single_post = array(
                'post_object' => $post
            );
        
            return $single_post;
        }
        
        public function get_qurio_save_post_data($post_id, $post, $update) {

            // If this is a revision, don't run API.
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }

            // Exclude other post types, check for default posts only
            if ( 'post' !== get_post_type( $post_id ) ) {
                return;
            }
      
            // For single post data
            // $action = 'wp/post';
            // $single_post_data = $this->get_qurio_single_post_data($post_id);
            // get_qurio_write_log('single_post_data = ');
            // get_qurio_write_log($single_post_data);
            // $res = $this->get_qurio_send_post_request($action, $single_post_data);
            // get_qurio_write_log('single_post_data = ');
            // get_qurio_write_log($res);

            // For all posts data
            $action = 'wp/posts';
            $get_all_post_data = $this->get_qurio_all_post_data();
            $send_article = $this->get_qurio_send_post_request($action, $get_all_post_data);

            $qurio_log2 = 'action = ' . $action;
            //get_qurio_write_log($qurio_log2);
            //get_qurio_write_log($get_all_post_data);

        }
        
        public function qurio_post_column_connect( $columns ) {
            $columns['qurio_connect'] = 'Qurio';
            return $columns;
        }

        public function get_qurio_connect_status( $column, $post_id ) {
            if ( $column == 'qurio_connect' ) {
                $qurio_connect_campaign_id = get_post_meta( $post_id, 'get_qurio_campaign_id', true );
                $qurio_connect_campaign_style = get_post_meta( $post_id, 'qurio_connect_campaign_style', true );

                if ( !empty( $qurio_connect_campaign_id ) ) {
                    $campaign_url = GET_QURIO_APP_URL.'/campaign/view/'.$qurio_connect_campaign_id;
                    echo sprintf( __('<a href="%s" target="_blank">View Campaign</a>', 'get-qurio'), esc_url($campaign_url) );
                } else {
                    $campaign_url = GET_QURIO_APP_URL.'/campaign/new/step1?postId/'.$post_id;
                    echo sprintf( __('<a href="%s" target="_blank" class="ctm_qurio_btn">Create Campaign</a>', 'get-qurio'), esc_url($campaign_url) );
                }
            }
        }

        /***************API endpoint start */
        public function register_get_qurio_api() {
            register_rest_route( 'get-qurio/v1', '/post-campaign', array(
                'methods' => 'POST',
                'callback' => array($this, 'get_qurio_campaign_id'),
                'permission_callback' => '__return_true',
            ) );
        }

        public function get_qurio_campaign_id($data) {

            get_qurio_write_log('Payload = ');
            get_qurio_write_log($data);

       
            //get_qurio_write_log('$_POST = ');
            //get_qurio_write_log($_POST);
           
            $api_data = array();
            $auth = apache_request_headers();
            $site_url = get_site_url();       

            //get_qurio_write_log('Auth = ');
            //get_qurio_write_log($auth);
            
            $api_key = $this->get_qurio_api_key(); 
            $computed_auth_key = sha1($api_key .''. $site_url);
            if (true/*isset($auth['qurio-authorization']) && $auth['qurio-authorization'] === $computed_auth_key &&
            isset($auth['quriowpdomain']) && $auth['quriowpdomain'] === $site_url*/) {

            // Authorization successful
            $post_id = absint($data['post_id']);
            $campaign_id = sanitize_text_field($data['campaign_id']);
            $qurio_connect_campaign_style = sanitize_text_field($data['style']);
            $popupdelay = $data['appearance']['popupDelay'];
            $serialized_data = maybe_serialize($data['appearance']);
            $msg = 'No post id found';
            if($post_id > 0){
                update_post_meta($post_id, 'get_qurio_campaign_id', $campaign_id);
                update_post_meta($post_id, 'qurio_connect_campaign_style', $qurio_connect_campaign_style);
                update_post_meta($post_id, 'qurio_popup_delay_time', $popupdelay);
                update_post_meta($post_id, 'qurio_campaign_appearance', $serialized_data);
                $msg ='Campaign id saved';
            }

            $api_data = array(
                'post_id' => $post_id,
                'campaign_id' => $campaign_id,
                'msg' => $msg
            );
            $response = new WP_REST_Response($api_data);
            
            }else{
                $api_data = array(
                    'msg' => 'Invalid API request',
                );
                $response = new WP_REST_Response($api_data);
            }

           // get_qurio_write_log('api_data = ');
           // get_qurio_write_log($api_data);
            //get_qurio_write_log('Response = ');
           // get_qurio_write_log($response);

            return $response;
        }

        public function get_qurio_campaign_stats() {
            $action = 'wp/campaigns-stats';
            $gq_api_key = sanitize_text_field($this->get_qurio_api_key());

            $headers = array();
            $headers['Content-Type'] = 'application/json';
            $headers['Authorization'] = $gq_api_key;
            
            $url = esc_url_raw( GET_QURIO_API_ROOT . '/' . $action );
            $get_fields = array(
                'headers'     => $headers,
            );

            $response = $this->get_qurio_send_post_request($action);
       
            get_qurio_write_log('action = ' . $action);
           get_qurio_write_log('Campaign Response = ');
           get_qurio_write_log($response);

            $response_data = json_decode( $response , true);

            get_qurio_write_log('response_data');
            get_qurio_write_log($response_data);            

            // Check for errors in the response
            if ((is_array($response_data) && !empty($response_data)) || json_last_error() !== JSON_ERROR_NONE) {

                if ($response_data['status'] == 'success') {
                    $res_json = sanitize_textarea_field($response_data['api_res']);
                    if(!$res_json) {
                        return;
                    }

                    $res_arr = json_decode($res_json, true);
                    $option_key = 'qurio_overview_campaign_data';
                    $saved = update_option($option_key, maybe_serialize( $res_arr ));

                }
            }           

        }
       
}