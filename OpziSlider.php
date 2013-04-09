<?php
/*
 * Plugin Name: OpziSlider
 * Version: 0.1
 * Plugin URI: http://opzi.net/oss/opzislider
 * Author: Jeremy Madea
 * Author URI: http://madea.net/
 * Description: A slider plugin based on flexslider. 
 */  

if ( ! defined( 'ABSPATH' )) exit; 

global $wp_version;  

if ( version_compare( $wp_version, "2.6", "<" ))  
    exit( 'OpziSlider requires WordPress 2.6 or newer.' );  


/* Add featured image support to current theme if it isn't already supported. */
if ( ! current_theme_supports( 'post-thumbnails' )) { 
    add_theme_support( 'post-thumbnails' ); 
}

require_once( 'classes/class-opzislider.php' );

global $opzislider;
$opzislider = new OpziSlider( __FILE__ );

wp_enqueue_script( 'flexslider',       plugin_dir_url( __FILE__ ) . 'js/jquery.flexslider-min.js', array( 'jquery' ));
wp_register_style( 'flexslider-style', plugin_dir_url( __FILE__ ) . 'css/flexslider.css' );
wp_enqueue_style( 'flexslider-style' );

function OpziSlider_get_group( $group = '' ) { 
    return get_posts( array( 'post_type' => 'opzi_slide', 'opzi_slider_slide_group' => $group ));
}

function OpziSlider_slides( $group = '', $custom_fields = array() ) { 
    $posts = OpziSlider_get_group( $group ); 
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



?>
