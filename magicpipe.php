<?php
/*

Plugin Name: MagicPipe

Plugin URI: https://surecode.me

Description: MagicPipe plugin with pagination for YouTube

Version: 1.0.1
 
Author: Kirill Shur(SureCode Marketing)

License: GPLv2

*/

  add_action('wp_enqueue_scripts','magicpipe_style');
  function magicpipe_style(){
    wp_enqueue_style('sureytbstyle1',plugins_url('/jpages/css/animate.css',__FILE__));
    wp_enqueue_style('sureytbstyle2',plugins_url('/jpages/css/jPages.css',__FILE__));
    wp_enqueue_style('sureytbstyle3',plugins_url('/jpages/css/style.css',__FILE__));
    wp_enqueue_style('sureytbstyle4',plugins_url('style.css',__FILE__));
    wp_enqueue_script('jquery');
    wp_enqueue_script('jpages',plugins_url('/jpages/js/jPages.min.js',__FILE__));

  }

add_action('admin_menu','magicpipe_menu');

function magicpipe_menu(){
  add_menu_page('Magic Pipe',
  'Magic Pipe',
  'manage_options',
  'magicpipe_page',
  'magicpipe_method',
   plugins_url('/jpages/img/magicpipe.png',__FILE__)
  );
}
function magicpipe_method(){
  ob_start();
  $options =  get_magicpipe_settings();
 ?>

 <div class="magicpipe_wrap">

  <h2 class="magicpipe_title"> MagicPipe Options</h2>

  <?php
    if(isset($_GET["message"]) && $_GET["message"] == 1){
   ?>
     <div id="message" class="updated fade">
       <p>
         <strong>
          Settings Saved
         </strong>
       </p>
     </div>

   <?php
    }
   ?>

  <form class="magicpipe_form" action="admin-post.php" method="post">

  <input type="hidden" name="action" value="save_magicpipe_options">
  <?php wp_nonce_field('my_magicpipe_data');?>

  <label for="api">API Key:</label>
  <input id="api" type="text" name="api" value="<?php echo esc_html($options['api']);?>">
  <br>
  <br>
  <label for="magicpipe_channel">Channel ID:</label>
  <input id="magicpipe_channel" type="text" name="magicpipe_channel" value="<?php echo esc_html($options['magicpipe_channel']);?>"/>
   <br>
   <br>
   <label for="magicpipe_results">YouTube Results:</label>
   <input id="magicpipe_results" type="text" name="magicpipe_results" value="<?php echo esc_html($options['magicpipe_results']);?>"/>
   <br>
   <br>
   <label for="gallery_results">Gallery Results:</label>
   <input id="gallery_results" type="text" name="gallery_results" value="<?php echo esc_html($options['gallery_results']);?>"/>
   <br>
   <br>
  <input type="submit" class="button-primary" name="submit" value="Submit">
  </form>
 </div>

<?php
  $magicpipeset = ob_get_clean();
  $allowed_html = array(
    'form'  => array(
      'class'=> array(),
      'method'=> array(),
      'action'=> array()
    ),
    'input' => array(
      'value' => array(),
       'type' => array(),
       'class'=> array(),
       'name'=> array(),
       'id'=> array()
    ),
    'br' => array(),
    'label' => array(
      'for'=> array()
    ),
    'div' => array(
      'class'=> array()
    ),
     'p'  => array(),
     'h2'  => array(
       'class'=> array()
     ),
     'link' => array(),
     'style' => array()
);
 echo wp_kses($magicpipeset, $allowed_html);
}


register_activation_hook(__FILE__,'magicpipe_settings');


function magicpipe_settings(){
  get_magicpipe_settings();
}

function get_magicpipe_settings(){

     $options = get_option('magicpipe_options',array());

     $newoptions["api"] = 'Put your YouTube api key';

     $newoptions["magicpipe_channel"] = "Put your YouTube channel key";

     $newoptions["magicpipe_results"] = '10';

     $newoptions["gallery_results"] = '6';

     $merged_options = wp_parse_args($options,$newoptions);

     $compare_options = array_diff_key($newoptions,$options);

      if(empty($options) && !empty($compare_options)){
        update_option('magicpipe_options',$merged_options);
      }
     return $merged_options;

}


function save_magicpipe_options(){

  if(!current_user_can('manage_options')){
    wp_die('Not allowed');
  }

  check_admin_referer('my_magicpipe_data');

  $options =  get_magicpipe_settings();

  foreach (array('api') as $option_name) {
    if(isset($_POST[$option_name])){
      $options[$option_name] = sanitize_text_field($_POST[$option_name]);
    }
  }

  foreach (array('magicpipe_channel') as $option_name) {
    if(isset($_POST[$option_name])){
      $options[$option_name] = sanitize_text_field($_POST[$option_name]);
    }
  }
  foreach (array('magicpipe_results') as $option_name) {
    if(isset($_POST[$option_name])){
      $options[$option_name] = sanitize_text_field($_POST[$option_name]);
    }
  }

  foreach (array('gallery_results') as $option_name) {
    if(isset($_POST[$option_name])){
      $options[$option_name] = sanitize_text_field($_POST[$option_name]);
    }
  }

  update_option('magicpipe_options',$options);

  wp_redirect(add_query_arg(array('page' => 'magicpipe_page','message' => '1'),admin_url('options-general.php')));

  exit;

}


add_action('admin_init','magicpipe_admin_init');

function magicpipe_admin_init(){
  add_action('admin_post_save_magicpipe_options','save_magicpipe_options');
}

function magicpipe_admin_style() {
  wp_enqueue_style('admin-styles',plugins_url('style.css',__FILE__));
}
add_action('admin_enqueue_scripts', 'magicpipe_admin_style');



add_shortcode('pipe','magicpipe_feed_method');

function magicpipe_feed_method($atts){
  ob_start();
?>
<?php
  $options = get_magicpipe_settings();
  extract(shortcode_atts(array('api' => $options['api'],
  'magicpipe_channel' => $options['magicpipe_channel'],
  'results' => $options['magicpipe_results'],
  'gallery_results' => $options['gallery_results']
  ),$atts));

  if(!empty($api) && !empty($magicpipe_channel)){
    $apiKey = $api;
    $channelId = $magicpipe_channel;
    $resultsNumber = $results;

    $gallery = $gallery_results;

    $requestUrl = 'https://www.googleapis.com/youtube/v3/search?key=' . $apiKey . '&channelId=' . $channelId . '&part=snippet,id&maxResults=' . $resultsNumber .'&order=date';


    $response = wp_remote_get($requestUrl);
    $json_response = json_decode( $response['body'], TRUE );


    if( $json_response ) {
        $i = 1;
    ?>
        <div class="videogalwrapper">
        <div class="holder"></div>
        <ul class="youtube-channel-videos" id="itemContainer">
    <?php
        foreach( $json_response['items'] as $item ) {
            if(!isset($item['id']['videoId'])){
              continue;
            }
            $videoTitle = $item['snippet']['title'];
            $videoID = $item['id']['videoId'];
            if( $videoTitle && $videoID ) {
      ?>
        <li class="youtube-channel-video-embed vid-<?php echo esc_html($videoID); ?> video-<?php echo esc_html($i++); ?>">
        <iframe width="500" height="300" src="https://www.youtube.com/embed/<?php echo esc_html($videoID); ?>" frameborder="0" allowfullscreen>
           <?php echo esc_html($videoTitle); ?>
         </iframe>
        </li>
     <?php
          }
        }
      ?>
      </ul>
      </div>

<script type="text/javascript">
 var $magicpipe = jQuery.noConflict();
$magicpipe(document).ready(function($){
  var gallery = '<?php echo esc_html($gallery);?>';
  var count_items = '<?php echo esc_html(count($json_response['items'])); ?>'

  $(function(){

      if(gallery && count_items > 0){
      $("div.holder").jPages({
          containerID : "itemContainer",
          perPage : gallery
      });
     }
  });

});
</script>

<?php

    } else {
      $allowed_html = array(
        'form'  => array(),
        'input' => array(),
        'br' => array(),
        'label' => array(),
        'div' => array(
          'class'=> array()
        ),
         'p'  => array(),
         'h2'  => array()
      );
       echo wp_kses('<div class="youtube-channel-videos error"><p>No videos are available at this time from the channel specified!</p></div>', $allowed_html);

      }

  }
}
