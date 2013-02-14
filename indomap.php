<?php
/*
Plugin Name: indomap
Plugin URI: http://wordpress.org/extend/plugins/indomap/
Description: Create Maps in Metabox Post with advanced features
Version: 1.0.1
Author: Minda Sari
Author URI: http://www.mindasari.wordpress.com/
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) )
	die( __("Can't load this file directly") );
	
class Indo_MAP
{
	function __construct() {
		$this->Indo_MAP();
	}
	
	function Indo_MAP() {
		add_action( 'init', array( &$this, 'init_Indo_MAP' ) );
	}
	
	function init_Indo_MAP() {
		add_shortcode( 'indo', array( &$this, 'function_indomap') );
		add_action( 'wp_print_scripts', array( &$this, 'equeue_Indo_MAP' ) );
		add_action( 'admin_init', array( &$this, 'admin_init_setting_Indo_MAP') );
		add_action( 'save_post', array( &$this, 'meta_save_setting_Indo_MAP') );
		
		add_filter( 'the_content', array( &$this, 'the_content_filter_Indo_MAP' ) );
		add_filter( 'get_the_content', array( &$this, 'the_content_filter_Indo_MAP' ) );
	}	
	
	function equeue_Indo_MAP() {
		// Google Maps API v3
		wp_deregister_script('googlemapsapi3');
		wp_enqueue_script('googlemapsapi3', 'http://maps.google.com/maps/api/js?sensor=false', false, '3', false); ;
		// gmap
		wp_deregister_script('gmap3');
		wp_enqueue_script('gmap3', plugins_url('/gmap3.min.js',__FILE__), array('jquery')); 
	}
	
	function function_indomap( $atts, $content=null ) {
		global $post;
		extract( shortcode_atts( array(
			'address' => '-7.536428700000001,110.23402829999998',
			'width' => '',
			'height' => '250',
			'data' => '',
			'zoom' => '13',
			'navigation' => true,
			'scroll' => true,
			'street' => 'false'
		), $atts ) );
		
		if(stripos('px', $height)==false || stripos('%', $height)==false ){
			$height = $height.'px';
		}
		if((stripos('px', $width)==false || stripos('%', $width)==false) && $width!=''){
			$width = $width.'px';
		}
		
		$data = str_ireplace("'","\'",$data.$address.'<br>'.get_the_post_thumbnail($post->ID, array(50,50)));
		if($width!='') $width = 'width:'.$width;
		$id = 'Indo_MAP-'.rand();
		$content = '
		<style>
		#'.$id.'{height:'.$height.';'.$width.'}
		</style>
		<div id="'.$id.'" class="gmap3"></div>
    		<script type="text/javascript">
				jQuery(function($){
					$("#'.$id.'").gmap3({
								  marker:{ 
										values:[{address: \''.$address.'\'}], 
										data:\''.$data.'\',
										events:{
										  click: function(marker, event, context){
											var map = $(this).gmap3("get"),
											  infowindow = $(this).gmap3({get:{name:"infowindow"}});
											if (infowindow){
											  infowindow.open(map, marker);
											  infowindow.setContent(context.data);
											} else {
											  $(this).gmap3({
												infowindow:{
												  anchor:marker, 
												  options:{content: context.data}
												}
											  });
											}
										  }
										}
								  },
								  map:{
										options:{
										  zoom: '.$zoom.',
										  mapTypeControl: true,
										  mapTypeControlOptions: {
											style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
										  },
										  navigationControl: '.$navigation.',
										  scrollwheel: '.$scroll.',
										  streetViewControl: '.$street.'
										}
								  }
					});								
				});
			</script>';
		return do_shortcode($content);		
	}	
	
	function admin_init_setting_Indo_MAP(){
		add_meta_box("add_setting_Indo_MAP", "MAP Address", array( &$this, "add_setting_Indo_MAP"), "post", "normal", "high");
		add_meta_box("add_setting_Indo_MAP", "MAP Address", array( &$this, "add_setting_Indo_MAP"), "page", "normal", "high");
	}
	
	function add_setting_Indo_MAP() {
		global $post;
		echo '<style type="text/css">
			.Indo_MAP_address{width:100%}
		</style>
		<div id="parent_Indo_MAP_address" class="parent_Indo_MAP_address">
			<label for="">'.__('Address:').'</label>
			<input type="text" id="Indo_MAP_address" class="Indo_MAP_address" name="Indo_MAP_address" value="'.get_post_meta($post->ID,'Indo_MAP_address',true).'" />
		</div><!-- #custom_setting_for_pages -->';
	}
	  
	function meta_save_setting_Indo_MAP(){
		global $post;
		update_post_meta($post->ID, "Indo_MAP_address", $_POST['Indo_MAP_address']);	  
	}
	
	function the_content_filter_Indo_MAP($content) {
		global $post;
		$address = get_post_meta($post->ID,'Indo_MAP_address',true);
		if(!empty($address) && $address != ''){
			$content .= do_shortcode('[indo address="'.$address.'"]');
		}
		return $content;
	}
	
}

$colabs_Indo_MAP = new Indo_MAP();

?>