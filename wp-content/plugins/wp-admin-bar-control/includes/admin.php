<?php
  /* Multiplugin functions */
  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  if(!function_exists('wp_get_current_user'))
    include(ABSPATH . "wp-includes/pluggable.php");

  /* Красивая функция вывода масивов */
  if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; } }

  if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'yummi' && !function_exists('yummi_register_settings') || isset($_REQUEST['page']) && $_REQUEST['page'] == 'yabp' && !function_exists('yummi_register_settings') ){ /* Filter pages */
    add_action( 'admin_init', 'yummi_register_settings' );
    function yummi_register_settings() {
      $url = plugin_dir_url( __FILE__ );
      register_setting( 'wpabc_admin_menu', 'yabp', 'wpabc_validate_options' );
      wp_enqueue_style( 'yummi-hint', $url . '/css/hint.min.css' );

      if ( !current_user_can('manage_options') )
        wp_die(__('Sorry, you are not allowed to install plugins on this site.'));
    }
  }

  add_action('admin_menu', 'wpabc_admin_menu');
  function wpabc_admin_menu() {
    if( empty( $GLOBALS['admin_page_hooks']['yummi']) )
      add_menu_page( 'yummi', 'Yummi Plugins', 'manage_options', 'yummi', 'yummi_plugins_yabp', WPABC_URL.'/includes/img/dashicons-yummi.png' );

    /*add_submenu_page( parent_slug, page_title, menu_title, rights(user can manage_options), menu_slug, function ); */
    add_submenu_page('yummi', __('Admin Bar Control', 'yabp'), __('Admin Bar Control', 'yabp'), 'manage_options', 'yabp', 'wpabc_options_page');
  }

  function yummi_plugins_yabp() { if(!function_exists('yummi_plugins')) include_once( WPABC_PATH . '/includes/yummi-plugins.php' ); }
  /* /Multiplugin functions */

  // Function to generate options page
  function wpabc_options_page() {
  	global $yabp;

    $yabp = array(
       'hideBar' => 'no'
      ,'hideBarWPAdmin' => 0
      ,'remove' => array()
      ,'barColor' => '#23282d'
      ,'style' => 'group'
      ,'hidePlugins' => array()
      ,'custom' => array()
      //,'mcss' => ''
    	//,'css' => ''
    );
    //update_option("yabp", $yabp);

    #Get option values
    $yabp = get_option( 'yabp', $yabp );

    //prr($yabp);

    #Get new updated option values, and save them
    if( @$_POST['action'] == 'update' ) {

      check_admin_referer('update-options-yabp');

      $yabp = array( //(int)$_POST[yabp] //sanitize_text_field($_POST[yabp])
        //Валидация данных https://codex.wordpress.org/%D0%92%D0%B0%D0%BB%D0%B8%D0%B4%D0%B0%D1%86%D0%B8%D1%8F_%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85
         'hideBar' => $_POST['hideBar']
        ,'hideBarWPAdmin' => !empty($_POST['hideBarWPAdmin']) ?$_POST['hideBarWPAdmin'] : ''
        ,'remove' => $_POST['remove']
        ,'barColor' => !empty($_POST['barColor']) ? $_POST['barColor'] : '#23282d'
        ,'style' => $_POST['style']
        ,'hidePlugins' => $_POST['hidePlugins']
        ,'custom' => !empty($_POST['custom']) ? $_POST['custom'] : ''
        //,'mcss' => $_POST['mcss']
        //,'css' => !empty($_POST['css']) ? $_POST['css'] : '' //textarea .ab-item { display: none; }
      );
      update_option("yabp", $yabp);
      echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved.').'</strong></p></div><script type="text/javascript">document.location.reload(true);</script>';//<script type="text/javascript">document.location.reload(true);</script>
    }

    function wpabc_validate_options( $input ) {
    	global $yabp;

    	$settings = get_option( 'yabp', $yabp );

      $input['hideBar'] = wp_filter_nohtml_kses( $input['hideBar'] );
      $input['hideBarWPAdmin'] = wp_filter_nohtml_kses( $input['hideBarWPAdmin'] );
      $input['remove'] = wp_filter_nohtml_kses( $input['remove'] );
      $input['barColor'] = wp_filter_nohtml_kses( $input['barColor'] );
      $input['style'] = wp_filter_post_kses( $input['style'] );
      $input['hidePlugins'] = wp_filter_nohtml_kses( $input['hidePlugins'] );
      $input['custom'] = wp_filter_post_kses( $input['custom'] );

    	return $input;
    }

    global $wp_version;
    $isOldWP = floatval($wp_version) < 2.5;

    $beforeRow = $isOldWP ? "<p>" : '<tr valign="top"><th scope="row">';
    $betweenRow = $isOldWP ? "" : '</th><td>';
    $afterRow = $isOldWP ? "</p>" : '</td><tr>';

    //prr($_POST);
    // if ( false !== $_REQUEST['updated'] ) echo '<div class="updated fade"><p><strong>'.__( 'Options saved' ).'</strong></p></div>'; // If the form has just been submitted, this shows the notification ?>

  	<div class="wrap">
      <?php screen_icon(); echo "<h1>" . __('Admin Bar Control', 'yabp') .' '. __( 'Settings' ) . "</h1>"; ?>

    	<form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">

      	<?php
        if(function_exists('wp_nonce_field'))
          wp_nonce_field('update-options-yabp');

          if (get_bloginfo('version') >= 3.5){
      			wp_enqueue_script('wp-color-picker');
      			wp_enqueue_style('wp-color-picker');
        	} ?>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="yabp" />
        <p class="submit">
          <input type="submit" name="Submit" class="button-primary yabpsave" value="<?php _e('Save Changes') ?>" />
        </p>
        <span id="log"></span>

        <?php if(!$isOldWP)
          echo "<table class='form-table'>"; ?>


        <?php echo $beforeRow ?>
          <label for="hideBar-no"><?php _e('Hide Admin Bar for', 'yabp')?>:</label>
        <?php echo $betweenRow ?>
          <input type="checkbox" name="hideBarWPAdmin" id="hideBarWPAdmin" value="1" <?php checked( $yabp['hideBarWPAdmin'], 1 ); ?>> <label for="hideBarWPAdmin"><?php _e('Admin page', 'yabp') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-all" value="all" <?php checked( $yabp['hideBar'], 'all' ); ?>> <label for="hideBar-all"><?php _e('Admins and Users', 'yabp') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-user" value="user" <?php checked( $yabp['hideBar'], 'user' ); ?>> <label for="hideBar-user"><?php _e('Users only', 'yabp') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-no" value="no" <?php checked( $yabp['hideBar'], 'no' ); ?>> <label for="hideBar-no"><?php _e('No one', 'yabp') ?></label><br>
        <?php echo $afterRow ?>


        <?php echo $beforeRow ?>
          <label for="remove"><?php _e('Remove from Bar', 'yabp')?>:</label>
        <?php echo $betweenRow ?>
          <input type="checkbox" name="remove[wplogo]" id="remove_WPLogo" value="hide" <?php if(!empty($yabp['remove']['wplogo'])) checked( $yabp['remove']['wplogo'], 'hide' ); ?>> <label for="remove_WPLogo"><?php _e('WP Logo', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[sitename]" id="remove_sitename" value="hide" <?php if(!empty($yabp['remove']['sitename'])) checked( $yabp['remove']['sitename'], 'hide' ); ?>> <label for="remove_sitename"><?php _e('Site name', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[updates]" id="remove_updates" value="hide" <?php if(!empty($yabp['remove']['updates'])) checked( $yabp['remove']['updates'], 'hide' ); ?>> <label for="remove_updates"><?php _e('Updates', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[comments]" id="remove_comments" value="hide" <?php if(!empty($yabp['remove']['comments'])) checked( $yabp['remove']['comments'], 'hide' ); ?>> <label for="remove_comments"><?php _e('Comments', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[newcontent]" id="remove_newContent" value="hide" <?php if(!empty($yabp['remove']['newcontent'])) checked( $yabp['remove']['newcontent'], 'hide' ); ?>> <label for="remove_newContent"><?php _e('New Content', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[newlink]" id="remove_newLink" value="hide" <?php if(!empty($yabp['remove']['newlink'])) checked( $yabp['remove']['newlink'], 'hide' ); ?>> <label for="remove_newLink"><?php _e('New Link', 'yabp') ?></label><br>
          <input type="checkbox" name="remove[myaccount]" id="remove_myaccount" value="hide" <?php if(!empty($yabp['remove']['myaccount'])) checked( $yabp['remove']['myaccount'], 'hide' ); ?>> <label for="remove_newLink"><?php _e('My Account', 'yabp') ?></label><br>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <label for="barColor"><?php _e('Auxiliary Bar color', 'yabp'); ?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="barColor" id="barColor" value="<?php echo $yabp['barColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              $('#barColor').wpColorPicker();
            });</script>
        <?php echo $afterRow ?>


        <?php echo $beforeRow ?>
          <label for="style-group"><?php _e('Plugins group style', 'yabp')?>:</label>
        <?php echo $betweenRow ?>
          <input type="radio" name="style" id="style-group" value="group" <?php checked( $yabp['style'], 'group' ); ?>> <label for="style-group"><?php _e('Group', 'yabp') ?></label><br>
          <input type="radio" name="style" id="style-inline" value="inline" <?php checked( $yabp['style'], 'inline' ); ?>> <label for="style-inline"><?php _e('InLine', 'yabp') ?></label><br>
        <?php echo $afterRow ?>


        <?php
        function endKey($array){
          end($array);
          return key($array);
        }
        $i = is_array($yabp['custom']) ? endKey($yabp['custom']) : 0;

        if( is_array($yabp['custom']) ){
          foreach ($yabp['custom'] as $key => $value): ?>

            <?php echo $beforeRow ?>
              <label for="custom[<?php echo $key?>]"><?php echo __('Custom Link', 'yabp').' '.$key?></label>
            <?php echo $betweenRow ?>
              <input id="custom[<?php echo $key?>]['title']" name="custom[<?php echo $key?>][title]" type="text" value="<?php echo $value[title] ?>" placeholder="<?php _e('Title', 'yabp')?>"/>
              <input id="custom[<?php echo $key?>]['icon']" name="custom[<?php echo $key?>][icon]" type="text" value="<?php echo $value[icon] ?>" placeholder="<?php _e('Icon', 'yabp')?>"/>
              <input id="custom[<?php echo $key?>]['link']" name="custom[<?php echo $key?>][link]" type="text" value="<?php echo $value[link] ?>" placeholder="<?php _e('Link', 'yabp')?>"/>
              <span class="del">✖</span>
            <?php echo $afterRow ?>

          <?php endforeach;
        }?>

        <?php echo $beforeRow ?>
          <?php _e('Add Custom Link', 'yabp') ?>:
        <?php echo $betweenRow ?>
          <div id="custom"></div>
          <?php echo '<label id="add">✚ '.__('Add Custom Link', 'yabp').'</label>' ?>
          <script type="text/javascript">
            var i=<?php echo $i?>;
            jQuery('#add').on('click', function(){
              i++; //<?php echo $beforeRow ?><?php echo $betweenRow ?><label for="custom['+i+']"><?php _e('Custom Link', 'yabp')?> '+i+'</label>
              jQuery('<?php echo $beforeRow ?><input id="custom['+i+'][title]" name="custom['+i+'][title]" type="text" value="" placeholder="<?php _e("Title", "yabp")?>"/><input id="custom['+i+'][icon]" name="custom['+i+'][icon]" type="text" value="" placeholder="<?php _e("Icon", "yabp")?>"/><input id="custom['+i+'][link]" name="custom['+i+'][link]" type="text" value="" placeholder="<?php _e("Link", "yabp")?>"/> <span class="del">✖</span><?php echo $afterRow ?>').appendTo( "#custom" );
            });
            jQuery('.del').on('click', function(){
              jQuery(this).parent().parent().remove();
            });
          </script>

        <?php echo $afterRow ?>


        <?php echo $beforeRow ?>
          <label for="hidePlugins">
            <?php _e('What plugins Hide', 'yabp')?>:<br/>
            ✔ - <?php _e('Active Plugin', 'yabp')?><br/>
            <span style="opacity:.3">✖</span> - <?php _e('Not Active Plugin', 'yabp')?><br/><br/>
            <input type="button" class="button" value="<?php _e('Check All','yabp')?>" onclick="jQuery('.hidePlugin').attr('checked', true);"/>
            <input type="button" class="button" value="<?php _e('UnCheck All','yabp')?>" onclick="jQuery('.hidePlugin').attr('checked', false);"/><br/><br/>
            <input type="button" class="button" value="<?php _e('Inverse Check','yabp')?>" onclick="jQuery('input.hidePlugin').each(function(){ jQuery(this).is(':checked') ? jQuery(this).removeAttr('checked') : jQuery(this).attr('checked','checked'); });"/>
          </label>

        <?php echo $betweenRow ?>
           <?php
           if ( ! function_exists( 'get_plugins' ) )
             require_once ABSPATH . 'wp-admin/includes/plugin.php';

           $all_plugins = get_plugins(); //error_log( print_r( $all_plugins, true ) );

           foreach ($all_plugins as $url => $plugin) {
             // ✔ ✓ ☐ ☑ ☒ ◉ ○ ✖
             //prr($yabp['hidePlugins'][$plugin['TextDomain']]);
             $hide = empty($yabp['hidePlugins'][$plugin['TextDomain']]) ? '' : $yabp['hidePlugins'][$plugin['TextDomain']];
            echo is_plugin_active($url) ? '✔' : '<span style="opacity:.3">✖</span>';
            echo ' <input type="checkbox" id="'.$plugin['TextDomain'].'" class="hidePlugin noactive" name="hidePlugins['.$plugin['TextDomain'].']" value="'.$plugin['TextDomain'].'" '.checked( $hide, $plugin['TextDomain'], false).' "/> <label for="'.$plugin['TextDomain'].'">'.$plugin['Name'].'</label><br>';
           }
           ?>
        <?php echo $afterRow ?>

        <?php /*echo $beforeRow ?>
          <?php _e('Custom Css', 'yabp')?></label>
        <?php echo $betweenRow ?>
           <textarea id="css" name="css" rows="5" cols="30"><?php echo stripslashes($yabp['css']); ?></textarea>
        <?php echo $afterRow*/ ?>


        <?php if(!$isOldWP)
            echo "</table>"; ?>

        <p class="submit">
          <input type="submit" name="Submit" class="button-primary yabpsave" value="<?php _e('Save Changes') ?>" />
        </p>

    	</form>

  	</div>

    <!-- <h3><?php _e('Installation codes', 'yabp') ?>:</h3>
    <p>
      <h4>[add_bookmark]</h4>
      <strong><?php _e('Extended', 'yabp') ?></strong>: [yabp post_types=post,recipes post_types_num=4 customnames=intro customfields=intro_name]<br/>
      <.?php _e('Where \'post_types\' can be all your Post Type, \'post_types_num\' is number of posts in Post Types to show, \'customnames\' can contain custom fields names, \'customfields\' can contain custom fields.', 'yabp') ?><br/>

      <h4>[booknarks]</h4>

      <small><?php _e('Put one of this shortcode to your pages.', 'yabp') ?></small>
    </p>
    <em>- <?php _e('or','yabp'); ?> -</em>
    <p>
      <h4>&lt;?php echo do_shortcode('[add_bookmark]') ?&gt;</h4>
      <h4>&lt;?php echo do_shortcode('[booknarks]') ?&gt;</h4>
      <small><?php _e('Put one of this code to your template files', 'yabp') ?>: <?php _e('Appearance') ?> &gt; <?php _e('Editor') ?></small>
    </p> -->

  	<?php
  }



/* Code */
  function na_action_link( $plugin, $action = 'activate' ) {

  	if ( strpos( $plugin, '/' ) ) {
  		$plugin = str_replace( '\/', '%2F', $plugin );
  	}
  	$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
  	$_REQUEST['plugin'] = $plugin;
  	$url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
  	return $url;
  }

  if( current_user_can( 'manage_options' ) )
    add_action( 'admin_bar_menu', 'all_plugins', 999 );

  function all_plugins( $wp_admin_bar ) {
    global $yabp;

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    if ( ! function_exists( 'get_plugins' ) )
      require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $all_plugins = get_plugins(); //error_log( print_r( $all_plugins, true ) );

    empty($yabp['hidePlugins']) ? $yabp['hidePlugins'] = [] : $yabp['hidePlugins'];

    if( $yabp['style'] == 'group' || !isset($yabp['style']) ){
      $args = array(
         'id'   	=> 'plugins'
        ,'title'	=> '◉ Plugins'
        ,'parent'	=> null
        ,'href'		=> null
        ,'meta'		=> array(
            'title'    => 'Activate/Deactivate plugins'
           //,'onclick'  => ''
           // ,'target'   => '_self'
           // ,'html'     => ''
           // ,'class'    => 'imsanity'
           // ,'rel'      => 'friend'
           // ,'tabindex' => PHP_INT_MAX
          )
      );
      $wp_admin_bar->add_node( $args );

      $active = array(
         'id'   	=> 'active'
        ,'title'	=> '<span class="active">◉</span> Active' //<span class="dashicons dashicons-visibility"></span>
        ,'parent'	=> 'plugins'
        ,'href'		=> null
        ,'meta'		=> array(
             'title' => 'Activate/Deactivate plugins'
            ,'class' => 'active-plugins-group'
          )
      );
      $wp_admin_bar->add_group( $active );

      $deactive = array(
         'id'   	=> 'deactive'
        ,'title'	=> '<span class="deactive">○</span> Deactive' //<span class="dashicons dashicons-hidden"></span>
        ,'parent'	=> 'plugins'
        ,'href'		=> null
        ,'meta'		=> array(
            'title'    => 'Activate/Deactivate plugins'
            ,'class' => 'deactive-plugins-group'
          )
      );
      $wp_admin_bar->add_group( $deactive );
    }

    foreach ($all_plugins as $url => $plugin) {
      if( !in_array($plugin['TextDomain'], $yabp['hidePlugins']) ) {
        //prr($plugin['TextDomain']);

        $styleOff = ($yabp['style'] == 'group' || !isset($yabp['style']) ) ? 'active' : null;
        $styleOn = ($yabp['style'] == 'group' || !isset($yabp['style']) ) ? 'deactive' : null;

        $off = array(
      		 'id'		  => $plugin['TextDomain']
      		,'parent'	=> $styleOff
      		,'title'	=> '<span class="active">◉</span> '.$plugin['Name'].' <b></b>' //<span class="dashicons dashicons-visibility"></span>
      		,'href'		=> na_action_link( $url, 'deactivate' )
      		,'meta'		=> array(
             'title'    => 'Deactivate '.$plugin['Name'].' plugin'
            ,'onclick'  => 'event.preventDefault();doIt(this, "'.na_action_link( $url, 'activate' ).'", "'.na_action_link( $url, 'deactivate' ).'");'
            )
      	);
      	$on = array(
      		 'id'   	=> $plugin['TextDomain']
      		,'parent'	=> $styleOn
      		,'title'	=> '<span class="deactive">○</span> '.$plugin['Name'].' <b></b>' //<span class="dashicons dashicons-hidden"></span>
      		,'href'		=> na_action_link( $url, 'activate' )
      		,'meta'		=> array(
              'title'    => 'Activate '.$plugin['Name'].' plugin'
             ,'onclick'  => 'event.preventDefault();doIt(this, "'.na_action_link( $url, 'activate' ).'", "'.na_action_link( $url, 'deactivate' ).'");'
            )
      	);

      	if( is_plugin_active($url) )
      		$wp_admin_bar->add_node( $off );
      	else
      		$wp_admin_bar->add_node( $on );

      }
    }

    // [advanced-custom-fields-pro/acf.php] => Array(
    //     [Name] => Advanced Custom Fields PRO
    //     [PluginURI] => https://www.advancedcustomfields.com/
    //     [Version] => 5.5.1
    //     [Description] => Customise WordPress with powerful, professional and intuitive fields
    //     [Author] => Elliot Condon
    //     [AuthorURI] => http://www.elliotcondon.com/
    //     [TextDomain] => acf
    //     [DomainPath] => /lang
    //     [Network] =>
    //     [Title] => Advanced Custom Fields PRO
    //     [AuthorName] => Elliot Condon
    // ) ?>
    <style type="text/css">
      #wp-admin-bar-imsanity span { font:400 20px/1 dashicons; margin-top:5px; }
      ul.active-plugins-group { display: block; }
      ul.deactive-plugins-group {
          position: absolute !important;
          top: 0;
          left: 100%;
          -webkit-box-shadow: 0 3px 5px rgba(0,0,0,.2);
          box-shadow: 3px 3px 5px rgba(0,0,0,.2);
          border-left: 1px solid rgba(0,0,0,.2);
          background: #32373c !important;
      }
      /*#wp-admin-bar-plugins .ab-sub-wrapper { display: block !important; }*/
    </style>
    <script>
      function doIt(that, wpnonceActivate, wpnonceDeactivate){
        var that = jQuery(that),
            url = that.attr('href'),
            log = jQuery(that).children('b'),
            child = jQuery(that).children('span');

        wpnonceActivate   = wpnonceActivate.split('=');
        wpnonceDeactivate = wpnonceDeactivate.split('=');
        wpnonceActivate   = wpnonceActivate[wpnonceActivate.length - 1];
        wpnonceDeactivate = wpnonceDeactivate[wpnonceDeactivate.length - 1];
        //console.log( 'wpnonceActivate:' + wpnonceActivate + ' / wpnonceDeactivate:' + wpnonceDeactivate );

        jQuery.get( url, function() {
          //console.log( 'Activate/Deactivate Imsanity plugin success' );
          if( child.hasClass('active') ){ //child.hasClass('dashicons-visibility')
            url = url.replace('=deactivate&','=activate&');
            url = url.replace(wpnonceDeactivate,wpnonceActivate);
            child.removeClass("active").addClass("deactive").text('○'); //child.removeClass("dashicons-visibility").addClass("dashicons-hidden")
          }else{
            url = url.replace('=activate&','=deactivate&');
            url = url.replace(wpnonceActivate,wpnonceDeactivate);
            child.removeClass("deactive").addClass("active").text('◉'); //child.removeClass("dashicons-hidden").addClass("dashicons-visibility")
          }
          that.attr('href', url);
          } )
          .done(function(){
            //log.css('color','green').text('Done');
            //console.log( 'done' );
          })
          .fail(function(){
            log.css('color','red').text('<?php _e('Get Error') ?>');
            var logClean = function(){
              log.removeAttr('style').text('');
            };
            setTimeout(logClean, 3000);
            //console.log( 'error' );
          })
          .always(function(){
            // console.log( 'finished' );
          });
        // jQuery.post(url, { data: valueToPass }, function(data){} );

        // return false; // prevent default browser refresh on '#' link
        };
      </script>

  <?php }

  // Удаление значков WP и ссылок в админбаре
  if( !empty($yabp['remove']) )
    add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );

  function remove_admin_bar_links() {
    global $yabp, $wp_admin_bar;
    !empty($yabp['remove']['wplogo']) ? $wp_admin_bar->remove_menu('wp-logo') : null;
    !empty($yabp['remove']['sitename']) ? $wp_admin_bar->remove_menu('site-name') : null;
    !empty($yabp['remove']['updates']) ? $wp_admin_bar->remove_menu('updates') : null;
    !empty($yabp['remove']['comments']) ? $wp_admin_bar->remove_menu('comments') : null;
    !empty($yabp['remove']['newcontent']) ? $wp_admin_bar->remove_menu('new-content') : null;
    !empty($yabp['remove']['newlink']) ? $wp_admin_bar->remove_menu('new-link') : null;
    !empty($yabp['remove']['myaccount']) ? $wp_admin_bar->remove_menu('my-account') : null;
  }
  // /Удаление значков WP и ссылок в админбаре

  if( $yabp['hideBar'] == 'user' && !current_user_can( 'manage_options' ) || $yabp['hideBar'] == 'all' )
    show_admin_bar( false );

  if( $yabp['hideBarWPAdmin'] == 1 )
    add_action( 'admin_enqueue_scripts', 'hide_wp_admin_bar' );

  function hide_wp_admin_bar() {
    wp_enqueue_style('bar_color', WPABC_URL. '/includes/css/style.css');
    $css = "html { padding-top: 0!important; } #wpadminbar {display: none;height: 0 !important;}";
    wp_add_inline_style( 'bar_color', $css );
  }

  if( !empty($yabp['barColor']) ) {
    add_action( 'wp_enqueue_scripts', 'bar_color' );
    add_action( 'admin_enqueue_scripts', 'bar_color' );
  }
	function bar_color() {
    global $yabp;
    wp_enqueue_style('bar_color', WPABC_URL. '/includes/css/style.css');
    $color = $yabp['barColor'];
    $css = "#wpadminbar {background: {$color} !important;}";
    wp_add_inline_style( 'bar_color', $css );
  }


  // Добавление своих пунктов админ-панель
  if( !empty($yabp['custom']) ) add_action('admin_bar_menu', 'add_mycms_admin_bar_link',25);
  function add_mycms_admin_bar_link() {
    global $yabp, $wp_admin_bar;
    if ( !is_super_admin() || !is_admin_bar_showing() )
  		return;

    foreach ($yabp['custom'] as $key => $value){
      $wp_admin_bar->add_menu( array(
    		 'id'    => 'custom_link_'.$key
    		,'title' => $value['icon'].' '.$value['title']
    		,'href'  => $value['link']
        ,'parent'	=> null // Уникальный идентификатор родительского меню
        ,'meta'		=> array(
          // 'title'    => 'Activate/Deactivate plugins'
          //,'onclick'  => ''
          // ,'target'   => '_self'
          // ,'html'     => ''
          // ,'class'    => 'imsanity'
          // ,'rel'      => 'friend'
          // ,'tabindex' => PHP_INT_MAX
          )
    	));
    }
  }
  // /Добавление своих пунктов админ-панель
