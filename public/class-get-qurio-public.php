<?php
/**
* The public-facing functionality of the plugin.
*
* @link https://getqurio.com/
* @since 1.0.0
*
* @package Get_Qurio
* @subpackage Get_Qurio/public
*/
/**
* The public-facing functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the public-facing stylesheet and JavaScript.
*
* @package Get_Qurio
* @subpackage Get_Qurio/public
* @author AthensLive Media Solutions <hq@getqurio.com>
*/
class Get_Qurio_Public {
/**
* The ID of this plugin.
*
* @since 1.0.0
* @access private
* @var string $plugin_name The ID of this plugin.
*/
private $plugin_name;
/**
* The version of this plugin.
*
* @since 1.0.0
* @access private
* @var string $version The current version of this plugin.
*/
private $version;
/**
* Initialize the class and set its properties.
*
* @since 1.0.0
* @param string $plugin_name The name of the plugin.
* @param string $version The version of this plugin.
*/
public function __construct( $plugin_name, $version ) {
$this->plugin_name = $plugin_name;
$this->version = $version;
add_shortcode( 'qurio_embed_campaign', array($this,'qurio_embed_campaign_html' ));
add_action('wp_footer' , array($this,'get_qurio_campaign_form'));
add_filter('the_content', array($this,'insert_qurio_campaign_form'));
}
/**
* Register the stylesheets for the public-facing side of the site.
*
* @since 1.0.0
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
wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/get-qurio-public.css', array(), $this->version, 'all' );
}
/**
* Register the JavaScript for the public-facing side of the site.
*
* @since 1.0.0
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
wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/get-qurio-public.js', array( 'jquery' ), $this->version, false );
}

    public function qurio_embed_campaign_html( $atts ) {
        global $post;

        $current_post_id = is_singular() ? get_the_ID() : ( isset($post->ID) ? $post->ID : '' );
        $output = "";

        $atts = shortcode_atts( array(
        'id' => '',
        ), $atts, 'qurio_embed_campaign' );
        if (empty($atts['id'])) {
        $atts['id'] = $current_post_id;
        }
        $id = $atts['id'];
        $qurio_connect_campaign_id = get_post_meta( $id, 'get_qurio_campaign_id', true );
        $qurio_connect_campaign_style = get_post_meta( $id, 'qurio_connect_campaign_style', true );
        
        if($qurio_connect_campaign_style != 'inline'){
            return '';
        }

        if (get_post_type($id) == 'post' && !empty($qurio_connect_campaign_id)) {
        $preview_url = 'preview-wp';
         $iframe_src = GET_QURIO_APP_URL . '/' . $preview_url . '/?campaignId=' . $qurio_connect_campaign_id;
         $output = "<div class='ctt_pop_content'>
        <iframe src=\"" . esc_url($iframe_src) . "\" width=\"600\" height=\"400\" frameborder=\"0\" style='width: 100%; Max-width: 100%;'></iframe>
        </div>
        <div class='custom_iframe_url'><div>";
        }
    
        return $output;
}








    


public function get_qurio_campaign_form(){
          global $post;
          $output ='';
            $current_post_id = is_singular() ? get_the_ID() : ( isset($post->ID) ? $post->ID : '' );
            $qurio_connect_campaign_id = sanitize_text_field(get_post_meta( $current_post_id, 'get_qurio_campaign_id', true ));
            $qurio_connect_campaign_style = sanitize_text_field(get_post_meta( $current_post_id, 'qurio_connect_campaign_style', true ));
            if(!$qurio_connect_campaign_style) {
            $qurio_connect_campaign_style = 'inline';
            }
            $qurio_popup_delay_time = intval(get_post_meta($current_post_id, 'qurio_popup_delay_time', true));
            $style_css = '';
            if(!empty($qurio_popup_delay_time)){
                $style_css = 'display:none;';
            }
           $serialized_appearance = get_post_meta($current_post_id, 'qurio_campaign_appearance', true);
           $appearance = maybe_unserialize($serialized_appearance);   //get the post data on key
           $overlay = isset($appearance['overlay']) ? $appearance['overlay'] : '#000000';
           $overlay_opacity = isset($appearance['overlayOpacity']) ? $appearance['overlayOpacity'] : 50;
           list($r, $g, $b) = sscanf($overlay, "#%02x%02x%02x");

           $style_css .= "background-color: rgba($r, $g, $b, " . ($overlay_opacity / 100) . ");";
    
            if (get_post_type($current_post_id) == 'post') {
               
            if (!empty($qurio_connect_campaign_id) && $qurio_connect_campaign_style == 'popup') {
            $iframe_src = GET_QURIO_APP_URL . '/preview-wp?campaignId=' . $qurio_connect_campaign_id;
            $output.= "
            <div id='popup1' class='ctt_overlay' style= '" .$style_css. "' >
            <div class='ctt_popup'>
            <a class='close' href='javascript:void(0)'>&times;</a>
            <div class='ctt_pop_content'>
            <iframe src=\"" . esc_url($iframe_src) . "\" frameborder=\"0\"></iframe>
            </div>
            </div>
            </div>
            <div class='custom_iframe_url'><div>
            
            <script>
            jQuery(document).ready(function($) {
               
            var delay_time = " . ($qurio_popup_delay_time * 1000) . ";
            setTimeout(function() {
                jQuery('.ctt_overlay').show();
            }, delay_time);
            });
          </script>"; 
            }
            }
            echo $output;
    }


    
    public function get_qurio_campaign_form_inline_style() {
        global $post;
        $current_post_id = is_singular() ? get_the_ID() : (isset($post->ID) ? $post->ID : '');
        $qurio_connect_campaign_id = sanitize_text_field(get_post_meta($current_post_id, 'get_qurio_campaign_id', true));
        $qurio_connect_campaign_style = sanitize_text_field(get_post_meta($current_post_id, 'qurio_connect_campaign_style', true));
    
        if (!$qurio_connect_campaign_style) {
            $qurio_connect_campaign_style = 'inline';
        }

        if (has_shortcode($post->post_content, 'qurio_embed_campaign')) {
            return '';
        }

        if (get_post_type($current_post_id) == 'post') {
            if (!empty($qurio_connect_campaign_id) && $qurio_connect_campaign_style == 'inline') {
                $iframe_src = GET_QURIO_APP_URL . '/preview-wp?campaignId=' . $qurio_connect_campaign_id;
                $output = "<div id='inline_form_show'><div class='ctt_pop_content'>
                               <iframe src=\"" . esc_url($iframe_src) . "\" width=\"600\" height=\"400\" frameborder=\"0\" style='width: 100%; max-width: 100%;'></iframe>
                           </div>
                           <div class='custom_iframe_url'></div></div>";
                return $output;
            }
        }
        //return '';
    }

 
public function insert_qurio_campaign_form($content) {
    if (is_singular('post')) {
        $campaign_form = $this->get_qurio_campaign_form_inline_style();
        if ($campaign_form) {
            $pattern = '/(<\/p>.*?<\/p>)/s';
            $replacement = '$1' . $campaign_form;
            $content = preg_replace($pattern, $replacement, $content, 1);
        }
    }
    return $content;
}

    
}



