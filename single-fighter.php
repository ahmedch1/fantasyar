<?php
get_header();

while (have_posts()) {
    the_post();

    // Get fighter's grade and team
    $grades = get_the_terms(get_the_ID(), 'grade');
    $teams = get_the_terms(get_the_ID(), 'team');
    $grade = $grades ? $grades[0]->name : '';
    $team = $teams ? $teams[0]->name : '';

    // Get taxonomy URLs
    $grade_url = $grades ? get_term_link($grades[0]) : '';
    $team_url = $teams ? get_term_link($teams[0]) : '';

    // Get featured image URL
    $featured_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

    // Get fighter's scores
    $stats = [];
    $total_shots = 0;
    $total_match_bonus = 0;
    $total_score = 0;

    for ($season = 1; $season <= 9; $season++) {
        $kills = get_post_meta(get_the_ID(), '_season' . $season . '_kills', true);
        $match_bonus = get_post_meta(get_the_ID(), '_season' . $season . '_match_bonus', true);

        $total_shots += intval($kills);
        $total_match_bonus += intval($match_bonus);
        $total_score += intval($kills) + intval($match_bonus);
    }
    ?>
    <div class="page_content_wrap">




        <div class="content">


            <article id="post-5495" class="post_item_single post_type_page post-5495 page type-page status-publish hentry">


                <div class="post_content entry-content">
                    <section data-vc-parallax="1.5" data-vc-parallax-o-fade="on" data-vc-parallax-image="https://ba7aar.live/fan/wp-content/uploads/2023/03/bac.jpg" class="vc_section vc_section-has-fill vc_row-o-full-height vc_section-o-content-middle vc_section-flex vc_general vc_parallax vc_parallax-content-moving-fade js-vc_parallax-o-fade" style="min-height: 70.8174vh;"><div class="vc_row wpb_row vc_row-fluid vc_rtl-columns-reverse vc_row-o-content-middle vc_row-flex skrollable skrollable-before" data-5p-top-bottom="opacity:0;" data-30p-top-bottom="opacity:1;" style="opacity: 1;"><div class="wpb_column vc_column_container vc_col-sm-2 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"></div></div></div><div class="wpb_column vc_column_container vc_col-sm-8 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"><div class="vc_row wpb_row vc_inner vc_row-fluid"><div class="wpb_column vc_column_container vc_col-sm-4 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper">
                                                        <div class="wpb_single_image wpb_content_element vc_align_center">

                                                            <figure class="wpb_wrapper vc_figure">
                                                                <div class="vc_single_image-wrapper vc_box_shadow_circle  vc_box_border_grey"><img width="150" height="150" src="<?php echo esc_url($featured_image_url); ?>" class="vc_single_image-img attachment-thumbnail" alt="<?php the_title(); ?>" decoding="async" loading="lazy" title="<?php the_title(); ?> srcset="<?php echo esc_url($featured_image_url); ?> 300w" sizes="(max-width: 150px) 100vw, 150px"></div>
                                                            </figure>
                                                        </div>
                                                        <div id="sc_title_74403438" class="sc_title color_style_default scheme_dark sc_title_default  vc_custom_1679073802751"><?php the_custom_title(); ?></div><!-- /.sc_title --><div class="vc_empty_space  height_tiny" style="height: 10px"><span class="vc_empty_space_inner"></span></div><div class="vc_message_box vc_message_box-standard vc_message_box-rounded vc_color-sky"><div class="vc_message_box-icon"><i class="icon-users-group"></i></div><p><strong>الفريق: <a href="<a href="<?php echo esc_url($team_url); ?>"><?php echo esc_html($team); ?></a></strong></p>
                                                        </div><div class="vc_message_box vc_message_box-standard vc_message_box-rounded vc_color-pink"><div class="vc_message_box-icon"><i class="fas fa-bullseye"></i></div><p><strong>الرتبة: <a href="<?php echo esc_url($grade_url); ?>"><?php echo esc_html($grade); ?></a></strong></p>
                                                        </div></div></div></div><div class="wpb_column vc_column_container vc_col-sm-8 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"><div id="sc_title_2099922542" class="sc_title color_style_dark scheme_dark sc_title_default"><h2 class="sc_item_title sc_title_title sc_align_center sc_item_title_style_default sc_item_title_tag">احصائيات اللاعب</h2></div><!-- /.sc_title --><div class="vc_empty_space  height_tiny" style="height: 50px"><span class="vc_empty_space_inner"></span></div><div id="fighterskills" class="sc_skills sc_skills_counter" data-type="counter"><div class="sc_skills_columns sc_item_columns trx_addons_columns_wrap columns_padding_bottom"><div class="sc_skills_column trx_addons_column-1_3"><div class="sc_skills_item_wrap"><div class="sc_skills_item inited"><div class="sc_skills_icon icon-user-times"></div><div class="sc_skills_total skrollable skrollable-between" data-start="0" data-stop="<?php echo $total_shots; ?>" data-step="1" data-max="<?php echo $total_shots; ?>" data-speed="33" data-duration="429" data-ed="" style="color: #dd3333"><?php echo $total_shots; ?></div></div><div class="sc_skills_item_title">كيل</div></div></div><div class="sc_skills_column trx_addons_column-1_3"><div class="sc_skills_item_wrap"><div class="sc_skills_item inited"><div class="sc_skills_icon icon-flag"></div><div class="sc_skills_total skrollable skrollable-between" data-start="0" data-stop="<?php echo $total_match_bonus; ?>" data-step="1" data-max="<?php echo $total_match_bonus; ?>" data-speed="33" data-duration="759" data-ed="" style="color: #dd3333"><?php echo $total_match_bonus; ?></div></div><div class="sc_skills_item_title">بونس ربح المبارة</div></div></div><div class="sc_skills_column trx_addons_column-1_3"><div class="sc_skills_item_wrap"><div class="sc_skills_item inited"><div class="sc_skills_icon icon-gamepad"></div><div class="sc_skills_total skrollable skrollable-between" data-start="0" data-stop="<?php echo $total_score; ?>" data-step="1" data-max="<?php echo $total_score; ?>" data-speed="13" data-duration="416" data-ed="" style="color: #dd3333"><?php echo $total_score; ?></div></div><div class="sc_skills_item_title">مجموع النقاط</div></div></div></div></div><a href="/my-account" id="sc_button_621011098" class="sc_button color_style_dark sc_button_default sc_button_size_normal sc_button_with_icon sc_button_icon_right"><span class="sc_button_icon"><span class="icon-plus"></span></span><span class="sc_button_text"><span class="sc_button_title">اضف هذا اللاعب إلى تشكيلتك</span></span><!-- /.sc_button_text --></a><!-- /.sc_button --></div></div></div></div></div></div></div><div class="wpb_column vc_column_container vc_col-sm-2 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"></div></div></div></div><div class="vc_parallax-inner skrollable skrollable-between" data-bottom-top="top: -50%;" data-top-bottom="top: 0%;" style="height: 150%; background-image: url(&quot;https://ba7aar.live/fan/wp-content/uploads/2023/03/bac.jpg&quot;); top: -36.7083%;"></div></section><div class="vc_row wpb_row vc_row-fluid"><div class="wpb_column vc_column_container vc_col-sm-4 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"></div></div></div><div class="wpb_column vc_column_container vc_col-sm-4 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"><div id="sc_title_1087868082" class="sc_title color_style_default sc_title_default  vc_custom_1679077859095"><h2 class="sc_item_title sc_title_title sc_align_center sc_item_title_style_default sc_item_title_tag">احصائيات اللاعب</h2><div class="sc_item_descr sc_title_descr sc_align_center"><p>احصائيات هذا اللاعب خلال كل الاسابيع</p>
                                        </div></div><!-- /.sc_title --></div></div></div><div class="wpb_column vc_column_container vc_col-sm-4 sc_layouts_column_icons_position_left"><div class="vc_column-inner"><div class="wpb_wrapper"></div></div>
                        </div></div>
                    <table style="margin: auto; width: 80%;">
                        <tr>
                            <th>الاسبوع</th>
                            <th>الكيلات</th>
                            <th>بونس ربح المباراة</th>
                            <th>مجموع النقاط</th>
                        </tr>
                        <?php for ($season = 1; $season <= 9; $season++) : ?>
                            <tr>
                                <td><?php echo $season; ?></td>
                                <td><?php echo get_post_meta(get_the_ID(), '_season' . $season . '_kills', true); ?></td>
                                <td><?php echo get_post_meta(get_the_ID(), '_season' . $season . '_match_bonus', true); ?></td>
                                <td><?php echo get_post_meta(get_the_ID(), '_season' . $season . '_total_score', true); ?></td>
                            </tr>
                        <?php endfor; ?>
                        <tr>
                            <td>المجموع</td>
                            <td><?php echo $total_shots; ?></td>
                            <td><?php echo $total_match_bonus; ?></td>
                            <td><?php echo $total_score; ?></td>
                        </tr>
                    </table>
                </div><!-- .entry-content -->


            </article>

        </div><!-- </.content> -->

    </div>


    <?php
}

get_footer();
