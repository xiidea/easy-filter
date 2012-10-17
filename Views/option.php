<div class="wrap">
    <div class="icon32" id="icon-options-general"></div>
    <h2><?php _e('Easy Filter Settings') ?></h2>

    <?php if( $option_updated ): ?>
        <div class="updated below-h2" id="message"><p><?php _e('Settings saved') ?>.</p></div>;
     <?php endif; ?>

    <form action="" method="post">
        <?php
        wp_nonce_field($plugin_domain, 'nonce_'.$plugin_domain);

        $setting_post_types = $settings['post_types'];

        if(!is_array($setting_post_types)) $setting_post_types = array();

        // define post type which we do not want to show
        $exclude = array('attachment','','revision','nav_menu_item');

        $post_types = get_post_types();
        foreach($post_types as $post_type){
            // if current post type is not include in our exclude post type
            if(!in_array($post_type, $exclude)){

                $oPostType=get_post_type_object($post_type);
                // if current post type match with save settings post type value, make it checked
                printf('<h3>%s "%s"</h3>',__('Configure Filter Option For'),$oPostType->label);
                $checked = (in_array($post_type, $setting_post_types)) ? ' checked="checked"' : '';
                $disabled = ($checked=="") ? ' disabled="disabled" ' : '';
                $hidden = ($checked=="") ? ' style="display:none" ' : '';

                $setting_filter_types= isset($settings[$post_type]['config'])?$settings[$post_type]['config']:array();
                $taxonomy_hidden = in_array('taxonomy',$setting_filter_types)? '' : ' style="display:none" ';

                $taxonomy_configuration_values="";
                $taxonomies=get_object_taxonomies($post_type);
                $taxonomies=array_diff($taxonomies,array('category','post_format'));
                foreach($taxonomies as $taxonomy) {
                    $tax_obj = get_taxonomy($taxonomy);

                    $setting_filter_taxonomies = isset($settings[$post_type]['taxonomies-details'])?$settings[$post_type]['taxonomies-details']:array();
                    $taxonomies_filter_style = isset($settings[$post_type]['style'][$taxonomy])?$settings[$post_type]['style'][$taxonomy]:"";
                    $taxonomy_configuration_values .= sprintf('<p>
                            <label>
                                <input  type="checkbox" name="%1$s[%2$s][taxonomies-details][]" value="%3$s" %5$s  />
                            %4$s &nbsp;
                            </label>
                                <label>
                                    <input %6$s type="radio" value="text" name="%1$s[%2$s][style][%3$s]">%8$s
                                </label>
                                <label>
                                    <input %7$s type="radio" value="dropdown" name="%1$s[%2$s][style][%3$s]">%9$s
                                </label>
                            </p>',
                        $plugin_domain
                        ,$post_type
                        ,$taxonomy
                        ,$tax_obj->labels->name
                        ,in_array($taxonomy , $setting_filter_taxonomies)? 'checked="checked"' : ''
                        ,$taxonomies_filter_style == 'text' ? 'checked="checked"' : ''
                        ,$taxonomies_filter_style == 'dropdown' ? 'checked="checked"' : ''
                        ,__('Text Box')
                        ,__('Dropdown')
                    );
                }


                ?>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><?php _e('Enable Custom Filter') ?></th>
                            <td>
                                        <label><input class="custom-filter-post-types" type="checkbox" name="<?php echo $plugin_domain?>[post_types][]" value="<?php echo $post_type?>"<?php echo $checked?> id="post-type<?php echo $post_type ?>" /> Show Filter Options </label>
                            </td>
                        </tr>
                        <tr id="tr-post-type-<?php echo $post_type ?>" valign="top" <?php echo $hidden ?>>
                            <th scope="row"><?php _e('Select Custom Filter Options') ?></th>
                            <td>
                                <p>
                                    <?php

                                    foreach($filter_types as $filter_type=>$filter_type_label):
                                        $category_class = ($filter_type=='category' || $filter_type=='category_ex')?" class='group_check_box $post_type' ":"";
                                        if($checked == ""){
                                            $checked_filter=($filter_type=='month' || $filter_type=='category')? ' checked="checked"' : '';
                                        }else{
                                            $checked_filter=(in_array($filter_type, $setting_filter_types)) ? ' checked="checked"' : '';
                                        }
                                        ?>
                                         <label><input class="post-filter-options" type="checkbox" name="<?php echo $plugin_domain?>[<?php echo $post_type ?>][config][]" value="<?php echo $filter_type?>" <?php echo $checked_filter.$disabled.$category_class ?> id="filter-post-type-<?php echo "{$post_type}-{$filter_type}" ?>" /> <?php echo $filter_type_label; ?>&nbsp;</label>

                                        <?php endforeach; ?>
                                </p>
                            </td>
                        </tr>
                        <?php if($taxonomy_configuration_values): ?>
                        <tr id="tr-post-type-<?php echo $post_type ?>-taxonomy" valign="top" <?php echo $taxonomy_hidden ?>>
                            <th scope="row"><?php _e('Select Taxonomy Input Style') ?></th>
                            <td>
                                <p>
                                    <label>
                                    <input rel="<?php echo $post_type ?>-selected-taxonomy" <?php echo $settings[$post_type]['taxonomy'] == 'combo' ? 'checked="checked"' : ""; ?>  type="radio" class="filter-style-selector-1" value="combo" name="<?php echo $plugin_domain?>[<?php echo $post_type ?>][taxonomy]">
                                        <?php _e('Dropdown+Text Value') ?>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input rel="<?php echo $post_type ?>-selected-taxonomy" <?php echo $settings[$post_type]['taxonomy'] == 'selective' ? 'checked="checked"' : ""; ?>  type="radio" class="filter-style-selector-2" value="selective" name="<?php echo $plugin_domain?>[<?php echo $post_type ?>][taxonomy]">
                                        <?php _e('Selective Taxonomy'); ?>
                                    </label>
                                </p>
                                <div <?php echo $settings[$post_type]['taxonomy'] == 'selective' ? '' : 'style="display:none"'; ?> id="<?php echo $post_type ?>-selected-taxonomy" >
                                     <?php echo $taxonomy_configuration_values; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php
            }
        }
        ?>
        <p class="submit">
            <input name="submit-<?php echo $plugin_domain ?>" type="submit" class="button-primary"
                   value="<?php esc_attr_e('Save Changes'); ?>"/>
        </p>

    </form>
</div>