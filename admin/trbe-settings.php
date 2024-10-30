<?php
function trbe_admin_menu() {
    global $trbe_settings_page;
    $trbe_settings_page = add_submenu_page(
                            'learndash-lms', //The slug name for the parent menu
                            __( 'Bulk Edit', 'learndash-bulk-edit' ), //Page title
                            __( 'Bulk Edit', 'learndash-bulk-edit' ), //Menu title
                            'manage_options', //capability
                            'learndash-bulk-edit', //menu slug 
                            'trbe_admin_page' //function to output the content
                        );
}
add_action( 'admin_menu', 'trbe_admin_menu' );


function trbe_admin_page() {
?>

<div class="trbe-head-panel">
    <h1><?php esc_html_e( 'Bulk Edit for Learndash', 'learndash-bulk-edit' ); ?></h1>
    <!-- <h3><?php// esc_html_e( 'Prevent user from accessing course content after course completion.', 'learndash-bulk-edit' ); ?></h3> -->
</div>

<div class="wrap trbe-wrap-grid">

    <form method="post" id="trbe-bulk-edit-form">
        
        <div class="trbe-form-fields">

            <div class="trbe-settings-title">
                Bulk Edit for Learndash - Settings </div>


            <div class="trbe-form-fields-label">
                Define the price </div>
            <div class="trbe-form-fields-group">
                <!-- text -->
                <input type="text" placeholder="" class="" id="trbe_courses_price" name="trbe_courses_price" style="width: 100px;" required>
            </div>
            <hr>

            <div class="trbe-form-fields-label">
                Select a category for courses to be edited 
            </div>

            <div class="trbe-form-fields-group">
                <!-- select -->
                <div class="trbe-form-div-select">
                    <label>
                        <select id="trbe_category_select" name="trbe_category_select" required>
                            <?php trbe_show_ld_categories_options(); ?>
                        </select>
                    </label>
                </div>
            </div>

            <div class="trbe-form-fields-label">
                Select courses to be edited 
                <span>
                    * you can select "All" or as many courses as you like, by Ctrl+Click (deselect by Ctrl+Click as well).
                </span>
            </div>

            <div class="trbe-form-fields-group">
                <!-- select -->
                <div class="trbe-form-div-select">
                    <label>
                        <select id="trbe_courses_select" name="trbe_courses_select[]" multiple="" required>
                            <?php trbe_show_ld_courses_options(); ?>
                        </select>
                    </label>
                </div>
            </div>
            <?php if ( class_exists( 'woocommerce' ) ) { ?>
		
            <div class="trbe-form-fields-label">
                WooCommerce
                <span>
                    * if you sell a package of courses in a single product, the price of the product will be updated with the price of a single course.
                </span>
            </div>

            <div class="trbe-form-fields-group">
                <div class="">
                    <input type="checkbox" id="trbe_update_woocommerce" name="trbe_update_woocommerce">
                    <label for="trbe_update_woocommerce">
                        <?php esc_html_e( 'Update related products prices', 'learndash-bulk-edit' ); ?>
                    </label>
                </div>
            </div>
            <?php } ?>

            <hr>

            <p class="submit">
                <input type="submit" name="submit-bulk-edit" id="submit-bulk-edit" class="button button-primary" value="Bulk Edit!">
            </p>

            <div style="display: flex;justify-content: space-evenly;">
                <div>
                    Contact Luis Rock, the author, at
                    <a href="mailto:luisrock@wptrat.com">
                        luisrock@wptrat.com
                    </a>
                </div>
                <div>
                    More plugins for LearnDash at
                    <a href="https://wptrat.com">
                        wptrat.com
                    </a> 
                </div>
            </div>

        </div> <!-- end form fields -->
    </form>
</div>
<?php } ?>