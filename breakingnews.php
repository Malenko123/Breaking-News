
<?php
/*
	Plugin Name: Breaking News
	Plugin URI: 
	Description: Make any post a breaking news
	Author: Kliment Malenko
	Version: 1.0
	Author URI: 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


include( plugin_dir_path( __FILE__ ) . 'settings-page.php');



function register_script() {
    wp_register_style( 'breakingnews-style', plugins_url('assets/css/bn-style.css', __FILE__), false, '1.0.0', 'all');
}
add_action('init', 'register_script');

//load in wp_head
function insert_jquery(){
	wp_enqueue_script('jquery', false, array(), false, false);
	wp_enqueue_style( 'breakingnews-style');
	
}
add_filter('wp_enqueue_scripts','insert_jquery',1);

function breakingnews_scripts() {    

	wp_register_script( 'scripts', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), '2.5.1' );
	wp_enqueue_script('scripts');

	wp_register_script( 'datetimepicker-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', null, null, true );
	wp_enqueue_script('datetimepicker-js');
    
    wp_enqueue_style( 'datetimepicker-style', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css' );
    wp_enqueue_script( 'datetimepicker-style' );

 	wp_enqueue_style( 'wp-color-picker');
    wp_enqueue_script( 'wp-color-picker');   
}
add_action( 'admin_enqueue_scripts', 'breakingnews_scripts' );


//create a custom meta box for posts
add_action( 'admin_init', 'my_admin_ads' );
function my_admin_ads() {
    add_meta_box( 'post_meta_box', 'Breaking News', 'display_post_meta_box','post', 'normal', 'high' );
}


function display_post_meta_box( $post ) {

	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );

    $featured_checkbox_meta = get_post_meta( $post->ID );
    $expiry_checkbox_meta = get_post_meta( $post->ID );
    ?>

    <h4>General Details</h4>
    <table width="100%">
        <tr>
            <td><?php _e( 'Custom Title' ); ?></td>
            <td><input type="text" name="custom_title" placeholder="" value="<?php echo esc_html( get_post_meta( $post->ID, 'custom_title', true ) );?>" />
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Featured Post' ); ?></td>
            <td><input type="checkbox" name="featured-checkbox" id="featured-checkbox" value="yes" <?php if ( isset ( $featured_checkbox_meta['featured-checkbox'] ) ) checked( $featured_checkbox_meta['featured-checkbox'][0], 'yes' ); ?> />
            <?php _e( 'Make this post breaking news' )?>
            </td>
        </tr>
        <tr>
			<td><?php _e( 'Expiry Date' ); ?></td>
			<td>
				<p><label><input type="checkbox" name="set_exp_date" value="yes" <?php if ( isset ( $expiry_checkbox_meta['set_exp_date'] ) ) checked( $expiry_checkbox_meta['set_exp_date'][0], 'yes' ); ?> class="set-exp-date"> Set an expiration date and time</label></p>
			</td>
		</tr>
        <tr>
    	 	<?php $expiry_date_textfield = get_post_meta( $post->ID, 'expiry_date_textfield', true ); ?>
         	
	        <td><?php _e('Select expiry Date'); ?></td>              
	        <td><input type="text" class="expiry-date" name="expiry_date_textfield" value="<?php echo esc_attr($expiry_date_textfield); ?>" /></td>

	        <script type="text/javascript">
	            jQuery(document).ready(function() {
	                jQuery('.expiry-date').datetimepicker({
	                    format:'Y-m-d H:i:s',
	                });
	            });
	        </script> 
        </tr>
    </table>
<?php 
}


function add_post_fields( $post_id, $post ) {

	//check the save status and overcome autosave
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 

    // exits depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }


    //custom title field
    if( isset( $_POST[ 'custom_title' ] ) ) {
	    update_post_meta( $post_id, 'custom_title', sanitize_text_field( $_POST['custom_title'] ) );
	} else {
	    delete_post_meta( $post_id, 'custom_title' );
	}

    // if checkbox is checked update to yes or no for unchecked
	if( isset( $_POST[ 'featured-checkbox' ] ) ) {
	    update_post_meta( $post_id, 'featured-checkbox', 'yes' );
	} else {
	    update_post_meta( $post_id, 'featured-checkbox', 'no' );
	}

	//expiry date checkbox
	if( isset( $_POST[ 'set_exp_date' ] ) ) {
	    update_post_meta( $post_id, 'set_exp_date', 'yes' );
	} else {
	    update_post_meta( $post_id, 'set_exp_date', 'no' );	
	}

	//expiry date field
	if ( isset( $_POST['expiry_date_textfield'] ) ) {        
        $new_expiry_date = $_POST['expiry_date_textfield'];
        update_post_meta( $post_id, 'expiry_date_textfield', $new_expiry_date );      
    } else {
		delete_post_meta( $post_id, 'expiry_date_textfield' );
	}
}
add_action( 'save_post', 'add_post_fields', 10, 2 );
 


function display_breakingnews_post(){

	global $post;

	$today = date('Y-m-d H:i:s');

	$exp_date_checkbox = get_post_meta( $post->ID, 'set_exp_date', true );
	$exp_date_meta = get_post_meta( $post->ID, 'expiry_date_textfield', true );


	$args = array(
	    'post_type' => 'post',
	    'posts_per_page'   => 1,
	    'orderby' => 'publish_date',
		'order' => 'DESC',
		'meta_query' => array(
            array(
                'key' => 'featured-checkbox',
                'value' => 'yes'
    		),
		)
	);

	//if expiry date checkbox is checked and a date is selected then:
	if( $exp_date_checkbox == 'yes' && $exp_date_meta ){
		$args['meta_query'][] = array(
	 		'value' => $today,
			'key' => 'expiry_date_textfield',	         	
        	'compare' => '>',
            'type' => 'DATETIME',
		);		
	}

	$query = new WP_Query( $args );


	if ( $query->have_posts() ) : ?>

		<div class="breakingsnews-section">
			<?php
		      while ( $query->have_posts() ) : $query->the_post(); 
		      	
	      		$customTitle = get_post_meta( $post->ID, 'custom_title', true ); 
		      	?>
		      	<div class="breaking-news-row" style="background-color:<?php echo (!empty(get_option( 'bg_color' )) ? get_option( 'bg_color' ) : 'blue'); ?>">
			      	<a href="<?php the_permalink(); ?>" class="breaking-news-post-link">
			      		<div class="breaking-news-title">
			      			<h5 style="color:<?php echo (!empty(get_option( 'text_color' )) ? get_option( 'text_color' ) : '#fff'); ?>">
			      				<?php echo (!empty(get_option( 'breakingnews_label' )) ? get_option( 'breakingnews_label' ) : 'Breaking News:'); ?> <span class="breaking-news-post-title"><?php echo ( $customTitle ? $customTitle : get_the_title() ); ?>	</span> 
			      			</h5>   				
		      			</div>
			      	</a>
			  	</div>
			<?php endwhile; ?> <!-- end while -->
		</div>
	<?php endif; ?> <!-- end if -->
	<?php wp_reset_postdata(); 

	?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.breakingsnews-section').appendTo('#site-header');
		});
	</script>
	<?php
}
add_action('wp_head', 'display_breakingnews_post');

?>
