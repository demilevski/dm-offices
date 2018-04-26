<?php
/*
Plugin Name: My Offices
Description: Bla bla bla
Version: 1.0
Author: Goran Milevski
Author URI: http://demilevski.com
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template')
		);


		// Add your templates to this array.
		$this->templates = array(
			'offices-template.php' => 'Listing offices',
		);

	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result)
		if ( is_search() ) {
			return $template;
		}

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		// Allows filtering of file path
		$filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

		$file =  $filepath . get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

// Our custom post type function
function create_posttype() {
 
    register_post_type( 'offices',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Offices' ),
                'singular_name' => __( 'Office' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'offices'),
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );


// Register Custom Taxonomy
function taxonomy_location() {

	$labels = array(
		'name'                       => _x( 'Locations', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Location', 'text_domain' ),
		'all_items'                  => __( 'All Items', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Item Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Item', 'text_domain' ),
		'edit_item'                  => __( 'Edit Item', 'text_domain' ),
		'update_item'                => __( 'Update Item', 'text_domain' ),
		'view_item'                  => __( 'View Item', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Items', 'text_domain' ),
		'search_items'               => __( 'Search Items', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No items', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                       => 'location',
		'with_front'                 => true,
		'hierarchical'               => true,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'location', array( 'offices' ), $args );

}
add_action( 'init', 'taxonomy_location', 0 );



// Register Custom Taxonomy
function taxonomy_branch() {

	$labels = array(
		'name'                       => _x( 'Branches', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Branch', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Branch', 'text_domain' ),
		'all_items'                  => __( 'All Items', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Item Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Item', 'text_domain' ),
		'edit_item'                  => __( 'Edit Item', 'text_domain' ),
		'update_item'                => __( 'Update Item', 'text_domain' ),
		'view_item'                  => __( 'View Item', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Items', 'text_domain' ),
		'search_items'               => __( 'Search Items', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No items', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                       => 'branch',
		'with_front'                 => true,
		'hierarchical'               => true,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'branch', array( 'offices' ), $args );

}
add_action( 'init', 'taxonomy_branch', 0 );

// include custom jQuery
function shapeSpace_include_custom_jquery() {

	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);

	wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
	wp_enqueue_script('prefix_bootstrap');

	wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
	wp_enqueue_style('prefix_bootstrap');

}
add_action('wp_enqueue_scripts', 'shapeSpace_include_custom_jquery');



//Enqueue Ajax Scripts
function enqueue_offices_ajax_scripts() {
    wp_register_script( 'offices-ajax-js', plugins_url( '/js/main.js', __FILE__ ));
    wp_localize_script( 'offices-ajax-js', 'ajax_offices_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_script( 'offices-ajax-js' );
}
add_action('wp_enqueue_scripts', 'enqueue_offices_ajax_scripts');

//Add Ajax Actions
add_action('wp_ajax_offices_filter', 'ajax_offices_filter');
add_action('wp_ajax_nopriv_offices_filter', 'ajax_offices_filter');


//Get Genre Filters
function get_location_filters()
{
    $terms = get_terms('location', $args = array('post_type'=>'offices'));
    $filters_html = false;
 
    if( $terms ):
        $filters_html = '<h3>Locations</h3>';
 
        foreach( $terms as $term )
        {
            $term_id = $term->term_id;
            $term_name = $term->name;
            $term_slug = $term->slug;
 
            $filters_html .= '<label for="'.$term_slug.'"><input type="checkbox" id="'.$term_slug.'" name="filter_genre[]" value="'.$term_id.'">'.$term_name.'</label>';
        }
        $filters_html .= '<hr>';
 
        return $filters_html;
    endif;
}

//Get Genre Filters
function get_branch_filters()
{
    $terms = get_terms('branch', $args = array('post_type'=>'offices'));
    $filters_html = false;
 
    if( $terms ):
        $filters_html = '<h3>Branches</h3>';
 
        foreach( $terms as $term )
        {
            $term_id = $term->term_id;
            $term_name = $term->name;
            $term_slug = $term->slug;
 
            $filters_html .= '<label for="'.$term_slug.'"><input type="checkbox" id="'.$term_slug.'" name="filter_genre[]" value="'.$term_id.'">'.$term_name.'</label>';
        }
        $filters_html .= '<hr>';
 
        return $filters_html;
    endif;
}

//Construct Loop & Results
function ajax_offices_filter()
{
	$query_data = $_GET;
	
	$loc_terms = ($query_data['loc']) ? explode(',',$query_data['loc']) : false;

	$branch_terms = ($query_data['branch']) ? explode(',',$query_data['branch']) : false;
	
	if ($loc_terms && $branch_terms) {
		$tax_query = array( array(
			'taxonomy' => 'location',
			'field' => 'id',
			'terms' => $loc_terms
		), array(
			'taxonomy' => 'branch',
			'field' => 'id',
			'terms' => $branch_terms
		));
	} else if($loc_terms) {
		$tax_query = array( array(
			'taxonomy' => 'location',
			'field' => 'id',
			'terms' => $loc_terms
		));
	} else if ($branch_terms) {
		$tax_query = array( array(
			'taxonomy' => 'branch',
			'field' => 'id',
			'terms' => $branch_terms
		));
	} else {
		$tax_query = '';
	}
	
	
	$paged = (isset($query_data['paged']) ) ? intval($query_data['paged']) : 1;
	
	$book_args = array(
		'post_type' => 'offices',
		's' => $search_value,
		'posts_per_page' => 2,
		'tax_query' => $tax_query,
		'paged' => $paged
	);
	$book_loop = new WP_Query($book_args);
	
	if( $book_loop->have_posts() ):
		while( $book_loop->have_posts() ): $book_loop->the_post();
			echo '<h2>'. get_the_title().'</h2>';
			$terms = get_the_terms( get_the_ID(), 'location' );

			if ( $terms && ! is_wp_error( $terms ) ) : 
				 
				$location = array();
				 
				foreach ( $terms as $term ) {
					$location[] = $term->name;
				}
					                         
				$allLocations = join( ", ", $location );
				 
				echo '<div><strong>Location: </strong>' .$allLocations. '</div>';
			endif;

			$terms = get_the_terms( get_the_ID(), 'branch' );
			if ( $terms && ! is_wp_error( $terms ) ) : 
				 
				$location = array();
				 
				foreach ( $terms as $term ) {
					$location[] = $term->name;
				}
					                         
				$allLocations = join( ", ", $location );
				 
				echo '<div><strong> Branch: </strong>' .$allLocations. '</div>';
				echo '<hr>';
			endif;
		endwhile;
		
		echo '<div class="filter-navigation">';
		$big = 999999999;
		echo paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total' => $book_loop->max_num_pages
		) );
		echo '</div>';	

	else:
		get_template_part('content-none');
	endif;
	wp_reset_postdata();
	
	die();
}

?>