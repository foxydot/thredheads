<?php 
if (!class_exists('MSDProjectCPT')) {
    class MSDProjectCPT {
        //Properties
        var $cpt = 'project';
        //Methods
        /**
        * PHP 4 Compatible Constructor
        */
        public function MSDProjectCPT(){$this->__construct();}
    
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //"Constants" setup
            $this->plugin_url = plugin_dir_url('msd-custom-cpt/msd-custom-cpt.php');
            $this->plugin_path = plugin_dir_path('msd-custom-cpt/msd-custom-cpt.php');
            //Actions
            add_action( 'init', array(&$this,'register_taxonomy_project_type') );
            add_action( 'init', array(&$this,'register_taxonomy_market_sector') );
            add_action( 'init', array(&$this,'register_cpt_project') );
            add_action('admin_head', array(&$this,'plugin_header'));
            add_action('admin_print_scripts', array(&$this,'add_admin_scripts') );
            add_action('admin_print_styles', array(&$this,'add_admin_styles') );
            add_action('admin_footer',array(&$this,'info_footer_hook') );
            // important: note the priority of 99, the js needs to be placed after tinymce loads
            add_action('admin_print_footer_scripts',array(&$this,'print_footer_scripts'),99);
            
            //Filters
            add_filter( 'pre_get_posts', array(&$this,'custom_query') );
            add_filter( 'enter_title_here', array(&$this,'change_default_title') );
        }

        function register_taxonomy_project_type(){
            
            $labels = array( 
                'name' => _x( 'Project types', 'project-types' ),
                'singular_name' => _x( 'Project type', 'project-types' ),
                'search_items' => _x( 'Search project types', 'project-types' ),
                'popular_items' => _x( 'Popular project types', 'project-types' ),
                'all_items' => _x( 'All project types', 'project-types' ),
                'parent_item' => _x( 'Parent project type', 'project-types' ),
                'parent_item_colon' => _x( 'Parent project type:', 'project-types' ),
                'edit_item' => _x( 'Edit project type', 'project-types' ),
                'update_item' => _x( 'Update project type', 'project-types' ),
                'add_new_item' => _x( 'Add new project type', 'project-types' ),
                'new_item_name' => _x( 'New project type name', 'project-types' ),
                'separate_items_with_commas' => _x( 'Separate project types with commas', 'project-types' ),
                'add_or_remove_items' => _x( 'Add or remove project types', 'project-types' ),
                'choose_from_most_used' => _x( 'Choose from the most used project types', 'project-types' ),
                'menu_name' => _x( 'Project types', 'project-types' ),
            );
        
            $args = array( 
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true, //we want a "category" style taxonomy, but may have to restrict selection via a dropdown or something.
        
                'rewrite' => array('slug'=>'project-type','with_front'=>false),
                'query_var' => true
            );
        
            register_taxonomy( 'project_type', array($this->cpt), $args );
        }

        function register_taxonomy_market_sector(){
            
            $labels = array( 
                'name' => _x( 'Market sectors', 'market-sectors' ),
                'singular_name' => _x( 'Market sector', 'market-sectors' ),
                'search_items' => _x( 'Search market sectors', 'market-sectors' ),
                'popular_items' => _x( 'Popular market sectors', 'market-sectors' ),
                'all_items' => _x( 'All market sectors', 'market-sectors' ),
                'parent_item' => _x( 'Parent market sector', 'market-sectors' ),
                'parent_item_colon' => _x( 'Parent market sector:', 'market-sectors' ),
                'edit_item' => _x( 'Edit market sector', 'market-sectors' ),
                'update_item' => _x( 'Update market sector', 'market-sectors' ),
                'add_new_item' => _x( 'Add new market sector', 'market-sectors' ),
                'new_item_name' => _x( 'New market sector name', 'market-sectors' ),
                'separate_items_with_commas' => _x( 'Separate market sectors with commas', 'market-sectors' ),
                'add_or_remove_items' => _x( 'Add or remove market sectors', 'market-sectors' ),
                'choose_from_most_used' => _x( 'Choose from the most used market sectors', 'market-sectors' ),
                'menu_name' => _x( 'Market sectors', 'market-sectors' ),
            );
        
            $args = array( 
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true, //we want a "category" style taxonomy, but may have to restrict selection via a dropdown or something.
        
                'rewrite' => array('slug'=>'market-sector','with_front'=>false),
                'query_var' => true
            );
        
            register_taxonomy( 'market_sector', array($this->cpt), $args );
        }
        
        function register_cpt_project() {
        
            $labels = array( 
                'name' => _x( 'Projects', 'project' ),
                'singular_name' => _x( 'Project', 'project' ),
                'add_new' => _x( 'Add New', 'project' ),
                'add_new_item' => _x( 'Add New Project', 'project' ),
                'edit_item' => _x( 'Edit Project', 'project' ),
                'new_item' => _x( 'New Project', 'project' ),
                'view_item' => _x( 'View Project', 'project' ),
                'search_items' => _x( 'Search Project', 'project' ),
                'not_found' => _x( 'No project found', 'project' ),
                'not_found_in_trash' => _x( 'No project found in Trash', 'project' ),
                'parent_item_colon' => _x( 'Parent Project:', 'project' ),
                'menu_name' => _x( 'Project', 'project' ),
            );
        
            $args = array( 
                'labels' => $labels,
                'hierarchical' => false,
                'description' => 'Project',
                'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
                'taxonomies' => array( 'category', 'project_type', 'market_sector' ),
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_position' => 20,
                
                'show_in_nav_menus' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => true,
                'has_archive' => true,
                'query_var' => true,
                'can_export' => true,
                'rewrite' => array('slug'=>'project','with_front'=>false),
                'capability_type' => 'post'
            );
        
            register_post_type( $this->cpt, $args );
        }
        
        function plugin_header() {
            global $post_type;
            ?>
            <?php
        }
         
        function add_admin_scripts() {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                wp_enqueue_script('media-upload');
                wp_enqueue_script('thickbox');
                wp_register_script('my-upload', plugin_dir_url(dirname(__FILE__)).'/js/msd-upload-file.js', array('jquery','media-upload','thickbox'),FALSE,TRUE);
                wp_enqueue_script('my-upload');
            }
        }
        
        function add_admin_styles() {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                wp_enqueue_style('thickbox');
                wp_enqueue_style('custom_meta_css',plugin_dir_url(dirname(__FILE__)).'/css/meta.css');
            }
        }   
            
        function print_footer_scripts()
        {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                print '<script type="text/javascript">/* <![CDATA[ */
                    jQuery(function($)
                    {
                        var i=1;
                        $(\'.customEditor textarea\').each(function(e)
                        {
                            var id = $(this).attr(\'id\');
             
                            if (!id)
                            {
                                id = \'customEditor-\' + i++;
                                $(this).attr(\'id\',id);
                            }
             
                            tinyMCE.execCommand(\'mceAddControl\', false, id);
             
                        });
                    });
                /* ]]> */</script>';
            }
        }
        function change_default_title( $title ){
            global $current_screen;
            if  ( $current_screen->post_type == $this->cpt ) {
                return __('Project Title','project');
            } else {
                return $title;
            }
        }
        
        function info_footer_hook()
        {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                ?><script type="text/javascript">
                        jQuery('#postdivrich').before(jQuery('#_contact_info_metabox'));
                    </script><?php
            }
        }
        

        function custom_query( $query ) {
            if(!is_admin()){
                $is_project = ($query->query_vars['project_type'])?TRUE:FALSE;
                if($query->is_main_query() && $query->is_search){
                    $searchterm = $query->query_vars['s'];
                    // we have to remove the "s" parameter from the query, because it will prevent the posts from being found
                    $query->query_vars['s'] = "";
                    
                    if ($searchterm != "") {
                        $query->set('meta_value',$searchterm);
                        $query->set('meta_compare','LIKE');
                    };
                    $query->set( 'post_type', array('post','page',$this->cpt) );
                    ts_data($query);
                }
                elseif( $query->is_main_query() && $query->is_archive ) {
                    $query->set( 'post_type', array('post','page',$this->cpt) );
                }
            }
        }           
  } //End Class
} //End if class exists statement