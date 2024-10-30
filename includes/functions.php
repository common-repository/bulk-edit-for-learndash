<?php

function trbe_get_public_post_types() {
	$args = array(
		'public'   => true,
	 );
	return get_post_types($args); 
}

function trbe_get_role_names() {
	global $wp_roles;
	if (!isset( $wp_roles)) {
		$wp_roles = new WP_Roles();
	}
	$roles = [];
	foreach ($wp_roles->roles as $k => $r) {
		$caps = $r['capabilities'];
		if( ( isset($caps['edit_posts']) && $caps['edit_posts'] ) || ( isset($caps['moderate_comments']) && $caps['moderate_comments'] ) ) {
			continue;
		}
		$roles[] = $k;
	}
	return $roles;
}


function trbe_show_ld_courses_options($echo = true) {
    //get all courses
    $courses = get_posts(array(
        'post_type' => 'sfwd-courses',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    $options = '<option value="-1"> All </option>';
    foreach($courses as $course) {
		$options .= '<option value="'. esc_attr($course->ID).'"> ' . esc_html($course->post_title) . ' </option>';
    }
	if($echo) {
		$allowed_html = array(
			'option' => array(
				'value' => array()
			 ),
		);
		echo wp_kses($options,$allowed_html);
	}
	return $options;
}

function trbe_echo_categories_options() {
    //get all categories
	$categories = get_categories(array(
		'hide_empty' => 0,
		'taxonomy' => 'category',
		'orderby' => 'name',
		'order' => 'ASC'
	));
	$options = '<option value="-1"> All </option>';
	foreach($categories as $category) {
		$options .= '<option value="'. esc_attr($category->term_id).'"> ' . esc_html($category->name) . ' </option>';
	}
	$allowed_html = array(
		'option' => array(
			'value' => array()
		 ),
	);
	echo wp_kses($options,$allowed_html);
}

//function to display a dropdown with all learndash course categories
function trbe_show_ld_categories_options() {
	$categories = get_terms( 'ld_course_category', array(
		'hide_empty' => 0,
		'orderby' => 'name',
		'order' => 'ASC'
	));
	$options = '<option value="-1"> All </option>';
	foreach($categories as $category) {
		$options .= '<option value="'. esc_attr($category->term_id).'"> ' . esc_html($category->name) . ' </option>';
	}
	$allowed_html = array(
		'option' => array(
			'value' => array()
		 ),
	);
	echo wp_kses($options,$allowed_html);
}

function trbe_get_ld_courses_by_category($category_id = 0) {
	$courses = get_posts(array(
		'post_type' => 'sfwd-courses',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'tax_query' => array(
			array(
				'taxonomy' => 'ld_course_category',
				'field' => 'id',
				'terms' => $category_id,
			),
		),
	));
	return $courses;
}

//function to display a dropdown with all learndash courses from a specific category
function trbe_ld_courses_by_category_options($category_id = 0) {
	$courses = trbe_get_ld_courses_by_category($category_id);
	$options = '<option value="-1"> All </option>';
	foreach($courses as $course) {
		$options .= '<option value="'. esc_attr($course->ID).'"> ' . esc_html($course->post_title) . ' </option>';
	}
	return $options;
} 


function trbe_ld_course_options() {
    if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'trbe_nonce' ) ) {
        echo 'Error: Security check';
		die();
    }
    if ( !isset( $_POST['category_id'] ) ) {
        echo 'Error: No category id';
		die();
    }
	$category_id = intval($_POST['category_id']);
	$allowed_html = array(
		'option' => array(
			'value' => array()
		 ),
	);
	
	if($category_id == -1) {
		$options = trbe_show_ld_courses_options(false);
		echo wp_kses($options,$allowed_html);
		die();
	}
	$options = trbe_ld_courses_by_category_options($category_id);
	echo wp_kses($options,$allowed_html);
	die();
}


function trbe_ld_bulk_edit() {
    $output = [
        'status' => 'failed',
        'message' => '',
    ];
    if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'trbe_nonce' ) ) {
        $output['message'] = 'Security check';
		echo json_encode($output);
		die();
    }
    if ( !isset( $_POST['price'] ) ) {
        $output['message'] = 'No price';
		echo json_encode($output);
		die();
    }
	$price = sanitize_text_field($_POST['price']);
    if ( !isset( $_POST['courses_selected'] ) ) {
        $output['message'] = 'No courses selected';
		echo json_encode($output);
		die();
    }
	$update_woocommerce = intval($_POST['update_woocommerce']);
	$output['status'] = 'success';
	$output['message'] = 'ok!!!';

	$courses_selected = [];
	// Loop through the input and sanitize each of the values
	foreach ( $_POST['courses_selected'] as $val ) {
		$courses_selected[] = intval($val);
	}

	//foreach courses, update sfwd-courses_course_price meta
	$updated = 0;
	foreach ($courses_selected as $course_id) {
		if(learndash_update_setting( $course_id, 'course_price', $price )) {
			$updated++;
			if($update_woocommerce) {
				trbe_update_woocommerce_price($course_id, $price);
			}
		}
	}

	if($updated == 0) {
		$message = 'No courses updated';
	} else if($updated < count($courses_selected)) {
		$message = 'Updated ' . $updated . ' courses (out of ' . count($courses_selected) . ')';
	} else if($updated == 1) {
		$message = 'Updated 1 course';
	} else {
		$message = 'Updated ' . $updated . ' courses';
	}

	$output['message'] = $message;

	echo json_encode($output);
	die();
}

//when woocommerce is activated
//TODO: WHAT IF PRODUCT IS A BUNDLE OF COURSES? SHOULD PLUGIN MAKE A SUM OF THE PRICES?
function trbe_get_products_with_related_courses() {
    global $wpdb;
    $query = "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_related_course'";
    $result = $wpdb->get_results( $query );
    if( !empty( $result ) ) {
        return $result;
    }
    return false;
}

function trbe_get_products_ids_by_course_id( $course_id ) {
    $courses_products = trbe_get_products_with_related_courses();
    if(!$courses_products) {
        return false;
    }
    $ids = [];    
    foreach($courses_products as $prod) {
        $id = $prod->post_id;
        $related_courses = get_post_meta( $id, '_related_course', true );
        //maybe unserialize related courses
        if( is_serialized( $related_courses ) ) {
            $related_courses = unserialize( $related_courses );
        }
        if( is_array($related_courses) && in_array( $course_id, $related_courses ) ) {
            $ids[] = $id;
        } else if( $related_courses == $course_id ) {
            $ids[] = $id;
        }   
    }
    return $ids;
}

function trbe_update_woocommerce_price($course_id, $new_price) {
	/**
	 * Check if WooCommerce is activated
	 * https://woocommerce.com/document/query-whether-woocommerce-is-activated/
	 */
	if ( !class_exists( 'woocommerce' ) ) {
		return false;
	}
	//for WoocCommerce, update the product price
	$products = trbe_get_products_ids_by_course_id( $course_id );
	if(!is_array($products) || empty($products)) {
		return false;
	}
	//foreach related course, update post meta keys _price and _regular_price
	foreach ($products as $product_id) {
		update_post_meta($product_id, '_price', (float)$new_price);
		update_post_meta($product_id, '_regular_price', (float)$new_price);
	}
}