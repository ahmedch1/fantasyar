<?php

// start your child theme code here
function create_fighter_post_type() {
    register_post_type( 'fighter',
        array(
            'labels' => array(
                'name' => __( 'fighters' ),
                'singular_name' => __( 'fighter' )
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' )
        )
    );
}
add_action( 'init', 'create_fighter_post_type' );

function create_fighter_taxonomies() {
    $labels = array(
        'name'              => _x( 'Grades', 'taxonomy general name' ),
        'singular_name'     => _x( 'Grade', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Grades' ),
        'all_items'         => __( 'All Grades' ),
        'parent_item'       => __( 'Parent Grade' ),
        'parent_item_colon' => __( 'Parent Grade:' ),
        'edit_item'         => __( 'Edit Grade' ),
        'update_item'       => __( 'Update Grade' ),
        'add_new_item'      => __( 'Add New Grade' ),
        'new_item_name'     => __( 'New Grade Name' ),
        'menu_name'         => __( 'Grades' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'grade' ),
    );

    register_taxonomy( 'grade', array( 'fighter' ), $args );


    $labels = array(
        'name'              => _x( 'Teams', 'taxonomy general name' ),
        'singular_name'     => _x( 'Team', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Teams' ),
        'all_items'         => __( 'All Teams' ),
        'parent_item'       => __( 'Parent Team' ),
        'parent_item_colon' => __( 'Parent Team:' ),
        'edit_item'         => __( 'Edit Team' ),
        'update_item'       => __( 'Update Team' ),
        'add_new_item'      => __( 'Add New Team' ),
        'new_item_name'     => __( 'New Team Name' ),
        'menu_name'         => __( 'Teams' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'team' ),
    );

    register_taxonomy( 'team', array( 'fighter' ), $args );
}
add_action( 'init', 'create_fighter_taxonomies' );
function add_fighter_fields() {
    add_meta_box(
        'fighter_fields',
        __( 'fighter Fields', 'textdomain' ),
        'fighter_fields_callback',
        'fighter'
    );
}
add_action( 'add_meta_boxes', 'add_fighter_fields' );
function fighter_fields_callback( $post ) {
    wp_nonce_field( 'fighter_fields', 'fighter_fields_nonce' );

    $full_score = 0;

    for ($season = 1; $season <= 9; $season++) {
        $kills = get_post_meta( $post->ID, '_season' . $season . '_kills', true );
        $match_bonus = get_post_meta( $post->ID, '_season' . $season . '_match_bonus', true );

        $kills = intval( $kills );
        $match_bonus = intval( $match_bonus );

        $total_score = $kills + $match_bonus;
        $full_score += $total_score;

        echo '<h4>Season ' . $season . '</h4>';

        echo '<p><label for="season' . $season . '_kills">' . __( 'Kills', 'textdomain' ) . '</label><br />';
        echo '<input type="number" id="season' . $season . '_kills" name="season' . $season . '_kills" value="' . esc_attr( $kills ) . '" /></p>';

        echo '<p><label for="season' . $season . '_match_bonus">' . __( 'Match Bonus', 'textdomain' ) . '</label><br />';
        echo '<input type="number" id="season' . $season . '_match_bonus" name="season' . $season . '_match_bonus" value="' . esc_attr( $match_bonus ) . '" /></p>';

        echo '<p><label for="season' . $season . '_total_score">' . __( 'Total Score', 'textdomain' ) . '</label><br />';
        echo '<input type="number" id="season' . $season . '_total_score" name="season' . $season . '_total_score" value="' . esc_attr( $total_score ) . '" readonly /></p>';
    }

    echo '<h4>Full Score</h4>';
    echo '<p><label for="full_score">' . __( 'Full Score', 'textdomain' ) . '</label><br />';
    echo '<input type="number" id="full_score" name="full_score" value="' . esc_attr( $full_score ) . '" readonly /></p>';
}


function save_fighter_fields( $post_id ) {
    $post_type = get_post_type($post_id);
    if ( 'fighter' != $post_type ) {
        return;
    }

    if ( ! isset( $_POST['fighter_fields_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['fighter_fields_nonce'], 'fighter_fields' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    for ($season = 1; $season <= 9; $season++) {
        $kills = isset( $_POST['season' . $season . '_kills'] ) ? sanitize_text_field( $_POST['season' . $season . '_kills'] ) : '';
        $match_bonus = isset( $_POST['season' . $season . '_match_bonus'] ) ? sanitize_text_field( $_POST['season' . $season . '_match_bonus'] ) : '';

        update_post_meta( $post_id, '_season' . $season . '_kills', $kills );
        update_post_meta( $post_id, '_season' . $season . '_match_bonus', $match_bonus );
        update_post_meta( $post_id, '_season' . $season . '_total_score', intval($kills) + intval($match_bonus));
    }
}
add_action( 'save_post', 'save_fighter_fields' );
// add custom user meta fields to store fighter choices
function add_custom_user_meta_fields() {
    add_user_meta(0, 'favorite_fighter', '');
}
add_action( 'admin_init', 'add_custom_user_meta_fields' );

// display the custom field on the user's profile page
function is_current_user_administrator() {
    $current_user = wp_get_current_user();
    return in_array('administrator', $current_user->roles);
}

function display_favorite_fighter_field($user) {
    $grades = array('captain', 'grade_a', 'grade_b', 'grade_c');

    $user_total_score = 0;
    $user_season_kills = array_fill(1, 9, 0);
    $user_season_match_bonus = array_fill(1, 9, 0);

    for ($season = 1; $season <= 9; $season++) {
        echo "<h3>Season $season</h3>";

        if (is_current_user_administrator()) {
            $season_locked_key = "season_{$season}_locked";
            $season_locked_value = get_option($season_locked_key) === 'on';
            ?>
            <p>
                <label for="<?php echo $season_locked_key; ?>">Lock Season <?php echo $season; ?>:</label>
                <input type="checkbox" name="<?php echo $season_locked_key; ?>" id="<?php echo $season_locked_key; ?>" <?php checked($season_locked_value, true); ?> />
            </p>
            <?php
        } else {
            $season_locked_value = get_option("season_{$season}_locked") === 'on';
        }

        $disabled = $season_locked_value ? 'disabled' : '';

        foreach ($grades as $grade) {
            $args = array(
                'post_type' => 'fighter',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'grade',
                        'field' => 'slug',
                        'terms' => $grade
                    )
                )
            );
            $fighters = get_posts( $args );

            $favorite_fighter_key = "favorite_fighter_season_{$season}_{$grade}";
            $favorite_fighter = get_user_meta( $user->ID, $favorite_fighter_key, true );

            ?>
            <h4><?php echo ucfirst(str_replace('_', ' ', $grade)); ?></h4>
            <table class="form-table">
                <tr>
                    <th><label for="<?php echo $favorite_fighter_key; ?>"><?php _e( "Select a fighter:", 'my_textdomain' ); ?></label></th>
                    <td>
                        <div class="select-wrapper">
                            <div class="selected-option">
                                <?php echo $favorite_fighter ? '<img src="' . get_the_post_thumbnail_url($favorite_fighter) . '" alt="' . get_the_title($favorite_fighter) . '">' . get_the_title($favorite_fighter) . ' (' . (is_array(get_the_terms($favorite_fighter, 'team')) ? get_the_terms($favorite_fighter, 'team')[0]->name : '') . ')' : __('Select a fighter', 'my_textdomain'); ?>
                                <span class="reset-button" style="cursor: pointer;">&times;</span>
                            </div>

                            <select class="custom-select" name="<?php echo $favorite_fighter_key; ?>" id="<?php echo $favorite_fighter_key; ?>" <?php echo $disabled; ?>>
                                <option value=""><?php _e('Select a fighter', 'my_textdomain'); ?></option>
                                <?php foreach ($fighters as $fighter) : ?>
                                    <option value="<?php echo $fighter->ID; ?>" <?php selected( $favorite_fighter, $fighter->ID ); ?> data-img-src="<?php echo get_the_post_thumbnail_url($fighter); ?>" data-team="<?php echo is_array(get_the_terms($fighter, 'team')) ? get_the_terms($fighter, 'team')[0]->name : ''; ?>"><?php echo $fighter->post_title; ?> (<?php echo is_array(get_the_terms($fighter, 'team')) ? get_the_terms($fighter, 'team')[0]->name : ''; ?>)</option>

                                <?php endforeach; ?>
                            </select>
                            <div class="options-container hidden">
                                <div class="option" data-value=""><?php _e('Select a fighter', 'my_textdomain'); ?></div>
                                <?php foreach ($fighters as $fighter) : ?>
                                    <div class="option" data-value="<?php echo $fighter->ID; ?>" <?php echo $favorite_fighter == $fighter->ID ? 'style="display:none;"' : ''; ?>>
                                        <img src="<?php echo get_the_post_thumbnail_url($fighter); ?>" alt="<?php echo $fighter->post_title; ?>">
                                        <?php echo $fighter->post_title . ' (' . (is_array(get_the_terms($fighter->ID, 'team')) ? get_the_terms($fighter->ID, 'team')[0]->name : '') . ')'; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>


                    </td>
                </tr>
                <?php if ($favorite_fighter) : ?>
                    <?php
                    $kills = get_post_meta($favorite_fighter, '_season' . $season . '_kills', true);
                    $match_bonus = get_post_meta($favorite_fighter, '_season' . $season . '_match_bonus', true);
                    $total_score = get_post_meta($favorite_fighter, '_season' . $season . '_total_score', true);

                    $user_total_score += intval($total_score);
                    $user_season_kills[$season] += intval($kills);
                    $user_season_match_bonus[$season] += intval($match_bonus);
                    ?>
                    <tr class="fighter-stats">
                        <th><label for="fighter_stats"><?php _e("Stats:", 'my_textdomain'); ?></label></th>
                        <td>
                            <p><label for="kills"><?php _e('Kills', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_kills" name="season<?php echo $season; ?>_<?php echo $grade; ?>_kills" value="<?php echo esc_attr($kills); ?>" readonly /></p>

                            <p><label for="match_bonus"><?php _e('Match Bonus', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_match_bonus" name="season<?php echo $season; ?>_<?php echo $grade; ?>_match_bonus" value="<?php echo esc_attr($match_bonus); ?>" readonly /></p>

                            <p><label for="total_score"><?php _e('Total Score', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_total_score" name="season<?php echo $season; ?>_<?php echo $grade; ?>_total_score" value="<?php echo esc_attr($total_score); ?>" readonly /></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
            <style>.fighter-image {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                }

                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 4px 8px;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                }
                /* Fighter image styles */
                .fighter-image {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                }

                /* Dropdown menu styles */
                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-bottom: 1px solid #dee2e6;
                }

                .select2-container .select2-results__option--highlighted {
                    background-color: #007bff;
                    color: #ffffff;
                }

                /* Input field styles */
                .select2-container .select2-selection--single {
                    height: 58px;
                    display: flex;
                    align-items: center;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                    padding: 0 8px;
                }

                .select2-container .select2-selection__arrow {
                    height: 58px;
                }

                /* Adjust font size */
                .select2-container .select2-selection__rendered,
                .select2-container .select2-results__option {
                    font-size: 16px;
                }

                /* Add some padding and border-radius to the dropdown */
                .select2-dropdown {
                    border-radius: 4px;
                    padding: 8px 0;
                }

                /* Add a box-shadow to the dropdown */
                .select2-container--open .select2-dropdown {
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.06);
                }
                /* Dropdown menu styles */
                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-bottom: 1px solid #dee2e6;
                    color: #333; /* Add text color */
                    font-weight: 600; /* Add font-weight */
                }

                .select2-container .select2-results__option--highlighted {
                    background-color: #007bff;
                    color: #ffffff;
                }

                /* Input field styles */
                .select2-container .select2-selection--single {
                    height: 58px;
                    display: flex;
                    align-items: center;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                    padding: 0 8px;
                    color: #333; /* Add text color */
                    font-weight: 600; /* Add font-weight */
                }
                .empty-card {
                    width: 50px;
                    height: 50px;
                    border: 1px solid #ccc;
                    display: inline-block;
                    margin-right: 5px;
                }


            </style>
            <script>

                jQuery(document).ready(function() {
                    function formatFighter (fighter) {
                        if (!fighter.id) {
                            return fighter.text;
                        }
                        var imgSrc = jQuery(fighter.element).data('img-src');
                        var markup = `
        <img src="${imgSrc}" class="fighter-image" alt="${fighter.text}" />
        <span>${fighter.text}</span>
    `;
                        return markup;
                    };

                    jQuery('select[id^="favorite_fighter_season_"]').select2({
                        templateResult: formatFighter,
                        templateSelection: formatFighter,
                        escapeMarkup: function (markup) { return markup; }
                    });

                    jQuery('select[id^="favorite_fighter_season_"]').on('change', function() {
                        var selectElement = jQuery(this);
                        var selectedValue = selectElement.val();
                        var selectId = selectElement.attr('id');
                        var selectWrapper = selectElement.closest(".select-wrapper");

                        // Update the relevant elements on the page
                        var fighter = selectElement.find("option:selected");
                        var imgSrc = fighter.data("img-src");
                        var fighterName = fighter.text();
                        var fighterTeam = fighter.data("team") || '';

                        selectWrapper.find(".selected-option img").attr('src', imgSrc).attr('alt', fighterName);
                        selectWrapper.find(".selected-option span:not(.reset-button)").html(fighterName + ' (' + fighterTeam + ')');


                        // Send an AJAX request to update the user settings
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: {
                                action: 'update_favorite_fighter',
                                user_id: '<?php echo $user->ID; ?>',
                                select_id: selectId,
                                selected_value: selectedValue,
                                security: '<?php echo wp_create_nonce('update-favorite-fighter-nonce'); ?>'
                            },
                            success: function(response) {
                                console.log(response);
                                // Check if the response contains fighter data
                                if (response.hasOwnProperty('fighter_data')) {
                                    var fighterData = response.fighter_data;
                                    var season = fighterData.season;
                                    var grade = fighterData.grade;

                                    // Update the fighter stats elements with the new data
                                    jQuery('#season' + season + '_' + grade + '_kills').val(fighterData.kills);
                                    jQuery('#season' + season + '_' + grade + '_match_bonus').val(fighterData.match_bonus);
                                    jQuery('#season' + season + '_' + grade + '_total_score').val(fighterData.total_score);

                                    // Update the user's total score element with the new data
                                    jQuery('#user_total_score').val(response.user_total_score);
                                }
                            },
                        });
                    });

                    // Handle click event on the "X" button to reset the field value
// Handle click event on the "X" button to reset the field value
                    jQuery(".reset-button").click(function() {
                        var selectWrapper = jQuery(this).closest(".select-wrapper");
                        var selectElement = selectWrapper.find("select");

                        // Check if the select element is disabled (locked), and return early if it is
                        if (selectElement.prop("disabled")) {
                            return;
                        }

                        // Reset the select element value
                        selectElement.val("").trigger("change");

                        // Update the selected-option div content with an empty card
                        selectWrapper.find(".selected-option img").attr('src', '').attr('alt', '');
                        selectWrapper.find(".selected-option span:not(.reset-button)").html('<?php echo __("Select a fighter", "my_textdomain"); ?>');
                    });

                });

            </script>



            <?php
        }

        echo "<h4>Season $season Totals</h4>";
        echo '<p>Total Kills: ' . $user_season_kills[$season] . '</p>';
        echo '<p>Total Match Bonus: ' . $user_season_match_bonus[$season] . '</p>';
    }

    echo '<h3>Total Score99</h3>';
    echo '<input type="number" id="user_total_score" name="user_total_score" value="' . esc_attr($user_total_score) . '" readonly />';
}



function save_favorite_fighter_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    $grades = array('captain', 'grade_a', 'grade_b', 'grade_c');
    $user_total_score = 0;

    for ($season = 1; $season <= 9; $season++) {
        if (is_current_user_administrator()) {
            $season_locked_key = "season_{$season}_locked";
            $season_locked_value = isset($_POST[$season_locked_key]) ? 'on' : 'off';
            update_option($season_locked_key, $season_locked_value);
        }

        foreach ($grades as $grade) {
            if (isset($_POST['favorite_fighter_season_' . $season . '_' . $grade])) {
                update_user_meta($user_id, 'favorite_fighter_season_' . $season . '_' . $grade, $_POST['favorite_fighter_season_' . $season . '_' . $grade]);

                // Calculate the user's total score
                $fighter_id = $_POST['favorite_fighter_season_' . $season . '_' . $grade];
                $fighter_season_total_score = get_post_meta($fighter_id, '_season' . $season . '_total_score', true);
                $user_total_score += intval($fighter_season_total_score);
            }
        }
    }

    // Save the user's total score
    update_user_meta($user_id, 'user_total_score', $user_total_score);
    //call other functions in other places
    populate_favorite_fighters_based_on_previous_season($user_id);
    display_four_tables($user, $season, $grade);


}

add_action('show_user_profile', 'display_favorite_fighter_field');
add_action('edit_user_profile', 'display_favorite_fighter_field');

add_action('personal_options_update', 'save_favorite_fighter_field');
add_action('edit_user_profile_update', 'save_favorite_fighter_field');




//populate based on previous season
function populate_favorite_fighters_based_on_previous_season($user_id) {
    $grades = array('captain', 'grade_a', 'grade_b', 'grade_c');

    for ($season = 2; $season <= 9; $season++) {
        $previous_season = $season - 1;

        foreach ($grades as $grade) {
            $favorite_fighter_key_previous_season = "favorite_fighter_season_{$previous_season}_{$grade}";
            $favorite_fighter_key_current_season = "favorite_fighter_season_{$season}_{$grade}";

            $favorite_fighter_previous_season = get_user_meta($user_id, $favorite_fighter_key_previous_season, true);

            if (!empty($favorite_fighter_previous_season)) {
                update_user_meta($user_id, $favorite_fighter_key_current_season, $favorite_fighter_previous_season);
            }
        }
    }
}

//new functions//
function user_ranking_shortcode($atts) {
    $atts = shortcode_atts(array(
        'per_page' => 10
    ), $atts, 'user_ranking');

    $per_page = intval($atts['per_page']);
    $paged = max(1, get_query_var('paged'));
    $user_args = array(
        'number' => $per_page,
        'paged' => $paged,
        'meta_key' => 'user_total_score',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'user_total_score',
                'compare' => 'EXISTS',
            ),
        ),
    );

    $user_query = new WP_User_Query($user_args);
    $total_users = $user_query->get_total();
    $users = $user_query->get_results();

    ob_start();
    ?>
    <table id="userList">
        <thead>
        <tr>
            <th>Rank</th>
            <th>User</th>
            <th>Points</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($users)) {
            $rank = 1;
            foreach ($users as $user) {
                $profile_url = get_author_posts_url($user->ID);
                $avatar_url = get_avatar_url($user->ID);
                $user_total_score = get_user_meta($user->ID, 'user_total_score', true);
                ?>
                <tr class="<?php echo ($rank <= 3) ? 'top-three' : ''; ?>">
                    <td><?php echo $rank; ?></td>
                    <td>
                        <a href="<?php echo $profile_url; ?>" class="user-link">
                            <img src="<?php echo $avatar_url; ?>" alt="<?php echo esc_attr($user->display_name); ?>-photo" class="user-avatar">
                            <?php echo esc_html($user->display_name); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($user_total_score); ?></td>
                </tr>
                <?php
                $rank++;
            }
        } else {
            echo '<tr><td colspan="3">' . __('No users found.', 'textdomain') . '</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <?php

    // Pagination
    $total_pages = ceil($total_users / $per_page);

    // ... (The rest of the code remains the same)
}
add_shortcode('user_ranking', 'user_ranking_shortcode');

//enqueue
function enqueue_select2() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue Select2
    wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);
    wp_enqueue_script('select2');

    // Enqueue Select2 CSS
    wp_register_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css', array(), '4.1.0-rc.0');
    wp_enqueue_style('select2');
}

function my_theme_scripts() {
    enqueue_select2();
}

function my_admin_scripts() {
    enqueue_select2();
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');
add_action('admin_enqueue_scripts', 'my_admin_scripts');


// Handle the AJAX request to update user settings
function update_favorite_fighter() {
    check_ajax_referer('update-favorite-fighter-nonce', 'security');

    if (isset($_POST['user_id'], $_POST['select_id'], $_POST['selected_value'])) {
        $user_id = intval($_POST['user_id']);
        $select_id = sanitize_text_field($_POST['select_id']);
        $selected_value = intval($_POST['selected_value']);

        if (current_user_can('edit_user', $user_id)) {
            update_user_meta($user_id, $select_id, $selected_value);

            // Get fighter data
            $fighter_data = array();
            if ($selected_value) {
                preg_match('/^favorite_fighter_season_(\d+)_(\w+)$/', $select_id, $matches);
                if (!empty($matches)) {
                    $season = intval($matches[1]);
                    $grade = $matches[2];            if ($selected_value) {
                        preg_match('/^favorite_fighter_season_(\d+)_(\w+)$/', $select_id, $matches);
                        $season = $matches[1];
                        $grade = $matches[2];

                        $fighter_data = array(
                            'season' => $season,
                            'grade' => $grade,
                            'kills' => get_post_meta($selected_value, '_season' . $season . '_kills', true),
                            'match_bonus' => get_post_meta($selected_value, '_season' . $season . '_match_bonus', true),
                            'total_score' => get_post_meta($selected_value, '_season' . $season . '_total_score', true),
                        );
                    }
                }
            }

            wp_send_json(array(
                'success' => true,
                'message' => 'User favorite fighter updated successfully.',
                'user_total_score' => $user_total_score,
                'fighter_data' => $fighter_data,
            ));
        } else {
            wp_send_json_error('Insufficient permissions to edit user.');
        }
    } else {
        wp_send_json_error(array(
            'message' => 'Failed to update user favorite fighter.',
        ));    }
}
add_action('wp_ajax_update_favorite_fighter', 'update_favorite_fighter');


// Add a "Verified" checkbox to the "fighter" custom post type
// Add a checkbox to the "fighter" custom post type
add_action( 'add_meta_boxes', 'add_fighter_verified_meta_box' );
function add_fighter_verified_meta_box() {
    add_meta_box(
        'fighter_verified',
        'Verified',
        'fighter_verified_meta_box_callback',
        'fighter',
        'side',
        'default'
    );
}

function fighter_verified_meta_box_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'fighter_verified_nonce' );
    $value = get_post_meta( $post->ID, '_fighter_verified', true );
    echo '<input type="checkbox" name="fighter_verified" value="1" ' . checked( $value, 1, false ) . ' /> Verified';
}

// Save the value of the "verified" checkbox
add_action( 'save_post_fighter', 'save_fighter_verified_meta_box' );
function save_fighter_verified_meta_box( $post_id ) {
    if ( ! isset( $_POST['fighter_verified_nonce'] ) || ! wp_verify_nonce( $_POST['fighter_verified_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['fighter_verified'] ) ) {
        update_post_meta( $post_id, '_fighter_verified', 1 );
    } else {
        delete_post_meta( $post_id, '_fighter_verified' );
    }
}

// Custom function to display the title with the "data-verified" attribute
function the_custom_title() {
    global $post;
    $verified = get_post_meta( $post->ID, '_fighter_verified', true );
    $title = get_the_title();

    if ( $verified ) {
        echo '<h1 class="sc_item_title sc_title_title sc_align_center sc_item_title_style_default sc_item_title_tag verified-title">' . $title . '<span class="verified-icon fas fa-check-circle"><span class="verified-tooltip">الراجل ده ثقة وكفأة وادارة الموقع عارفاه </span></span></h1>';
    } else {
        echo '<h1 class="sc_item_title sc_title_title sc_align_center sc_item_title_style_default sc_item_title_tag">' . $title . '</h1>';
    }
}

function enqueue_font_awesome_5() {
    wp_enqueue_style('font-awesome-5-free', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome_5');
// custom function to display user tables
function display_four_tables($user, $season, $grade) {
    $favorite_fighter_key = "favorite_fighter_season_{$season}_{$grade}";
    $favorite_fighter = get_user_meta($user->ID, $favorite_fighter_key, true);

    if ($favorite_fighter) {
        $fighter_url = get_permalink($favorite_fighter);
        $thumbnail_url = get_the_post_thumbnail_url($favorite_fighter);
        $kills = get_post_meta($favorite_fighter, '_season' . $season . '_kills', true);
        $match_bonus = get_post_meta($favorite_fighter, '_season' . $season . '_match_bonus', true);
        $total_score = get_post_meta($favorite_fighter, '_season' . $season . '_total_score', true);

        // Create table row
        $row = '<tr>';
        $row .= '<td>' . $season . '</td>';
        $row .= '<td>' . ucfirst(str_replace('_', ' ', $grade)) . '</td>';

        $row .= '<td><a href="' . esc_url($fighter_url) . '">';
        $row .= get_the_title($favorite_fighter) . '</a></td>';

        $row .= '<td><a href="' . esc_url($fighter_url) . '">';
        $row .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . get_the_title($favorite_fighter) . '" style="width: 60px; height: auto;"></a></td>';

        $row .= '<td>' . esc_attr($kills) . '</td>';
        $row .= '<td>' . esc_attr($match_bonus) . '</td>';
        $row .= '<td>' . esc_attr($total_score) . '</td>';

        $row .= '</tr>';

        return array('row' => $row, 'total_score' => $total_score);
    } else {
        return array('row' => '', 'total_score' => 0); // Return empty row and 0 total score if there is no favorite fighter for this row
    }
}
//show user tables//show user tables

function display_author_tables($user_id) {
    $user = get_userdata($user_id);
    $grades = array('captain', 'grade_a', 'grade_b', 'grade_c');

    $user_total_score = 0; // Initialize user total score

    for ($season = 1; $season <= 9; $season++) {
        echo "<h3>Season $season</h3>";

        // Print table header
        echo '<table class="form-table">';
        echo '<tr><th>Season</th><th>Grade</th><th>Fighter</th><th>Thumbnail</th><th>Kills</th><th>Match Bonus</th><th>Total Score</th></tr>';

        $season_total_score = 0; // Initialize season total score
        $season_total_kills = 0; // Initialize season total kills
        $season_total_match_bonus = 0; // Initialize season total match bonus

        // Print table rows for each grade
        foreach ($grades as $grade) {
            $result = display_four_tables($user, $season, $grade);
            $score = $result['total_score'];
            $row = $result['row'];
            echo $row;
            $season_total_score += $score;
            $user_total_score += $score;

            // Update season totals
            $favorite_fighter_key = "favorite_fighter_season_{$season}_{$grade}";
            $favorite_fighter = get_user_meta($user->ID, $favorite_fighter_key, true);
            $season_total_kills += get_post_meta($favorite_fighter, '_season' . $season . '_kills', true);
            $season_total_match_bonus += get_post_meta($favorite_fighter, '_season' . $season . '_match_bonus', true);
        }

        // Print table row for season totals
        echo '<tr>';
        echo '<td colspan="2" style="text-align: right;"><strong>Season Totals:</strong></td>';
        echo '<td></td>'; // Empty cell for fighter name
        echo '<td></td>'; // Empty cell for thumbnail
        echo '<td><strong>' . $season_total_kills . '</strong></td>';
        echo '<td><strong>' . $season_total_match_bonus . '</strong></td>';
        echo '<td><strong>' . $season_total_score . '</strong></td>';
        echo '</tr>';
        // Close table
        echo '</table>';
    }

    // Display user total score
    echo "<h3>User Total Score: {$user_total_score}</h3>";

    return $user_total_score;

    // Use $user_total_score variable later in your code
}

// add a preloader
// Add preloader jQuery script
function add_preloader_scripts() {
    wp_add_inline_script('jquery-migrate', '
        jQuery(window).on("load", function() {
            jQuery(".preloader").fadeOut("slow");
        });
    ');
}
add_action('wp_enqueue_scripts', 'add_preloader_scripts');

// Add preloader HTML to the body
function add_preloader_html() {
    echo '
        <div class="preloader">
            <img src="' . get_stylesheet_directory_uri() . '/preloader.gif" alt="Preloader">
        </div>
    ';
}
add_action('wp_body_open', 'add_preloader_html');
//event functions

function fighters_grid_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'team_id' => '',
        ),
        $atts,
        'fighters_grid'
    );

    if (empty($atts['team_id'])) {
        return '<p>Invalid team ID provided.</p>';
    }

    $fighters_args = array(
        'post_type' => 'fighter',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'team',
                'field' => 'term_id',
                'terms' => $atts['team_id'],
            ),
        ),
    );

    $fighters_query = new WP_Query($fighters_args);

    if (!$fighters_query->have_posts()) {
        return '<p>No fighters found for this team.</p>';
    }

    $output = '<div class="fighters-grid vc_row">';

    while ($fighters_query->have_posts()) {
        $fighters_query->the_post();

        $thumbnail = get_the_post_thumbnail(get_the_ID(), 'thumbnail', array('class' => 'fighter-thumbnail'));
        $title = get_the_title();
        $permalink = get_permalink();

        $output .= <<<HTML
        <div class="fighter vc_col-sm-3">
            <a href="{$permalink}" class="fighter-link">
                {$thumbnail}
                <h3 class="fighter-title">{$title}</h3>
            </a>
        </div>
HTML;
    }

    wp_reset_postdata();

    $output .= '</div>';

    return $output;
}
add_shortcode('fighters_grid', 'fighters_grid_shortcode');
function display_event_fighters($event_id) {
    $teams = get_the_terms($event_id, 'team');

    if ($teams && !is_wp_error($teams)) {
        echo '<div class="vc_row">';

        foreach ($teams as $team) {
            echo '<div class="vc_col-sm-12">'; // Adjust the column size according to your design
            echo '<h2 class="vc_custom_heading">' . $team->name . '</h2>';

            echo do_shortcode('[fighters_grid team_id="' . $team->term_id . '"]');
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>No teams found for this event.</p>';
    }
}

//display teams in the front end in the tribe events plugin
// Display teams in the front end in the Tribe Events plugin
function event_fighters_after_event_meta() {
    if (get_post_type() == 'tribe_events') {
        display_event_fighters(get_the_ID());
    }
}
add_action('tribe_events_single_event_after_the_meta', 'event_fighters_after_event_meta');

function associate_team_taxonomy_with_events() {
    global $wp_taxonomies;
    $taxonomy_name = 'team';
    $post_type_name = 'tribe_events';

    if (isset($wp_taxonomies[$taxonomy_name]) && !in_array($post_type_name, $wp_taxonomies[$taxonomy_name]->object_type)) {
        $wp_taxonomies[$taxonomy_name]->object_type[] = $post_type_name;
    }
}
add_action('init', 'associate_team_taxonomy_with_events', 11);

//show shortocde in frontend
function display_favorite_fighter_field_frontend($user) {
    $grades = array('captain', 'grade_a', 'grade_b', 'grade_c');

    $user_total_score = 0;
    $user_season_kills = array_fill(1, 9, 0);
    $user_season_match_bonus = array_fill(1, 9, 0);

    for ($season = 1; $season <= 9; $season++) {
        echo "<h3>Season $season</h3>";

        if (is_current_user_administrator()) {
            $season_locked_key = "season_{$season}_locked";
            $season_locked_value = get_option($season_locked_key) === 'on';
            ?>
            <p>
                <label for="<?php echo $season_locked_key; ?>">Lock Season <?php echo $season; ?>:</label>
                <input type="checkbox" name="<?php echo $season_locked_key; ?>" id="<?php echo $season_locked_key; ?>" <?php checked($season_locked_value, true); ?> />
            </p>
            <?php
        } else {
            $season_locked_value = get_option("season_{$season}_locked") === 'on';
        }

        $disabled = $season_locked_value ? 'disabled' : '';

        foreach ($grades as $grade) {
            $args = array(
                'post_type' => 'fighter',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'grade',
                        'field' => 'slug',
                        'terms' => $grade
                    )
                )
            );
            $fighters = get_posts( $args );

            $favorite_fighter_key = "favorite_fighter_season_{$season}_{$grade}";
            $favorite_fighter = get_user_meta( $user->ID, $favorite_fighter_key, true );

            ?>
            <h4><?php echo ucfirst(str_replace('_', ' ', $grade)); ?></h4>
            <table class="form-table">
                <tr>
                    <th><label for="<?php echo $favorite_fighter_key; ?>"><?php _e( "Select a fighter:", 'my_textdomain' ); ?></label></th>
                    <td>
                        <div class="select-wrapper">
                            <div class="selected-option">
                                <?php echo $favorite_fighter ? '<img src="' . get_the_post_thumbnail_url($favorite_fighter) . '" alt="' . get_the_title($favorite_fighter) . '">' . get_the_title($favorite_fighter) . ' (' . (is_array(get_the_terms($favorite_fighter, 'team')) ? get_the_terms($favorite_fighter, 'team')[0]->name : '') . ')' : __('Select a fighter', 'my_textdomain'); ?>
                                <span class="reset-button" style="cursor: pointer;">&times;</span>
                            </div>

                            <select class="custom-select" name="<?php echo $favorite_fighter_key; ?>" id="<?php echo $favorite_fighter_key; ?>" <?php echo $disabled; ?>>
                                <option value=""><?php _e('Select a fighter', 'my_textdomain'); ?></option>
                                <?php foreach ($fighters as $fighter) : ?>
                                    <option value="<?php echo $fighter->ID; ?>" <?php selected( $favorite_fighter, $fighter->ID ); ?> data-img-src="<?php echo get_the_post_thumbnail_url($fighter); ?>" data-team="<?php echo is_array(get_the_terms($fighter, 'team')) ? get_the_terms($fighter, 'team')[0]->name : ''; ?>"><?php echo $fighter->post_title; ?> (<?php echo is_array(get_the_terms($fighter, 'team')) ? get_the_terms($fighter, 'team')[0]->name : ''; ?>)</option>

                                <?php endforeach; ?>
                            </select>
                            <div class="options-container hidden">
                                <div class="option" data-value=""><?php _e('Select a fighter', 'my_textdomain'); ?></div>
                                <?php foreach ($fighters as $fighter) : ?>
                                    <div class="option" data-value="<?php echo $fighter->ID; ?>" <?php echo $favorite_fighter == $fighter->ID ? 'style="display:none;"' : ''; ?>>
                                        <img src="<?php echo get_the_post_thumbnail_url($fighter); ?>" alt="<?php echo $fighter->post_title; ?>">
                                        <?php echo $fighter->post_title . ' (' . (is_array(get_the_terms($fighter->ID, 'team')) ? get_the_terms($fighter->ID, 'team')[0]->name : '') . ')'; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>


                    </td>
                </tr>
                <?php if ($favorite_fighter) : ?>
                    <?php
                    $kills = get_post_meta($favorite_fighter, '_season' . $season . '_kills', true);
                    $match_bonus = get_post_meta($favorite_fighter, '_season' . $season . '_match_bonus', true);
                    $total_score = get_post_meta($favorite_fighter, '_season' . $season . '_total_score', true);

                    $user_total_score += intval($total_score);
                    $user_season_kills[$season] += intval($kills);
                    $user_season_match_bonus[$season] += intval($match_bonus);
                    ?>
                    <tr class="fighter-stats">
                        <th><label for="fighter_stats"><?php _e("Stats:", 'my_textdomain'); ?></label></th>
                        <td>
                            <p><label for="kills"><?php _e('Kills', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_kills" name="season<?php echo $season; ?>_<?php echo $grade; ?>_kills" value="<?php echo esc_attr($kills); ?>" readonly /></p>

                            <p><label for="match_bonus"><?php _e('Match Bonus', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_match_bonus" name="season<?php echo $season; ?>_<?php echo $grade; ?>_match_bonus" value="<?php echo esc_attr($match_bonus); ?>" readonly /></p>

                            <p><label for="total_score"><?php _e('Total Score', 'textdomain'); ?></label><br />
                                <input type="number" id="season<?php echo $season; ?>_<?php echo $grade; ?>_total_score" name="season<?php echo $season; ?>_<?php echo $grade; ?>_total_score" value="<?php echo esc_attr($total_score); ?>" readonly /></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
            <style>.fighter-image {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                }

                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 4px 8px;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                }
                /* Fighter image styles */
                .fighter-image {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                }

                /* Dropdown menu styles */
                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-bottom: 1px solid #dee2e6;
                }

                .select2-container .select2-results__option--highlighted {
                    background-color: #007bff;
                    color: #ffffff;
                }

                /* Input field styles */
                .select2-container .select2-selection--single {
                    height: 58px;
                    display: flex;
                    align-items: center;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                    padding: 0 8px;
                }

                .select2-container .select2-selection__arrow {
                    height: 58px;
                }

                /* Adjust font size */
                .select2-container .select2-selection__rendered,
                .select2-container .select2-results__option {
                    font-size: 16px;
                }

                /* Add some padding and border-radius to the dropdown */
                .select2-dropdown {
                    border-radius: 4px;
                    padding: 8px 0;
                }

                /* Add a box-shadow to the dropdown */
                .select2-container--open .select2-dropdown {
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.06);
                }
                /* Dropdown menu styles */
                .select2-container .select2-results__option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-bottom: 1px solid #dee2e6;
                    color: #333; /* Add text color */
                    font-weight: 600; /* Add font-weight */
                }

                .select2-container .select2-results__option--highlighted {
                    background-color: #007bff;
                    color: #ffffff;
                }

                /* Input field styles */
                .select2-container .select2-selection--single {
                    height: 58px;
                    display: flex;
                    align-items: center;
                }

                .select2-container .select2-selection__rendered {
                    display: flex;
                    align-items: center;
                    padding: 0 8px;
                    color: #333; /* Add text color */
                    font-weight: 600; /* Add font-weight */
                }
                .empty-card {
                    width: 50px;
                    height: 50px;
                    border: 1px solid #ccc;
                    display: inline-block;
                    margin-right: 5px;
                }


            </style>
            <script>

                jQuery(document).ready(function() {
                    function formatFighter (fighter) {
                        if (!fighter.id) {
                            return fighter.text;
                        }
                        var imgSrc = jQuery(fighter.element).data('img-src');
                        var markup = `
        <img src="${imgSrc}" class="fighter-image" alt="${fighter.text}" />
        <span>${fighter.text}</span>
    `;
                        return markup;
                    };

                    jQuery('select[id^="favorite_fighter_season_"]').select2({
                        templateResult: formatFighter,
                        templateSelection: formatFighter,
                        escapeMarkup: function (markup) { return markup; }
                    });

                    jQuery('select[id^="favorite_fighter_season_"]').on('change', function() {
                        var selectElement = jQuery(this);
                        var selectedValue = selectElement.val();
                        var selectId = selectElement.attr('id');
                        var selectWrapper = selectElement.closest(".select-wrapper");

                        // Update the relevant elements on the page
                        var fighter = selectElement.find("option:selected");
                        var imgSrc = fighter.data("img-src");
                        var fighterName = fighter.text();
                        var fighterTeam = fighter.data("team") || '';

                        selectWrapper.find(".selected-option img").attr('src', imgSrc).attr('alt', fighterName);
                        selectWrapper.find(".selected-option span:not(.reset-button)").html(fighterName + ' (' + fighterTeam + ')');


                        // Send an AJAX request to update the user settings
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: {
                                action: 'update_favorite_fighter',
                                user_id: '<?php echo $user->ID; ?>',
                                select_id: selectId,
                                selected_value: selectedValue,
                                security: '<?php echo wp_create_nonce('update-favorite-fighter-nonce'); ?>'
                            },
                            success: function(response) {
                                console.log(response);
                                // Check if the response contains fighter data
                                if (response.hasOwnProperty('fighter_data')) {
                                    var fighterData = response.fighter_data;
                                    var season = fighterData.season;
                                    var grade = fighterData.grade;

                                    // Update the fighter stats elements with the new data
                                    jQuery('#season' + season + '_' + grade + '_kills').val(fighterData.kills);
                                    jQuery('#season' + season + '_' + grade + '_match_bonus').val(fighterData.match_bonus);
                                    jQuery('#season' + season + '_' + grade + '_total_score').val(fighterData.total_score);

                                    // Update the user's total score element with the new data
                                    jQuery('#user_total_score').val(response.user_total_score);
                                }
                            },
                        });
                    });

                    // Handle click event on the "X" button to reset the field value
// Handle click event on the "X" button to reset the field value
                    jQuery(".reset-button").click(function() {
                        var selectWrapper = jQuery(this).closest(".select-wrapper");
                        var selectElement = selectWrapper.find("select");

                        // Check if the select element is disabled (locked), and return early if it is
                        if (selectElement.prop("disabled")) {
                            return;
                        }

                        // Reset the select element value
                        selectElement.val("").trigger("change");

                        // Update the selected-option div content with an empty card
                        selectWrapper.find(".selected-option img").attr('src', '').attr('alt', '');
                        selectWrapper.find(".selected-option span:not(.reset-button)").html('<?php echo __("Select a fighter", "my_textdomain"); ?>');
                    });

                });

            </script>



            <?php
        }

        echo "<h4>Season $season Totals</h4>";
        echo '<p>Total Kills: ' . $user_season_kills[$season] . '</p>';
        echo '<p>Total Match Bonus: ' . $user_season_match_bonus[$season] . '</p>';
    }

    echo '<h3>Total Score99</h3>';
    echo '<input type="number" id="user_total_score" name="user_total_score" value="' . esc_attr($user_total_score) . '" readonly />';
}
add_shortcode('display_facorite_fighter_shortcode','display_favorite_fighter_field_frontend');

