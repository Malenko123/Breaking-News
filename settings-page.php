<?php
//add Breaking News page
function ads_options_page() {
 
	add_options_page(
		'Breaking News', // page <title>Title</title>
		'Breaking News', // menu link text
		'manage_options', // capability to access the page
		'breakingnews_settings_slug', // page URL slug
		'breakingnews_settings_form', // callback function with content
		2 // priority
	);
 
}
add_action( 'admin_menu', 'ads_options_page' );
 

function breakingnews_settings_form(){
 
	echo '<div class="wrap">
	<h1>Breaking News Settings</h1>
	<form method="post" action="options.php">';
 
		settings_fields( 'bn_settings' ); // settings group name
		do_settings_sections( 'breakingnews_settings_slug' ); // just a page slug
		submit_button();
 
	echo '</form></div>';
 
}

//register the setting
function bn_register_setting(){
 
 	//register breakingnews label
	register_setting(
		'bn_settings', // settings group name
		'breakingnews_label', // option name
		'sanitize_text_field' // sanitization function
	);
	//register background_color label
	register_setting(
		'bn_settings', 
		'bg_color', 
		'sanitize_text_field'
	);
	//register text color label
	register_setting(
		'bn_settings', 
		'text_color',
		'sanitize_text_field' 
	);
	//register featured post link
	register_setting(
		'bn_settings', 
		'featured_post_link', 
		'sanitize_text_field' 
	);
 
	add_settings_section(
		'breaking_news_id',
		'', // title (if needed)
		'', // callback function (if needed)
		'breakingnews_settings_slug' // page slug
	);
 
 	//add breakingnews label
	add_settings_field(
		'breakingnews_label',
		'Breaking News title',
		'breaking_news_label', // function which prints the field
		'breakingnews_settings_slug', // page slug
		'breaking_news_id',
		array( 
			'label_for' => 'breakingnews_label',
			'class' => 'setting-field', // for <tr> element
		)
	);
	//add background_color label
	add_settings_field(
		'bg_color',
		'Background Color',
		'breaking_news_bg_color', 
		'breakingnews_settings_slug', 
		'breaking_news_id', 
		array( 
			'label_for' => 'bg_color',
			'class' => 'setting-field', 
		)
	);
	//add text color field
	add_settings_field(
		'text_color',
		'Text Color',
		'breaking_news_text_color', 
		'breakingnews_settings_slug', 
		'breaking_news_id',
		array( 
			'label_for' => 'text_color',
			'class' => 'setting-field',
		)
	);
	//add featured post link
	add_settings_field(
		'featured_post_link',
		'Link to the featured post',
		'featured_post_link',
		'breakingnews_settings_slug',
		'breaking_news_id',
		array( 
			'label_for' => 'featured_post_link',
			'class' => 'setting-field',
		)
	);
}
add_action( 'admin_init',  'bn_register_setting' );
 

/*
 Breaking news settings Fields
*/
function breaking_news_label(){
 
	$breakingnewsLabel = get_option( 'breakingnews_label' );
	printf(
		'<input type="text" id="breakingnews_label" name="breakingnews_label" value="%s" />',
		esc_attr( $breakingnewsLabel )
	);
}

function breaking_news_bg_color(){
 
	$bgColor = get_option( 'bg_color' );
	printf(
		'<input type="text" id="bg_color" value="%s" class="color-field" name="bg_color" data-default-color="#effeff" />',
		esc_attr( $bgColor )
	); 
}

function breaking_news_text_color(){
 
	$textColor = get_option( 'text_color' );
	printf(
		'<input type="text" id="text_color" value="%s" class="color-field" name="text_color" data-default-color="#effeff" />',
		esc_attr( $textColor )
	);
}

//display the featured post in the settings page
function featured_post_link(){
 
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

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 

		$featuredPost = get_post_meta( get_the_ID(), 'featured-checkbox', true );

		if( $featuredPost == 'yes' ){
			echo '<a href="'.get_permalink(get_the_ID()).'" target="_blank">' . get_the_title() . '</a>';
			echo '<a href="'.get_edit_post_link(get_the_ID()).'" target="_blank" style="margin-left:8px;">Edit Post</a>';
		} else{
			echo 'No featured post selected';
		}

	endwhile; endif;
}


?>
