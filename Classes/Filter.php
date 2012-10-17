<?php

namespace EzFilter\Classes;


class Filter
{

    function ConvertQuery($query, WP $WP){

        $settings =  $WP->getSettings($WP->typenow);

        if(isset($settings['style']) && $WP->hasSelectiveTextInput($settings['style'])){
            foreach($settings['style'] as $key => $type){
                if($type === 'text' && isset( $_GET[$key] ) && $_GET[$key] != ''){
                    $taxonomy = get_taxonomy($key);
                    $query->query_vars[$taxonomy->query_var] = $this->get_term_by_name($_GET[$key],$key);
                }
            }
        }else{
            $taxonomy = get_taxonomy($_GET['meta_key']);
            $query->query_vars[$taxonomy->query_var] = $this->get_term_by_name($_GET['meta_value'],$_GET['meta_key']);
        }
        return $query;
    }

    function get_term_by_name($value, $taxonomy){
        $term = get_term_by('name', $value, $taxonomy);
        return ($term) ? $term->slug : $value;
    }

    function AppendWhere( $where ) {
            if ( $_GET['date_s']!='') {
                $where .= " AND post_date >= '$_GET[date_s]'";
            }
            if ( $_GET['date_e']!='') {
                $where .= " AND post_date <= '$_GET[date_e]'";
            }
        return $where;
    }

    function CreateInputs(WP $WP){

        $typenow=$WP->typenow;

        $date_range_input = false;
        $settings=$WP->getSettings($typenow);

        if(!isset($settings['config'])){
            return false;
        }

        if($this->_isAuthorFilterNeeded($settings['config'])){
            $this->_CreateAuthorInput($WP);
        }

        if($this->_isDateFilterNeeded($settings['config'])){
            $date_range_input = true;
            $this->_CreateDateInput($WP);
        }

        if($this->_isTaxonomyFilterNeeded($settings['config'])){
            $this->_CreateTaxonomyInputs( $WP, $settings);
        }
        if($WP->removeDefaultFilter() || $date_range_input){
            printf('<span class="ex-filter" id="ex-filter-setting-input">%s</span>',json_encode($settings));
        }

        return true;
    }

    private function _isAuthorFilterNeeded($config){
        return in_array('author',$config);
    }

    private function _isDateFilterNeeded($config){
        return in_array('date_range',$config);
    }

    private function _isTaxonomyFilterNeeded($config){
            return in_array('taxonomy',$config);
    }

    private function _CreateAuthorInput(WP $WP){
        $author = (isset($_GET['author'])?$_GET['author']:0);
        wp_dropdown_users(array(
            'name' => 'author',
            'show' => 'display_name',
            'show_option_all' => __('View All Author'),
            'class' =>'ex-filter',
            'selected' =>$author
        ));
    }

    private function _CreateDateInput(WP $WP){
        $date_s = (isset($_GET['date_s'])?$_GET['date_s']:'');
        $date_e = (isset($_GET['date_e'])?$_GET['date_e']:'');
        printf(
            '<input %1$s title="Date From" placeholder="Date From" name="date_s" value="%2$s" />
             - <input %1$s title="Date To" placeholder="Date To" name="date_e" value="%3$s" />'
            ,'autocomplete="off" class="date-picker-input ex-filter" type="text"'
            , $date_s
            , $date_e
            );
    }

    private function _CreateTaxonomyInputs(WP $WP, $settings){

        $TaxonomyStyle = isset($settings['taxonomy']) ? ucfirst($settings['taxonomy']) : 'Combo';
        $functionName = "_CreateTaxonomy{$TaxonomyStyle}Input" ;

        try{
            $this->$functionName($WP,$settings);
        }catch (\Exception $e){

        }
    }

    private function _CreateTaxonomyComboInput( WP $WP, $settings) {
        $typenow=$WP->typenow;

        $meta_key = isset($_GET['meta_key'])?$_GET['meta_key']:'';
        $meta_value = isset($_GET['meta_value'])?$_GET['meta_value']:'';
        $filters = get_object_taxonomies($typenow);

        $taxonomy_type_group="";
        foreach ($filters as $tax_slug) {
            if('category' == $tax_slug && in_array('category',$settings['config'])){
                continue;
            }
            $tax_obj = get_taxonomy($tax_slug);

            $taxonomy_type_group .= sprintf('<option %3$s value="%1$s">%2$s</option>'
                                            ,$tax_slug
                                            ,$tax_obj->labels->name
                                            ,$meta_key == $tax_slug ? 'selected="selected"' : '' );
        }

        if($taxonomy_type_group!=""){
            echo "<select name='meta_key' class='ex-filter'>";
            echo "<option value=''>Select Filter By</option>";
            echo $taxonomy_type_group;
            echo "</select>";
            printf('<input autocomplete="off" class="ex-filter" title="%2$s" placeholder="%2$s" name="meta_value" type="text" value="%1$s" >',esc_attr($meta_value),__('Enter Search key'));
        }

    }

    private function _CreateTaxonomySelectiveInput( WP $WP, $settings ){
        if(!isset($settings['taxonomies-details'])){
            return false;
        }

        if(!is_array($settings['taxonomies-details'])){
            return false;
        }

        foreach($settings['taxonomies-details'] as $meta_key){
            $input_style = isset($settings['style'][$meta_key]) ? $settings['style'][$meta_key] : 'dropdown';
            $function = "_CreateSelective".ucfirst($input_style)."Input";
            $this->$function($WP, $meta_key);
        }
        return true;
    }


    private function _CreateSelectiveDropdownInput(WP $WP, $tax_slug){
        $tax_obj = get_taxonomy($tax_slug);
        $terms = get_terms($tax_slug);

        $taxonomy_type_group="";
        foreach ($terms as $term) {
            $taxonomy_type_group .= sprintf('<option value="%1$s" %2$s >%3$s</option>'
                                             , $term->slug
                                             , $_GET[$tax_obj->query_var] == $term->slug ? 'selected="selected"' : ''
                                             , $term->name
                                             );
        }
        if($taxonomy_type_group!=""){
            printf('<select title="%1$s" name="%2$s" class="ex-filter">
                    <option value="">%3$s %4$s</option>',
                                            esc_attr(__('Search By')." ".$tax_obj->labels->name)
                                            , esc_attr($tax_obj->query_var)
                                            , __('View All')
                                            , $tax_obj->labels->name);
            echo $taxonomy_type_group;
            echo "</select>";
        }
    }

    private function _CreateSelectiveTextInput(WP $WP, $tax_slug){
        $tax_obj = get_taxonomy($tax_slug);
        $meta_value = isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : '';
        printf('<input autocomplete="off" class="ex-filter" name="%1$s" type="text" value="%2$s" title="%3$s" placeholder="%3$s">'
                                                    , esc_attr($tax_slug)
                                                    , esc_attr($meta_value)
                                                    , esc_attr(__('Search By')." ".$tax_obj->labels->name));
    }

}
