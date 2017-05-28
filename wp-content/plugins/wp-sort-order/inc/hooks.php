<?php
/**
* Class & Method
*/
	
	$wpso = new Wpso();
	
	class Wpso
	{
		function __construct()
		{
			if ( !get_option( 'wpso_activation' ) ) $this->wpso_activation();
			
			add_action( 'plugins_loaded', array( $this, 'wpso_load_textdomain' ) );
	
			add_action( 'admin_menu', array( $this, 'admin_menu') );
			
			add_action( 'admin_init', array( $this, 'refresh' ) );
			add_action( 'admin_init', array( $this, 'update_options') );
			add_action( 'admin_init', array( $this, 'load_script_css' ) );
			
			// sortable ajax action
			add_action( 'wp_ajax_update-menu-order', array( $this, 'update_menu_order' ) );
			add_action( 'wp_ajax_update-menu-order-tags', array( $this, 'update_menu_order_tags' ) );
			add_action( 'wp_ajax_update-menu-order-users', array( $this, 'update_menu_order_users' ) );
			
			// reorder post types
			add_action( 'pre_get_posts', array( $this, 'wpso_pre_get_posts' ) );
			
			add_filter( 'get_previous_post_where', array( $this, 'wpso_previous_post_where' ) );
			add_filter( 'get_previous_post_sort', array( $this, 'wpso_previous_post_sort' ) );
			add_filter( 'get_next_post_where', array( $this, 'hocpo_next_post_where' ) );
			add_filter( 'get_next_post_sort', array( $this, 'wpso_next_post_sort' ) );
			
			// reorder taxonomies
			add_filter( 'get_terms_orderby', array( $this, 'wpso_get_terms_orderby' ), 10, 3 );
			add_filter( 'wp_get_object_terms', array( $this, 'wpso_get_object_terms' ), 10, 3 );
			add_filter( 'get_terms', array( $this, 'wpso_get_object_terms' ), 10, 3 );
			
			// reorder users
			add_action('pre_user_query', array($this, 'wpso_pre_user_query'));
		}
		
		function wpso_pre_user_query($userquery){
			
			global $wpdb, $current_screen;
			
			if ( 'users' != $current_screen->id )
			return;
		
			$vars = $userquery->query_vars;
			
			//pree($vars);
			
			$valid_tax = $this->current_tax($_GET);
			
			//pree($valid_tax);
			if($valid_tax==0)
			return;
			
			$userquery->query_from .= " LEFT JOIN {$wpdb->usermeta} m1 ON {$wpdb->users}.ID=m1.user_id AND (m1.meta_key='user_order_".$valid_tax."')"; 
			
			$userquery->query_orderby = " ORDER BY m1.meta_value ".($userquery->query_vars["order"] == "ASC" ? "asc " : "desc ");
			
			//pree($userquery);
		}
		
		function wpso_activation()
		{
			global $wpdb;
			$result = $wpdb->query( "DESCRIBE $wpdb->terms `term_order`" );
			if ( !$result ) {
				$query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
				$result = $wpdb->query( $query );
			}
	
			$result = $wpdb->query( "DESCRIBE $wpdb->users `user_order`" );
			if ( !$result ) {
				$query = "ALTER TABLE $wpdb->users ADD `user_order` INT( 4 ) NULL DEFAULT '0'";
				$result = $wpdb->query( $query );
			}
	
			update_option( 'wpso_activation', 1 );
		}
	
		function wpso_load_textdomain()
		{
			//load_plugin_textdomain( 'wp-sort-order', false, basename( dirname( __FILE__ ) ).'/languages/' );
		}
		function admin_menu()
		{
			add_options_page( __( 'WP Sort Order', 'wp-sort-order' ), __( 'WP Sort Order', 'wp-sort-order' ), 'manage_options', 'wpso-settings', array( $this,'admin_page' ) );
		}
		
		function admin_page()
		{
			require WPSO_DIR.'inc/settings.php';
		}
	
		function _check_load_script_css()
		{
			global $wpso_pro;
			$active = false;
			
			$objects = $this->get_wpso_options_objects();
			$tags = $this->get_wpso_options_tags();
			
			
			if ( empty( $objects ) && empty( $tags ) ) return false;
			
			//pree($objects);
			//pree($tags);
			
			// exclude (sorting, addnew page, edit page)
			if ( isset( $_GET['orderby'] ) || strstr( $_SERVER['REQUEST_URI'], 'action=edit' ) || strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post-new.php' ) ) return false;
			
			//pree($tags);
			
			if ( !empty( $objects ) ) {
				if ( isset( $_GET['post_type'] ) && !isset( $_GET['taxonomy'] ) && in_array( $_GET['post_type'], $objects ) ) { // if page or custom post types
					$active = true;
				}
				if ( !isset( $_GET['post_type'] ) && strstr( $_SERVER['REQUEST_URI'], 'wp-admin/edit.php' ) && in_array( 'post', $objects ) ) { // if post
					$active = true;
				}
			}
			

			
			
			//pree($active);
			
			if ( !empty( $tags ) ) {
				
				if ( isset( $_GET['taxonomy'] ) && in_array( $_GET['taxonomy'], $tags ) ) {
					$active = true;
				}
				
				if(!$active){
					foreach($tags as $tag){
						if(isset($_GET[$tag])){
							$active = true;
							continue;
						}
					}
				}
					
				if (!$active && $wpso_pro && strstr( $_SERVER['REQUEST_URI'], 'wp-admin/users.php' ) && in_array( 'usercategories', $tags ) ) { // if post
					$active = true;
				}				
			}
			
			//pree($active);
			
			return $active;
		}
	
		function load_script_css()
		{
			if ( $this->_check_load_script_css() ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'wpsojs', WPSO_URL.'/js/scripts.js', array( 'jquery' ), null, true );
				
				wp_enqueue_style( 'wpso', WPSO_URL.'/css/styles.css', array(), null );
			}
			if(is_admin()){
				wp_enqueue_style( 'wpso', WPSO_URL.'/css/admin.css', array(), null );
				wp_enqueue_script( 'wpsojs', WPSO_URL.'/js/admin.js', array( 'jquery' ), null, true );
			}
		}
		
		
				
		function refresh()
		{
			
			global $wpdb;
			$objects = $this->get_wpso_options_objects();
			$tags = $this->get_wpso_options_tags();
			
			//pree($objects);
			//pree($tags);
			
			if ( !empty( $objects ) ) {
				foreach( $objects as $object) {
					$result = $wpdb->get_results( "
						SELECT count(*) as cnt, max(menu_order) as max, min(menu_order) as min 
						FROM $wpdb->posts 
						WHERE post_type = '".$object."' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')
					" );
					if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max ) continue;
					
					$results = $wpdb->get_results( "
						SELECT ID 
						FROM $wpdb->posts 
						WHERE post_type = '".$object."' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future') 
						ORDER BY menu_order ASC
					" );
					foreach( $results as $key => $result ) {
						$wpdb->update( $wpdb->posts, array( 'menu_order' => $key+1 ), array( 'ID' => $result->ID ) );
					}
				}
			}
	
			$terms = array();
			
			if ( !empty( $tags ) ) {
				foreach( $tags as $taxonomy ) {
					$result = $wpdb->get_results( "
						SELECT count(*) as cnt, max(term_order) as max, min(term_order) as min 
						FROM $wpdb->terms AS terms 
						INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id ) 
						WHERE term_taxonomy.taxonomy = '".$taxonomy."'
					" );
					//pree($taxonomy);
					//pree($result);//exit;
					$terms[] = $taxonomy;
					$continue = ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max );
					if ($continue) continue;
					
					$results = $wpdb->get_results( "
						SELECT terms.term_id 
						FROM $wpdb->terms AS terms 
						INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id ) 
						WHERE term_taxonomy.taxonomy = '".$taxonomy."' 
						ORDER BY term_order ASC
					" );
					//pree($results);//exit;
					foreach( $results as $key => $result ) {
						$wpdb->update( $wpdb->terms, array( 'term_order' => $key+1 ), array( 'term_id' => $result->term_id ) );
					}
				}
			}
			
			if ( !empty( $terms ) ) {
				//pree($terms);
				foreach( $terms as $term ) {
					$args = array('taxonomy'=>$term,'hide_empty'=>false);
					$term_items = get_terms($args);
					//pree($term_items);
					//continue;
					if(!empty($term_items)){
						foreach( $term_items as $item ) {
							
							$this->refine_terms_relations($item->term_id);
							$item_child = get_term_children( $item->term_id, $term );
							if(!empty($item_child)){
								foreach( $item_child as $child ) {
																$this->refine_terms_relations($child);

								}
							}
							
							
						}
					}
				}
			}
			
			
			
		}
		
		function refine_terms_relations($item){
			global $wpdb;
							
							//pree($item);
							//pree('\n');
							
							//pree($item);
							//pree($item);
							//pree($taxonomy);
							//$objects = get_objects_in_term($item, $taxonomy);
							//pree($objects);
							
							$squery = "
								SELECT count(*) as cnt, max(um.meta_value) as max, min(um.meta_value) as min 
								FROM
									$wpdb->term_relationships AS r,
									$wpdb->users AS u,
									$wpdb->usermeta AS um								
								WHERE
									r.term_taxonomy_id=$item
									AND
									u.ID=r.object_id
									AND
									(um.user_id=r.object_id
									AND
									um.meta_key=CONCAT('user_order_', r.term_taxonomy_id))
							";
							//if($item==26)
							//pree($squery);
							
							$stats = $wpdb->get_results( $squery );
							
							//pree($stats);
							$cquery = "SELECT COUNT(*) FROM $wpdb->users u, $wpdb->term_relationships AS r WHERE r.term_taxonomy_id=$item AND u.ID=r.object_id";
							
							$user_count1 = $wpdb->get_var( $cquery );
							
							$cquery = "SELECT COUNT(*) FROM $wpdb->usermeta um WHERE um.meta_key=CONCAT('user_order_', $item)";

							$user_count2 = $wpdb->get_var( $cquery );							
							
							/*if($item==26){
								pree($user_count1);
								pree($user_count2);
								pree($cquery);
							}*/
							//pree($cquery);
							
							
							//pree($user_count.' > '.$stats[0]->cnt);
							//pree('<br />');
							if(($user_count1!=$user_count2)){ //|| $user_count1!=$stats[0]->cnt){
								$dquery = "
									SELECT 
										um.umeta_id,
										um.user_id
									FROM 
										$wpdb->usermeta um
									WHERE										
										um.meta_key=CONCAT('user_order_', $item)
										
								";
								//if($item==26)
								//pree($dquery);
								
								$dres = $wpdb->get_results($dquery);
								//pree($dres);
								if(!empty($dres)){
									foreach($dres as $res){
										if($res->user_id!=$res->object_id){
											$dfquery = "DELETE FROM $wpdb->usermeta WHERE umeta_id=$res->umeta_id";
											//pree($dfquery);
											$wpdb->query($dfquery);
										}else{
											//pree($res->user_id.' - '.$item.' - '.$res->umeta_id);
										}
									}
								}
							}
							
							//$stats[0]->cnt == 0 || 
							$continu = ( $stats[0]->cnt == $stats[0]->max );
							if ($continu) return;
							
							
							
							$rquery = "
								
								SELECT 
									um.user_id,
									um.meta_value
								FROM
									$wpdb->term_relationships AS r,
									$wpdb->users AS u,
									$wpdb->usermeta AS um								
								WHERE
									r.term_taxonomy_id=$item
									AND
									u.ID=r.object_id
									AND
									(um.user_id=r.object_id
									AND
									um.meta_key=CONCAT('user_order_', r.term_taxonomy_id))
									
							";
							//pree($rquery);
							$relations = $wpdb->get_results( $rquery );
							
							//pree($relations);
							
							if(!empty($relations)){
								foreach( $relations as $k => $res ) {
									$user_order = 'user_order_'.$item;
									//pree($k);
									//pree($res);
									//$wpdb->update( $wpdb->users, array( 'user_order' => $k+1 ), array( 'ID' => $res->object_id ) );
									update_user_meta(intval($res->user_id), $user_order, $k+1);
								}
							}else{
								$rquery = "
								
								SELECT 
									u.ID AS user_id
								FROM
									$wpdb->term_relationships AS r,
									$wpdb->users AS u							
								WHERE
									r.term_taxonomy_id=$item
									AND
									u.ID=r.object_id
									
							";
								//pree($rquery);
								$relations = $wpdb->get_results( $rquery );
								//pree($relations);
								
								if(!empty($relations)){
									foreach( $relations as $k => $res ) {
										$user_order = 'user_order_'.$item;
										//pree($k);
										//pree($res);
										//$wpdb->update( $wpdb->users, array( 'user_order' => $k+1 ), array( 'ID' => $res->object_id ) );
										update_user_meta(intval($res->user_id), $user_order, $k+1);
									}
								}
							}
							
									
		}
		
		function update_menu_order()
		{
			global $wpdb;
	
			parse_str( $_POST['order'], $data );
			
			if ( !is_array( $data ) ) return false;
				
			// get objects per now page
			$id_arr = array();
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					$id_arr[] = $id;
				}
			}
			
			// get menu_order of objects per now page
			$menu_order_arr = array();
			foreach( $id_arr as $key => $id ) {
				$results = $wpdb->get_results( "SELECT menu_order FROM $wpdb->posts WHERE ID = ".intval( $id ) );
				foreach( $results as $result ) {
					$menu_order_arr[] = $result->menu_order;
				}
			}
			
			// maintains key association = no
			sort( $menu_order_arr );
			
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					$wpdb->update( $wpdb->posts, array( 'menu_order' => $menu_order_arr[$position] ), array( 'ID' => intval( $id ) ) );
				}
			}
		}
		
		function update_menu_order_tags()
		{
			global $wpdb;
			
			parse_str( $_POST['order'], $data );
			
			if ( !is_array( $data ) ) return false;
			
			$id_arr = array();
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					$id_arr[] = $id;
				}
			}
			
			$menu_order_arr = array();
			foreach( $id_arr as $key => $id ) {
				$results = $wpdb->get_results( "SELECT term_order FROM $wpdb->terms WHERE term_id = ".intval( $id ) );
				foreach( $results as $result ) {
					$menu_order_arr[] = $result->term_order;
				}
			}
			sort( $menu_order_arr );
			
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					$wpdb->update( $wpdb->terms, array( 'term_order' => $menu_order_arr[$position] ), array( 'term_id' => intval( $id ) ) );
				}
			}
		}
		
		function current_tax($params){
			
			$valid_tax = 0;//array();
			if(!empty($params)){
				$tags = $this->get_wpso_options_tags();
				//pree($tags);
				//pree($params);
				foreach($params as $tax=>$term){
					
	
					
					if(in_array($tax, $tags)){
						//pree($tax);
						//pree($term);					
						$obj = get_term_by( 'slug', $term, $tax ); 
						//pree($obj);
						$valid_tax = $obj->term_id;
					}
				}
				//pree($tags);
			}	
			return $valid_tax;
		}
	
		function update_menu_order_users()
		{
			global $wpdb;
			
			parse_str( $_POST['order'], $data );
			parse_str( $_POST['referer_string'], $params);
			//pree($data);
			//pree($params);
			$valid_tax = $this->current_tax($params);
			//pree($valid_tax);
			
			if ( !is_array( $data ) ) return false;
			
			$id_arr = array();
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					$id_arr[] = $id;
				}
			}
			//pree($id_arr);
			$user_order = 'user_order_'.$valid_tax;
			//pree($user_order);
			$menu_order_arr = array();
			foreach( $id_arr as $key => $id ) {
				//$results = $wpdb->get_results( "SELECT user_order FROM $wpdb->users WHERE ID = ".intval( $id ) );
				//foreach( $results as $result ) {
					$menu_order_arr[] = get_user_meta( $id, $user_order, true ); //$result->user_order;
				//}
			}
			sort( $menu_order_arr );
			
			//pree($data);
			//pree($menu_order_arr);
			
			foreach( $data as $key => $values ) {
				foreach( $values as $position => $id ) {
					//pree($position.' - '.$id.' - '.$menu_order_arr[$position]);
					//pree($menu_order_arr[$position]);
					//$wpdb->update( $wpdb->users, array( 'user_order' => $menu_order_arr[$position] ), array( 'ID' => intval( $id ) ) );
					//pree(intval($id).' - '.$user_order.' - '.$menu_order_arr[$position]);
					update_user_meta(intval($id), $user_order, $menu_order_arr[$position]);
				}
			}
			
			
		}	
		
		
		function update_options()
		{
			global $wpdb;
			
			if ( !isset( $_POST['wpso_submit'] ) ) return false;
				
			check_admin_referer( 'nonce_wpso' );
				
			$input_options = array();
			$input_options['objects'] = isset( $_POST['objects'] ) ? $_POST['objects'] : '';
			$input_options['tags'] = isset( $_POST['tags'] ) ? $_POST['tags'] : '';
			
			update_option( 'wpso_options', $input_options );
			
			$objects = $this->get_wpso_options_objects();
			$tags = $this->get_wpso_options_tags();
			
			if ( !empty( $objects ) ) {
				foreach( $objects as $object ) {
					$result = $wpdb->get_results( "
						SELECT count(*) as cnt, max(menu_order) as max, min(menu_order) as min 
						FROM $wpdb->posts 
						WHERE post_type = '".$object."' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')
					" );
					if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max ) continue;
					
					if ( $object == 'page' ) {
						$results = $wpdb->get_results( "
							SELECT ID 
							FROM $wpdb->posts 
							WHERE post_type = '".$object."' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future') 
							ORDER BY menu_order, post_title ASC
						" );
					} else {
						$results = $wpdb->get_results( "
							SELECT ID 
							FROM $wpdb->posts 
							WHERE post_type = '".$object."' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future') 
							ORDER BY post_date DESC
						" );
					}
					foreach( $results as $key => $result ) {
						$wpdb->update( $wpdb->posts, array( 'menu_order' => $key+1 ), array( 'ID' => $result->ID ) );
					}
				}
			}
			
			if ( !empty( $tags ) ) {
				foreach( $tags as $taxonomy ) {
					$result = $wpdb->get_results( "
						SELECT count(*) as cnt, max(term_order) as max, min(term_order) as min 
						FROM $wpdb->terms AS terms 
						INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id ) 
						WHERE term_taxonomy.taxonomy = '".$taxonomy."'
					" );
					if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max ) continue;
					
					$results = $wpdb->get_results( "
						SELECT terms.term_id 
						FROM $wpdb->terms AS terms 
						INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id ) 
						WHERE term_taxonomy.taxonomy = '".$taxonomy."' 
						ORDER BY name ASC
					" );
					foreach( $results as $key => $result ) {
						$wpdb->update( $wpdb->terms, array( 'term_order' => $key+1 ), array( 'term_id' => $result->term_id ) );
					}
				}
			}
			
			wp_redirect( 'admin.php?page=wpso-settings&msg=update' );
		}
		
		function wpso_previous_post_where( $where )
		{
			global $post;
	
			$objects = $this->get_wpso_options_objects();
			if ( empty( $objects ) ) return $where;
			
			if ( isset( $post->post_type ) && in_array( $post->post_type, $objects ) ) {
				$current_menu_order = $post->menu_order;
				$where = str_replace( "p.post_date < '".$post->post_date."'", "p.menu_order > '".$current_menu_order."'", $where );
			}
			return $where;
		}
		
		function wpso_previous_post_sort( $orderby )
		{
			global $post;
			
			$objects = $this->get_wpso_options_objects();
			if ( empty( $objects ) ) return $orderby;
			
			if ( isset( $post->post_type ) && in_array( $post->post_type, $objects ) ) {
				$orderby = 'ORDER BY p.menu_order ASC LIMIT 1';
			}
			return $orderby;
		}
		
		function hocpo_next_post_where( $where )
		{
			global $post;
	
			$objects = $this->get_wpso_options_objects();
			if ( empty( $objects ) ) return $where;
			
			if ( isset( $post->post_type ) && in_array( $post->post_type, $objects ) ) {
				$current_menu_order = $post->menu_order;
				$where = str_replace( "p.post_date > '".$post->post_date."'", "p.menu_order < '".$current_menu_order."'", $where );
			}
			return $where;
		}
		
		function wpso_next_post_sort( $orderby )
		{
			global $post;
			
			$objects = $this->get_wpso_options_objects();
			if ( empty( $objects ) ) return $orderby;
			
			if ( isset( $post->post_type ) && in_array( $post->post_type, $objects ) ) {
				$orderby = 'ORDER BY p.menu_order DESC LIMIT 1';
			}
			return $orderby;
		}
		
		function wpso_pre_get_posts( $wp_query )
		{
			$objects = $this->get_wpso_options_objects();
			if ( empty( $objects ) ) return false;
			
			
			if ( is_admin() ) {
				
				
				if ( isset( $wp_query->query['post_type'] ) && !isset( $_GET['orderby'] ) ) {
					if ( in_array( $wp_query->query['post_type'], $objects ) ) {
						$wp_query->set( 'orderby', 'menu_order' );
						$wp_query->set( 'order', 'ASC' );
					}
				}
			
			/**
			* for Front End
			*/
			
			} else {
				
				$active = false;
				
				// page or custom post types
				if ( isset( $wp_query->query['post_type'] ) ) {
					// exclude array()
					if ( !is_array( $wp_query->query['post_type'] ) ) {
						if ( in_array( $wp_query->query['post_type'], $objects ) ) {
							$active = true;
						}
					}
				// post
				} else {
					if ( in_array( 'post', $objects ) ) {
						$active = true;
					}
				}
				
				if ( !$active ) return false;
				
				// get_posts()
				if ( isset( $wp_query->query['suppress_filters'] ) ) {
					if ( $wp_query->get( 'orderby' ) == 'date' )  $wp_query->set( 'orderby', 'menu_order' );
					if ( $wp_query->get( 'order' ) == 'DESC' ) $wp_query->set( 'order', 'ASC' );
				// WP_Query( contain main_query )
				} else {
					if ( !$wp_query->get( 'orderby' ) )  $wp_query->set( 'orderby', 'menu_order' );
					if ( !$wp_query->get( 'order' ) ) $wp_query->set( 'order', 'ASC' );
				}
			}
		}
		
		function wpso_get_terms_orderby( $orderby, $args )
		{
			if ( is_admin() ) return $orderby;
			
			$tags = $this->get_wpso_options_tags();
			
			if( !isset( $args['taxonomy'] ) ) return $orderby;
			
			$taxonomy = $args['taxonomy'];
			if ( !in_array( $taxonomy, $tags ) ) return $orderby;
			
			$orderby = 't.term_order';
			return $orderby;
		}
		
	
		function wpso_get_object_terms( $terms )
		{
			$tags = $this->get_wpso_options_tags();
			
			if ( is_admin() && isset( $_GET['orderby'] ) ) return $terms;
			
			foreach( $terms as $key => $term ) {
				if ( is_object( $term ) && isset( $term->taxonomy ) ) {
					$taxonomy = $term->taxonomy;
					if ( !in_array( $taxonomy, $tags ) ) return $terms;
				} else {
					return $terms;
				}
			}
			
			usort( $terms, array( $this, 'taxcmp' ) );
			return $terms;
		}
		
		function taxcmp( $a, $b )
		{
			if ( $a->term_order ==  $b->term_order ) return 0;
			return ( $a->term_order < $b->term_order ) ? -1 : 1;
		}
		
		
		function get_wpso_options_objects()
		{
			$wpso_options = get_option( 'wpso_options' ) ? get_option( 'wpso_options' ) : array();
			//pree($wpso_options);
			$objects = isset( $wpso_options['objects'] ) && is_array( $wpso_options['objects'] ) ? $wpso_options['objects'] : array();
			//pree($objects);
			return $objects;
		}
		function get_wpso_options_tags()
		{
			$wpso_options = get_option( 'wpso_options' ) ? get_option( 'wpso_options' ) : array();
			$tags = isset( $wpso_options['tags'] ) && is_array( $wpso_options['tags'] ) ? $wpso_options['tags'] : array();
			return $tags;
		}
		
	}