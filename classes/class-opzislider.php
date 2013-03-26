<?php
/*
 * Plugin Name: OpziSlider
 * Version: 0.1
 * Plugin URI: http://opzi.net/wordpress/plugins/OpziSlider
 * Author: Jeremy Madea
 * Author URI: http://madea.net/
 * Description: A slider plugin based on flexslider. 
 */  

global $wp_version;  

if ( version_compare( $wp_version, "2.6", "<" ))  
    exit( 'OpziSlider requires WordPress 2.6 or newer.' );  


/* Add featured image support to current theme if it isn't already supported. */
if ( ! current_theme_supports( 'post-thumbnails' ) ) { 
    add_theme_support( 'post-thumbnails' ); 
}

class OpziSlider  
{
    public $plugin_file; 
    public $plugin_url;
    public $plugin_dir; 
    public $options_name = 'OpziSlider_options'; 
    public $default_options = array(
            'namespace' => "flex-",       // String
            'selector' => ".slides > li", // String (selector)
            'animation' => "fade",        // String
            'easing' => "swing",          // String
            'direction' => "horizontal",  // String
            'reverse' => false,           // Boolean
            'animationLoop' => true,      // Boolean
            'smoothHeight' => false,      // Boolean
            'startAt' => 0,               // Integer
            'slideshow' => true,          // Boolean
            'slideshowSpeed' => 7000,     // Integer
            'animationSpeed' => 600,      // Integer
            'initDelay' => 0,             // Integer
            'randomize' => false,         // Boolean
        // Usability features
            'pauseOnAction' => true,      // Boolean
            'pauseOnHover' => false,      // Boolean
            'useCSS' => true,             // Boolean
            'touch' => true,              // Boolean
            'video' => false,             // Boolean
        // Primary Controls
            'controlNav' => true,         // Boolean
            'directionNav' => true,       // Boolean
            'prevText' => "Previous",     // String
            'nextText' => "Next",         // String
        // Secondary Navigation
            'keyboard' => true,           // Boolean
            'multipleKeyboard' => false,  // Boolean
            // 'mousewheel' => false,     // Boolean UNSUPPORTED BY THIS PLUGIN.
            'pausePlay' => false,         // Boolean
            'pauseText' => 'Pause',       // String
            'playText' => 'Play',         // String
        // Special properties
            'controlsContainer' => "",    // String (class selector)
            'manualControls' => "",       // String (selector)
            'sync' => "",                 // String (selector)
            'asNavFor' => "",             // String (selector)
        // Carousel Options
            'itemWidth' => 0,             // Integer
            'itemMargin' => 0,            // Integer
            'minItems' => 0,              // Integer
            'maxItems' => 0,              // Integer
            'move' => 0,                  // Integer
        );

    public function __construct( $file )  
    {   
        $this->plugin_file = $file;  
        $this->plugin_url = plugin_dir_url( $file ); 
        $this->plugin_dir = plugin_dir_path( $file );

        register_activation_hook( $file, array( &$this,  'on_activation' ));

        add_action( 'init',       array( &$this, 'create_post_type' ));
        add_action( 'init',       array( &$this, 'create_taxonomy'  )); 
        add_action( 'wp_head',    array( &$this, 'js_init_script'   ));
        add_action( 'admin_menu', array( &$this, 'on_admin_menu' ));
        add_action( 'admin_init', array( &$this, 'on_admin_init' ));
    }

    public function on_activation()  
    {
        $this->set_options();
    }

    public function create_post_type() 
    {
        register_post_type( 'opzi_slide',
            array(
                'labels' => array(
                    'name'               => 'OpziSlider Slides',
                    'singular_name'      => 'Slide',
                    'all_items'          => 'All Slides',
                    'add_new'            => 'Add New Slide',
                    'add_new_item'       => 'Add New Slide',
                    'edit'               => 'Edit',
                    'edit_item'          => 'Edit Slide',
                    'new_item'           => 'New Slide',
                    'view'               => 'View',
                    'view_item'          => 'View Slide',
                    'search_items'       => 'Search Slides',
                    'not_found'          => 'No Slides found',
                    'not_found_in_trash' => 'No Slides found in Trash',
                ),
                'public'              => true,
                'publicly_queryable'  => false, 
                'exclude_from_search' => true,
                'menu_position'       => 20,
                'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
                'taxonomies'          => array( '' ),
                'show_in_nav_menus'   => false,
            )
        );
    }

    public function create_taxonomy() { 
        register_taxonomy( 'opzi_slide_group', array( 'opzi_slide' ), 
            array( 
                labels => array(
                    'name'                => 'Slide Groups', 
                    'singular_name'       => 'Slide Group', 
                    'search_items'        => 'Search Slide Groups', 
                    'all_items'           => 'All Slide Groups', 
                    'parent_item'         => 'Parent Slide Group', 
                    'parent_item_colon'   => 'Parent Slide Group:', 
                    'edit_item'           => 'Edit Slide Group', 
                    'update_item'         => 'Update Slide Group', 
                    'add_new_item'        => 'Add Slide Group', 
                    'new_item_name'       => 'New Slide Group Name',
                    'menu_name'           => 'Slide Groups',
                ), 
                'show_ui'           => true, 
                'hiearchical'       => true, 
                'show_admin_column' => true, 
                'show_in_nav_menus' => false,
            )
        ); 
    }

    public function get_group( $group = '' ) 
    { 
        return get_posts( array( 'post_type' => 'opzi_slide', 'opzi_slide_group' => $group ));
    }

    public function get_slides( $group = '', $custom_fields = array() ) 
    { 
        $posts = $this->get_group( $group ); 
        $slides = array(); 
        foreach ($posts as $slide_post) { 
            $slide = new stdClass(); 
            $slide->id      = $slide_post->ID; 
            $slide->img     = get_the_post_thumbnail( $slide->id ); 
            $slide->title   = get_the_title( $slide->id );  
            $slide->content = get_post_field( 'post_content', $slide->id );
            $slide->custom  = array();
            foreach ( $custom_fields as $field ) {
                $slide->custom[ $field ] = get_post_meta( $slide->id, $field, true ); 
            }
            $slides[] = $slide; 
        }
        return $slides; 
    }

    public function js_init_script() 
    {
        $options = $this->options(); 

        // json_encode() encodes assoc arrays and objects as JSON objects 
        // but it encodes empty (and indexed) arrays as JSON arrays. 
        // We need $options to encode to a JSON object even if it's empty.
        if ( empty( $options )) $options = new stdClass();

        echo '<script type="text/javascript" charset="utf-8">' . "\n"
           . '    jQuery(window).load(function() {'            . "\n"
           . '        jQuery(\'.flexslider\').flexslider('     . "\n"
           . json_encode( $options )                           . "\n"
           . '        );'                                      . "\n"
           . '    });'                                         . "\n"
           . '</script>'                                       . "\n";
    }

    public function options() 
    {
        return array_diff_assoc( get_option( $this->options_name, array()), $this->default_options );
    }

    public function set_options( $options = array() )
    {
        return update_option( $this->options_name, array_merge( $this->default_options, $options )); 
    }

    public function all_options() 
    {
        $options = get_option( $this->options_name );
        if ( empty( $options )) 
            $options = $this->default_options; 
        return $options;
    }

    public function on_admin_menu()
    {   // settings_page_opzislider_options_page
        add_options_page( 
            'Opzi Slider Settings', 
            'Opzi Slider', 
            'manage_options', 
            'opzislider_options_page', 
            array( &$this, 'options_page' )
        ); 
        add_settings_section(
            'opzislider_flexslider_options', 
            'Flexslider Options',
            array( &$this, 'options_page_section_flexslider' ),
            $this->options_name
        );
        foreach ($this->all_options() as $option => $value) {
            add_settings_field( 
                "opzislider_$option", 
                $option, 
                array( &$this, 'generate_field_html' ), 
                $this->options_name, 
                'opzislider_flexslider_options', 
                array( $option, $value )
            );
        } 
    }

    public function on_admin_init()
    {
        register_setting( $this->options_name, $this->options_name, array( $this, 'sanitize_options' ));
    }

    public function sanitize_options( $submitted_options ) 
    {
        $sanitized_options = array(); 
        foreach ( $this->default_options as $option => $value ) {
            if ( array_key_exists( $option, $submitted_options )) { 
                if ( is_bool( $value ) ) {
                    $sanitized_options[ $option ] = (bool)$submitted_options[ $option ]; 
                } elseif ( is_int( $value )) {
                    $sanitized_options[ $option ] = (int)$submitted_options[ $option ];
                } elseif ( is_string( $value )) { 
                    $sanitized_options[ $option ] = (string)$submitted_options[ $option ]; 
                }
            }
        }
        return $sanitized_options;
    }

    public function options_page() 
    { 
    ?>
    <div class="wrap"><?php screen_icon(); ?><h2>Opzi Slider</h2><form action="options.php" method="post">
        <?php settings_fields( $this->options_name ); ?> 
        <?php do_settings_sections( $this->options_name ); ?> 
        <br /><input name="Submit" type="submit" class="button button-primary" value="Save Changes" />
    </form></div>
    <?php 
    }

    public function options_page_section_flexslider() 
    { 
        //do_settings_fields( $this->options_name, 'opzislider_flexslider_options' ); 
    }

    public function generate_field_html( $args ) 
    {
        $option = $args[0]; 
        $value  = $args[1];
        if     ( is_bool(   $value )) $this->generate_boolean_field_html( $args );
        elseif ( is_int(    $value )) $this->generate_integer_field_html( $args );
        elseif ( is_string( $value )) $this->generate_string_field_html(  $args );
    }
    public function generate_boolean_field_html( $args ) 
    {
        $option = $args[0]; 
        $value  = $args[1];
        echo "<input type='radio' name='" 
           . $this->options_name . '[' . $option . "]' " 
           . checked($value, true, false)
           . " value='1' /> True<br />\n";
        echo "<input type='radio' name='" 
           . $this->options_name . '[' . $option . "]' " 
           . checked($value, false, false) 
           . " value='0' /> False\n";
    }
    public function generate_integer_field_html( $args ) 
    {
        $option = $args[0]; 
        $value  = $args[1];
        echo "<input type='text' name='" . $this->options_name . '[' . $option . "]' "
           . "value='" . $value . "' />\n";
    }
    public function generate_string_field_html( $args ) 
    {
        $option = $args[0]; 
        $value  = $args[1];
        echo "<input type='text' name='" . $this->options_name . '[' . $option . "]' "
           . "value='" . $value . "' />\n";
    }

}
 


?>
