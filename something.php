<?php
/*
Plugin Name:Custom Post Type
Author:Smart Coder
Description:Custom Post Type
 */
add_action('init', 'codex_Movies_init');

function codex_Movies_init()
{
    $labels = array(
        'name'               => _x('Movies', 'post type general name', 'your-plugin-textdomain'),
        'singular_name'      => _x('Movies', 'post type singular name', 'your-plugin-textdomain'),
        'menu_name'          => _x('Movies', 'admin menu', 'your-plugin-textdomain'),
        'name_admin_bar'     => _x('Movies', 'add new on admin bar', 'your-plugin-textdomain'),
        'add_new'            => _x('Add New', 'Movies', 'your-plugin-textdomain'),
        'add_new_item'       => __('Add New Movies', 'your-plugin-textdomain'),
        'new_item'           => __('New Movies', 'your-plugin-textdomain'),
        'edit_item'          => __('Edit Movies', 'your-plugin-textdomain'),
        'view_item'          => __('View Movies', 'your-plugin-textdomain'),
        'all_items'          => __('All Movies', 'your-plugin-textdomain'),
        'search_items'       => __('Search Movies', 'your-plugin-textdomain'),
        'parent_item_colon'  => __('Parent Movies:', 'your-plugin-textdomain'),
        'not_found'          => __('No Movies found.', 'your-plugin-textdomain'),
        'not_found_in_trash' => __('No Movies found in Trash.', 'your-plugin-textdomain')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('Description.', 'your-plugin-textdomain'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'Movies'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor','thumbnail')
    );

    register_post_type('movies', $args);
}
    function wpdocs_register_meta_boxes()
    {
        add_meta_box('movie-id', 'Name', 'wpdocs_my_display_callback', 'movies','side','high');
        add_meta_box('author-id', 'Chose Author', 'wpdocs_my_display_callback_author', 'movies','side','high');
    }
    add_action('add_meta_boxes', 'wpdocs_register_meta_boxes');

    function wpdocs_my_display_callback($post){
        $name = get_post_meta($post->ID, 'movies_name',true);
        $email = get_post_meta($post->ID, 'movies_email',true);
        ?>
        <label for="">Name:</label>
        <input type="text" name="name" value="<?php echo $name;?>">
         <label for="">Email:</label>
        <input type="text" name="email" value="<?php echo $email; ?>">
        <?php
    }
    function data_save($post_id,$post){
       
        $name = $_POST['name'];
        $email = $_POST['email'];
        update_post_meta($post_id,'movies_name', $name);
        update_post_meta($post_id,'movies_email', $email);

    }
    add_action('save_post','data_save',10,2);

    function custom_column($colum){
        $colum = array(
            "cb"    => "<input type='checkbox'/>",
            'title' => 'Movies Title',
            'name'  => 'Name',
            'email' => 'Email',
            'date'  => 'Date',
        );

        return $colum;
    }
    add_action('manage_movies_posts_columns','custom_column');

    function show_custom_column($colum,$post_id){
        switch($colum){
            case 'name':
                $name = get_post_meta($post_id, 'movies_name', true);
                echo $name;
                break;
            case 'email':
                $email = get_post_meta($post_id, 'movies_email', true);
                echo $email;
                break;

        }
    }
    add_action('manage_movies_posts_custom_column','show_custom_column',10,2);

    add_filter('manage_edit-movies_sortable_columns',function($colum){
        $colum['name']  = 'name';
        $colum['email'] = 'email';
        return $colum;
    });

    function wpdocs_my_display_callback_author($post){
        ?>
        <select name="author" id="">
            <?php 
            $author_select = get_post_meta($post->ID, 'author_select',true);
            $author = get_users( array('role'=>'author') );
            foreach($author as $item):
                $select = '';
                if($author_select == $item->ID){
                    $select = 'selected = "selected"';
                }
            ?>
            <option value="<?php echo $item->ID;?>" <?php echo $select;?>><?php echo $item->display_name;?></option>
            <?php endforeach;?>
            
        </select>
        <?php
    }

    add_action('save_post','author_save_data',10,2);
    function author_save_data($post_id,$post){
        $author = $_POST['author'];
        update_post_meta($post_id,'author_select', $author);
    }

    add_action('restrict_manage_posts','author_filter');
    function author_filter(){
        global $typenow;
        if($typenow == 'movies'){
            $author_id = $_GET['author_filter'];
            $args = array(
                'role'             => 'author',
                'show_option_none' => 'Select Author',
                'name'             => 'author_filter',
                'id'               => 'all_author_fileter',   // integer
                'selected'         => $author_id,
            );
            wp_dropdown_users ($args);
        }
    }
    add_filter( 'parse_query','filter_by_author' );
    function filter_by_author($query){
        global $typenow;
        global $pagenow;
        $author_id= $_GET['author_filter'];
        if($typenow == 'movies' && $pagenow == 'edit.php' && !empty($author_id)){
            $query->query_vars["meta_key"]   = 'author_select';
            $query->query_vars["meta_value"] = $author_id;

        }
    }
    add_action('init', 'create_movies_tax');

    function create_movies_tax()
    {
        register_taxonomy(
            'movie_category',
            'movies',
            array(
                'label' => __('Movie Category'),
                'rewrite' => array('slug' => 'movie_category'),
                'hierarchical' => true,
            )
        );
    }

    add_action('restrict_manage_posts','category_filter');
    function category_filter(){
        global $typenow;
        $show_taxonomy = 'movie_category';
        $selected_movie_category_id = isset($_GET[$show_taxonomy]) ? intval($_GET[$show_taxonomy]) : "";

        if($typenow == 'movies'){
            $args = array(
                'show_option_all' => 'Show all',
                'taxonomy' => $show_taxonomy,
                'show_count' => true,
                'name'=> $show_taxonomy,
                'selected' => $selected_movie_category_id,
            );
            wp_dropdown_categories($args);
        }
    }

    add_filter('parse_query', 'filter_by_category');
    function filter_by_category($query){
        global $typenow;
        global $pagenow;
        $post_type = 'movies';
        $taxonomy= 'movie_category';
        $query_var = &$query->query_vars;
        //print_r($query_var);

        if($typenow == $post_type && $pagenow == 'edit.php' && isset($query_var[$taxonomy]) && is_numeric($query_var[$taxonomy])){
            $term_detals= get_term_by("id", $query_var[$taxonomy], $taxonomy);
            $query_var[$taxonomy] = $term_detals->slug;
        }

        

       
        
    }




