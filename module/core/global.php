<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
//随机字符串
function boxmoe_token($length){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $randStr = str_shuffle($str);
    $rands= substr($randStr,0,$length);
    return $rands;
}

//主题资源路径
function boxmoe_themes_dir() {
    $themes_dir='';
    $ui_cdn = get_boxmoe('ui_cdn');
    $diy_cdn_src = get_boxmoe('diy_cdn_src');
    if ( !empty($src) && !empty($diy_cdn_src) ){
        $themes_dir =  $diy_cdn_src;
    }else{
        $themes_dir = get_template_directory_uri();
    }
    return $themes_dir;
}

//全站链接字符
function boxmoe_connector() {
	return get_boxmoe('connector') ? ' ' . get_boxmoe('connector'). ' ' : ' - ';
}
//全站标题
function boxmoe_title() {
	global $new_title;
	if( $new_title ) return $new_title;
	global $paged;
	$html = '';
	$t = trim(wp_title('', false));
	if( (is_single() || is_page()) && get_the_subtitle(false) ){
		$t .= get_the_subtitle(false);
	}
	if ($t) {
		$html .= $t . boxmoe_connector();
	}
	$html .= get_bloginfo('name');
	if (is_home()) {
		if ($paged > 1) {
			$html .= boxmoe_connector() . '最新发布';
		}else{
			$html .= boxmoe_connector() . get_option('blogdescription');
		}
	}
	if ($paged > 1) {
		$html .= boxmoe_connector() . '第' . $paged . '页';
	}
	return $html;
}

function get_the_subtitle($span=true){
    global $post;
    $post_ID = $post->ID;
    $subtitle = get_post_meta($post_ID, 'subtitle', true);

    if( !empty($subtitle) ){
    	if( $span ){
        	return ' <span>'.$subtitle.'</span>';
        }else{
        	return ' '.$subtitle;
        }
    }else{
        return false;
    }
}
/* 
 * post meta keywords
 * ====================================================
*/
$postmeta_keywords_description = array(
    array(
        "name" => "keywords",
        "std" => "",
        "title" => __('关键字', 'boxmoe').'：'
    ),
    array(
        "name" => "description",
        "std" => "",
        "title" => __('描述', 'boxmoe').'：'
        )
);
if( get_boxmoe('post_keywords_description_s') ){
    add_action('admin_menu', 'boxmoe_postmeta_keywords_description_create');
    add_action('save_post', 'boxmoe_postmeta_keywords_description_save');
}

function boxmoe_postmeta_keywords_description() {
    global $post, $postmeta_keywords_description;
    foreach($postmeta_keywords_description as $meta_box) {
        $meta_box_value = get_post_meta($post->ID, $meta_box['name'], true);
        if($meta_box_value == "")
            $meta_box_value = $meta_box['std'];
        echo'<p>'.$meta_box['title'].'</p>';
        if( $meta_box['name'] == 'keywords' ){
            echo '<p><input type="text" style="width:98%" value="'.$meta_box_value.'" name="'.$meta_box['name'].'"></p>';
        }else{
            echo '<p><textarea style="width:98%" name="'.$meta_box['name'].'">'.$meta_box_value.'</textarea></p>';
        }
    }
   
    echo '<input type="hidden" name="post_newmetaboxes_noncename" id="post_newmetaboxes_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
}

function boxmoe_postmeta_keywords_description_create() {
    global $theme_name;
    if ( function_exists('add_meta_box') ) {
        add_meta_box( 'postmeta_keywords_description_boxes', __('自定义关键字和描述', 'boxmoe'), 'boxmoe_postmeta_keywords_description', 'post', 'normal', 'high' );
        add_meta_box( 'postmeta_keywords_description_boxes', __('自定义关键字和描述', 'boxmoe'), 'boxmoe_postmeta_keywords_description', 'page', 'normal', 'high' );
    }
}

function boxmoe_postmeta_keywords_description_save( $post_id ) {
    global $postmeta_keywords_description;
   
    if ( !wp_verify_nonce( isset($_POST['post_newmetaboxes_noncename'])?$_POST['post_newmetaboxes_noncename']:'', plugin_basename(__FILE__) ))
        return;
   
    if ( !current_user_can( 'edit_posts', $post_id ))
        return;
                   
    foreach($postmeta_keywords_description as $meta_box) {
        $data = $_POST[$meta_box['name']];
        if(get_post_meta($post_id, $meta_box['name']) == "")
            add_post_meta($post_id, $meta_box['name'], $data, true);
        elseif($data != get_post_meta($post_id, $meta_box['name'], true))
            update_post_meta($post_id, $meta_box['name'], $data);
        elseif($data == "")
            delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
    }
}

/* 
 * keywords
 * ====================================================
*/
function boxmoe_keywords() {
  global $s, $post;
  $keywords = '';
  if ( is_singular() ) {
    if ( get_the_tags( $post->ID ) ) {
      foreach ( get_the_tags( $post->ID ) as $tag ) $keywords .= $tag->name . ', ';
    }
    foreach ( get_the_category( $post->ID ) as $category ) $keywords .= $category->cat_name . ', ';
    if(get_boxmoe('post_keywords_description_s') ) {
        $the = trim(get_post_meta($post->ID, 'keywords', true));
        if( $the ) $keywords = $the;
    }else{
        $keywords = substr_replace( $keywords , '' , -2);
    }
    
  } elseif ( is_home () )    { $keywords =get_boxmoe('keywords');
  } elseif ( is_tag() )      { $keywords = single_tag_title('', false);
  } elseif ( is_category() ) { $keywords = single_cat_title('', false);

    if(get_boxmoe('cat_keyworks_s') ){
        $description = trim(strip_tags(category_description()));
        if( $description && strstr($description, '::::::') ){
            $desc = explode('::::::', $description);
            if( $desc[0] && !empty($desc[0]) ) {
                $keywords = trim($desc[0]);
            }
        }
    }

  } elseif ( is_search() )   { $keywords = esc_html( $s, 1 );
  } else { $keywords = trim( wp_title('', false) );
  }
  if ( $keywords ) {
    echo "<meta name=\"keywords\" content=\"$keywords\">\n";
  }
}
/* 
 * description
 * ====================================================
*/
function boxmoe_description() {
  global $s, $post;
  $description = '';
  $blog_name = get_bloginfo('name');
  if ( is_singular() ) {
    if( !empty( $post->post_excerpt ) ) {
      $text = $post->post_excerpt;
    } else {
      $text = $post->post_content;
    }
    $description = trim( str_replace( array( "\r\n", "\r", "\n", "　", " "), " ", str_replace( "\"", "'", strip_tags( $text ) ) ) );
    if ( !( $description ) ) $description = $blog_name . "-" . trim( wp_title('', false) );
    if(get_boxmoe('post_keywords_description_s') ) {
        $the = trim(get_post_meta($post->ID, 'description', true));
        if( $the ) $description = $the;
    }
  } elseif ( is_home () )    { $description =get_boxmoe('description');
  } elseif ( is_tag() )      { $description = $blog_name . "'" . single_tag_title('', false) . "'";
  } elseif ( is_category() ) { 

    $description = trim(strip_tags(category_description()));

    if(get_boxmoe('cat_keyworks_s') && $description && strstr($description, '::::::') ){
        $desc = explode('::::::', $description);
        $description = trim($desc[1]);
    }

  } elseif ( is_archive() )  { $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
  } elseif ( is_search() )   { $description = $blog_name . ": '" . esc_html( $s, 1 ) . "' ".__('的搜索結果', 'boxmoe');
  } else { $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
  }
  $description = mb_substr( $description, 0, 80, 'utf-8' );
  echo "<meta name=\"description\" itemprop=\"description\" itemprop=\"name\" content=\"$description\">\n";
}

//Favicon地址
function boxmoe_favicon() {
	$src = get_boxmoe('favicon_src');
	if( !empty($src) ) {
		$src = '<link rel="shortcut icon" href="'.$src.'" />';
	}else{
        $src = '<link rel="shortcut icon" href="'.get_template_directory_uri().'/assets/images/favicon.ico" />';
    }
	echo $src;
}
//节日灯笼
function boxmoe_load_lantern() {
	if (get_boxmoe('lantern') ){?>
    <div id="wp" class="wp"> <div class="xnkl"> <div class="deng-box1"> <div class="deng"> <div class="xian"> </div> <div class="deng-a"> <div class="deng-b"> <div class="deng-t"> <?php echo get_boxmoe( 'lanternfont1', '春')?> </div> </div> </div> <div class="shui shui-a"> <div class="shui-c"> </div> <div class="shui-b"> </div> </div> </div> </div> <div class="deng-box"> <div class="deng"> <div class="xian"> </div> <div class="deng-a"> <div class="deng-b"> <div class="deng-t"> <?php echo get_boxmoe( 'lanternfont2', '新')?> </div> </div> </div> <div class="shui shui-a"> <div class="shui-c"> </div> <div class="shui-b"> </div> </div> </div> </div> </div>
	<?php }
}

function boxmoe_render_sakura_preloader() { ?>
    <div class="preloader">
        <svg version="1.1" id="boxmoe-sakura" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="80" height="80" viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
            <g id="sakura">
            <path id="hana-01" class="st0" d="M52,16.4c-1-8-8-12-8-12l-4,2l-4-2c0,0-7,4-8,12c-0.4,3.2,1,7,2,9.1c2.1,4.4,6.4,7.9,10,10.9
            c3.6-3,7.9-6.6,10-10.9C51,23.4,52.4,19.7,52,16.4z">
               <animate attributeType="XML" attributeName="opacity" values="0;1;1;1;1;1;0;0;0;0" dur="5s" calcMode="discrete" begin="0s" repeatCount="indefinite"></animate>
            </path>
            <path id="hanapath-01" class="st1" d="M52,16.4c-1-8-8-12-8-12l-4,2l-4-2c0,0-7,4-8,12c-0.4,3.2,1,7,2,9.1c2.1,4.4,6.4,7.9,10,10.9
            c3.6-3,7.9-6.6,10-10.9C51,23.4,52.4,19.7,52,16.4z"></path>
            <path id="hana-02" class="st0" d="M74.2,31.3l0.7-4.4c0,0-6-5.4-13.9-3.9c-3.2,0.6-6.3,3.1-8,4.7c-3.5,3.4-5.6,8.5-7.3,12.9
            c4,2.5,8.7,5.5,13.5,6.1c2.3,0.3,6.3,0.5,9.2-0.9c7.3-3.4,8.9-11.3,8.9-11.3L74.2,31.3z">
               <animate attributeType="XML" attributeName="opacity" values="0;1;1;1;1;1;0;0;0;0" dur="5s" calcMode="discrete" begin="0.5s" repeatCount="indefinite"></animate>
            </path>
            <path id="hanapath-02" class="st1" d="M74.2,31.3l0.7-4.4c0,0-6-5.4-13.9-3.9c-3.2,0.6-6.3,3.1-8,4.7c-3.5,3.4-5.6,8.5-7.3,12.9
            c4,2.5,8.7,5.5,13.5,6.1c2.3,0.3,6.3,0.5,9.2-0.9c7.3-3.4,8.9-11.3,8.9-11.3L74.2,31.3z"></path>
            <path id="hana-03" class="st0" d="M65,56.4c-1.6-2.9-4.9-5.1-6.9-6.2c-4.3-2.3-9.8-2.7-14.5-3c-1.2,4.6-2.5,9.9-1.7,14.7
            c0.4,2.2,1.5,6.1,3.7,8.5c5.5,5.9,13.5,5,13.5,5l2.1-4l4.4-0.7C65.6,70.8,68.9,63.5,65,56.4z">
               <animate attributeType="XML" attributeName="opacity" values="0;1;1;1;1;1;0;0;0;0" dur="5s" calcMode="discrete" begin="1s" repeatCount="indefinite"></animate>
            </path>
            <path id="hanapath-03" class="st1" d="M65,56.4c-1.6-2.9-4.9-5.1-6.9-6.2c-4.3-2.3-9.8-2.7-14.5-3c-1.2,4.6-2.5,9.9-1.7,14.7
               c0.4,2.2,1.5,6.1,3.7,8.5c5.5,5.9,13.5,5,13.5,5l2.1-4l4.4-0.7C65.6,70.8,68.9,63.5,65,56.4z"></path>
               <path id="hana-04" class="st0" d="M36.5,47.3c-4.7,0.3-10.2,0.7-14.5,3c-2,1.1-5.4,3.3-6.9,6.2c-3.9,7.1-0.6,14.4-0.6,14.4l4.4,0.7
               l2.1,4c0,0,8,0.9,13.5-5c2.2-2.4,3.3-6.3,3.7-8.5C39,57.2,37.6,51.9,36.5,47.3z">
               <animate attributeType="XML" attributeName="opacity" values="0;1;1;1;1;1;0;0;0;0" dur="5s" calcMode="discrete" begin="1.5s" repeatCount="indefinite"></animate>
            </path>
            <path id="hanapath-04" class="st1" d="M36.5,47.3c-4.7,0.3-10.2,0.7-14.5,3c-2,1.1-5.4,3.3-6.9,6.2c-3.9,7.1-0.6,14.4-0.6,14.4
            l4.4,0.7l2.1,4c0,0,8,0.9,13.5-5c2.2-2.4,3.3-6.3,3.7-8.5C39,57.2,37.6,51.9,36.5,47.3z"></path>
            <path id="hana-05" class="st0" d="M27,27.7c-1.6-1.6-4.8-4.1-8-4.7c-7.9-1.5-13.9,3.9-13.9,3.9l0.7,4.4l-3.1,3.2
            c0,0,1.6,7.9,8.9,11.3c3,1.4,7,1.2,9.2,0.9c4.8-0.7,9.5-3.6,13.5-6.1C32.5,36.2,30.5,31.1,27,27.7z">
               <animate attributeType="XML" attributeName="opacity" values="0;1;1;1;1;1;0;0;0;0" dur="5s" calcMode="discrete" begin="2s" repeatCount="indefinite"></animate>
            </path>
            <path id="hanapath-05" class="st1" d="M27,27.7c-1.6-1.6-4.8-4.1-8-4.7c-7.9-1.5-13.9,3.9-13.9,3.9l0.7,4.4l-3.1,3.2
               c0,0,1.6,7.9,8.9,11.3c3,1.4,7,1.2,9.2,0.9c4.8-0.7,9.5-3.6,13.5-6.1C32.5,36.2,30.5,31.1,27,27.7z"></path>
            <animateTransform attributeType="XML" attributeName="transform" type="rotate" values="0 40 40; 360 40 40" calcMode="linear" dur="10s" repeatCount="indefinite"></animateTransform>
         </g>
         <animateTransform attributeName="transform" type="translate" additive="sum" from="40,40" to="40,40"></animateTransform>
         <animateTransform attributeName="transform" type="scale" additive="sum" keyTimes="0;0.5;1" keySplines="0.42 0.0 0.58 1.0" values="1,1;0.75,0.75;1,1" dur="3s" repeatCount="indefinite"></animateTransform>
         <animateTransform attributeName="transform" type="translate" additive="sum" from="-40,-40" to="-40,-40"></animateTransform>
      </svg>
    </div>
    <?php }

function boxmoe_render_cat_preloader() { ?>
    <div class="preloader preloader-cat">
      <div class="boxmoe-cat-loader bm-cat">
        <div class="bm-cat-body"></div>
        <div class="bm-cat-head">
          <div class="bm-cat-face"></div>
        </div>
        <div class="bm-cat-foot">
          <div class="bm-cat-tummy-end"></div>
          <div class="bm-cat-bottom"></div>
          <div class="bm-cat-legs bm-cat-left"></div>
          <div class="bm-cat-legs bm-cat-right"></div>
        </div>
        <div class="bm-cat-paw">
          <div class="bm-cat-hands bm-cat-left"></div>
          <div class="bm-cat-hands bm-cat-right"></div>
        </div>
      </div>
    </div>
<?php }

function boxmoe_render_preloader() {
    $mode = get_boxmoe('boxmoe_preloader', null);
    if ($mode === 'cat') {
        boxmoe_render_cat_preloader();
        return;
    }
    if ($mode === 'sakura') {
        boxmoe_render_sakura_preloader();
    }
}
            
//logo地址
function boxmoe_logo(){
    if( get_boxmoe('logo_src') ) {
        $src = '<img src="'.get_boxmoe('logo_src').'" alt="'. get_bloginfo('name') .'" class="logo">';	
    }else{
        $src = bloginfo('name');
    }    
    return  $src;
}

// 前端载入
function boxmoe_load_scripts_and_styles() {
    wp_enqueue_style('theme-style', boxmoe_themes_dir() . '/assets/css/style.css', array(), null, false);
    wp_enqueue_style('theme-emoji-style', boxmoe_themes_dir() . '/assets/emoji/src/css/jquery.emoji.css', array(), null, false);
    if(get_boxmoe('loli')){
        wp_enqueue_style('theme-live2d-style', 'https://log.moejue.cn/live2d/assets/waifu.css', array(), null, false);
    }
    if(get_boxmoe('music_on')){
        wp_enqueue_style('theme-APlayer-style', boxmoe_themes_dir() . '/assets/css/APlayer.min.css', array(), null, false);
    }
    wp_enqueue_script('custom-jquery', boxmoe_themes_dir() . '/assets/js/lib/jquery.min.js', array(), null, false);
    wp_enqueue_script('pjax', boxmoe_themes_dir() . '/assets/js/lib/jquery.pjax.min.js', array('custom-jquery'), null, false);
}
add_action('wp_enqueue_scripts', 'boxmoe_load_scripts_and_styles', 100);

function boxmoe_load_footer() {?>       
        <script src="<?php echo boxmoe_themes_dir();?>/assets/js/lib/theme.min.js" type="text/javascript"></script>
        <script src="<?php echo boxmoe_themes_dir();?>/assets/emoji/src/js/jquery.emoji.js" type="text/javascript"></script>
        <script src="<?php echo boxmoe_themes_dir();?>/assets/emoji/src/js/emoji.list.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri();?>/assets/js/comments.js" type="text/javascript"></script>
        <script src="<?php echo boxmoe_themes_dir();?>/assets/js/boxmoe.js" type="text/javascript" id="boxmoe_script"></script>	
        <?php if(get_boxmoe('music_on')){ ?>
        <script src="<?php echo boxmoe_themes_dir();?>/assets/js/APlayer.min.js" type="text/javascript"></script>
        <script src="https://unpkg.com/meting@2.0.1/dist/Meting.min.js" type="text/javascript"></script>  
        <?php } ?>
        <?php if(get_boxmoe('loli')){ ?>
        <script src="https://log.moejue.cn/live2d/assets/waifu-tips.js"></script>
        <script src="https://log.moejue.cn/live2d/assets/live2d.js"></script><?php } ?>
        <?php if (get_boxmoe('sakura')): ?>
<script src="<?php echo boxmoe_themes_dir();?>/assets/js/lib/sakura.js"></script>
        <?php endif; ?>
<script type="text/javascript">
  <?php if(get_boxmoe('snow')){ ?>
    setInterval(createSnowflake, 500);
  <?php } ?>
  <?php if (get_boxmoe('hitokoto_on')): ?>
                    var hitokoto = function () {
                    $.get("https://v1.hitokoto.cn/?c=<?php echo get_boxmoe('hitokoto_text')?>", {},
                        function (data) {
                            document.getElementById("hitokoto").innerHTML = data.hitokoto;
                        });
                    };
                    if ($("#hitokoto").length) {
                        hitokoto();
                    }
  <?php endif; ?>
                    $(document).on("pjax:complete", function () {
                        <?php if (get_boxmoe('hitokoto_on')): ?>
                        if ($("#hitokoto").length) {
                            hitokoto();
                        }
                        <?php endif; ?>
                        initEmoji();
                        <?php if(get_boxmoe('loli')){ ?>
                        initLive2d();
                        <?php } ?>
                    });
                    var initEmoji = function () {
                        $("#btn").click(function () {
                            $("#comment").emoji({
                                button: '#btn',
                                showTab: true,
                                animation: 'fade',
                                basePath: '<?php echo boxmoe_themes_dir();?>/assets/images/emoji',
                                icons: emojiLists
                            });
                        });
                    };initEmoji();
  <?php if( get_boxmoe('footer_time') ) {
    echo 'displayRunningTime("'.get_boxmoe('footer_time','').'");';}?>
  <?php if(get_boxmoe('loli')){ ?>
                    var initLive2d = function(){ initModel("https://log.moejue.cn/live2d/assets/"); };
                    initLive2d();
  <?php } ?>
                </script>
    <?php }   
//底部链接输出
function boxmoe_footer_seo() {
	if( get_boxmoe('footer_seo') ) {
	echo '<ul class="nav flex-row align-items-center mt-sm-0 justify-content-center nav-footer">';
	echo get_boxmoe('footer_seo');
	echo '</ul>';
	}else{
		
	}	
}
//底部社交输出
function boxmoe_footer_social() {
	if(get_boxmoe('boxmoe_qq')){
		echo '<a href="'.get_boxmoe('boxmoe_qq').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主QQ群" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-qq"></i></a>';
		}
	if(get_boxmoe('boxmoe_wechat')){
		echo '<a href="'.get_boxmoe('boxmoe_wechat').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主微信" data-fancybox="gallery" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-wechat"></i></a>';
		}	
	if(get_boxmoe('boxmoe_weibo')){
		echo '<a href="'.get_boxmoe('boxmoe_weibo').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主微博" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-weibo"></i></a>';
		}
	if(get_boxmoe('boxmoe_github')){
		echo '<a href="'.get_boxmoe('boxmoe_github').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Github" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-github"></i></a>';
		}
	if(get_boxmoe('boxmoe_mail')){
		echo '<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email='.get_boxmoe('boxmoe_mail').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主邮箱" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-envelope"></i></a>';
		}
	if(get_boxmoe('boxmoe_bilibili')){
		echo '<a href="'.get_boxmoe('boxmoe_bilibili').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主B站" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-bold"></i></a>';
		}
	if(get_boxmoe('boxmoe_twitter')){
		echo '<a href="'.get_boxmoe('boxmoe_twitter').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Twitter" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-twitter"></i></a>';
		}
	if(get_boxmoe('boxmoe_music')){
		echo '<a href="'.get_boxmoe('boxmoe_music').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主网易云音乐" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-music"></i></a>';
		}
	if(get_boxmoe('boxmoe_facebook')){
		echo '<a href="'.get_boxmoe('boxmoe_facebook').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Facebook" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-facebook"></i></a>';
		}
	if(get_boxmoe('boxmoe_youtube')){
		echo '<a href="'.get_boxmoe('boxmoe_youtube').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Youtube" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-youtube-play"></i></a>';
		}
	if(get_boxmoe('boxmoe_steam')){
		echo '<a href="'.get_boxmoe('boxmoe_steam').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Steam" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-steam"></i></a>';
		}
	if(get_boxmoe('boxmoe_telegram')){
		echo '<a href="'.get_boxmoe('boxmoe_telegram').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主Telegram" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-telegram"></i></a>';
		}
	if(get_boxmoe('boxmoe_wordpress')){
		echo '<a href="'.get_boxmoe('boxmoe_wordpress').'" data-bs-toggle="tooltip" data-bs-placement="top" title="博主WordPress" target="_blank" class="text-reset btn btn-social btn-icon">
          <i class="fa fa-wordpress"></i></a>';
		}
}
function boxmoe_load_footerlogo() {?>
    <a class="mb-4 mb-lg-0 d-block" href="<?php echo home_url(); ?>">
    <?php echo boxmoe_logo(); ?></a>
    <?php }
//底部信息输出
function boxmoe_footer_info() {
	echo '<p class="mb-0 copyright">';
	echo 'Copyright © 2016 <a href="'.home_url().'" target="_blank">'.get_bloginfo( 'name' ).'</a>. All Rights Reserved. <br> Powered by Wordpress | Theme by
                <a href="https://github.com/iAJue/lolimeow" target="_blank">LoLiMeow</a>';				
	if( get_boxmoe('footer_info') ) {
	echo '<br>'.get_boxmoe('footer_info','');	
	}
	if( get_boxmoe('footer_time') ) {
    echo '<br><span id="runningTime"></span>';	
    }
	if( get_boxmoe('boxmoedataquery') ) {
	echo '<br>'.get_num_queries().' queries in '.timer_stop().' s';	
	}
	if( get_boxmoe('trackcode') ) {
	echo '<div style="display:none;">'.get_boxmoe('trackcode').'</div>';	
	}
	echo '</p>'."\n";
}

//导航&侧栏部分
if (function_exists('register_nav_menus')) {
	register_nav_menus( array(
	    'navs' => __('顶部主导航', 'boxmoe-com'),
	    ));
}
function boxmoe_nav_menu($location='navs',$dropdowns='dropdown'){
    echo ''.str_replace("</ul></div>", "", preg_replace("/<div[^>]*><ul[^>]*>/", "", 
	wp_nav_menu(array(
	'theme_location' => $location,
	'fallback_cb' => 'bootstrap_5_wp_nav_menu_walker::fallback',
	'depth' => 0,
	'menu_class' => $dropdowns,
	'walker' => new bootstrap_5_wp_nav_menu_walker(),
	'echo' => false)) )).'';
}
//banner参数
function boxmoe_banner() {
	$banner_rand = get_boxmoe('banner_rand');
	$banner_api_on =  get_boxmoe('banner_api_on');
	if( !empty($banner_api_on)) {	
	$banner_dir = 'style="background-image: url(\''.get_boxmoe('banner_api_url').'?'.boxmoe_token(6).'\');"';
	}	
	else if( !empty($banner_rand) ) {
	$banner_no = get_boxmoe('banner_rand_n');
	$temp_no = rand(1,$banner_no);		
	$banner_dir = 'style="background-image: url(\''.boxmoe_themes_dir().'/assets/images/banner/'.$temp_no.'.jpg\');" ';
	}
	else if	( get_boxmoe('banner_url') ) {
	$banner_dir = 'style="background-image: url(\''.get_boxmoe('banner_url').'\');"';}	
	else {	
	$banner_dir = 'style="background-image: url(\''.boxmoe_themes_dir().'/assets/images/banner/1.jpg\');"';
	}		
return  $banner_dir;
}

//全站布局
function boxmoe_blog_layout() {
    $sidebar ='col-lg-10 mx-auto';
    $blog_layout = get_boxmoe('blog_layout');	
        if(!empty($blog_layout)) {
            if(get_boxmoe('blog_layout') == 'two' ){
                $sidebar='col-lg-8';
                }elseif(get_boxmoe('blog_layout') == 'one' ){
                    $sidebar = 'col-lg-10 mx-auto';
                }
            }
    return  $sidebar;	
}

//布局边框
function boxmoe_border(){
    $border='';
    $border_layout= get_boxmoe('blog_layout_border');
    if(!empty($border_layout)) {
        if(get_boxmoe('blog_layout_border') == 'default' ){
            $border='';
            }elseif(get_boxmoe('blog_layout_border') == 'border' ){
                $border = 'blog-border';
            }elseif(get_boxmoe('blog_layout_border') == 'shadow' ){
                $border = 'blog-shadow';
            }
        }
        return  $border;
}



//widgets
if( get_boxmoe('blog_layout') !== 'one' ){

    if (function_exists('register_sidebar')){
        $widgets = array(
            'sitesidebar' => __('全站侧栏', 'boxmoe-com'),
            'sidebar' => __('首页侧栏', 'boxmoe-com'),
            'postsidebar' => __('文章页侧栏', 'boxmoe-com'),
            'pagesidebar' => __('页面侧栏', 'boxmoe-com'),
        );
		$boxmoeborder='';
		if(get_boxmoe('blog_layout_border') == 'default' ){
			$boxmoeborder='blog-border';
			}elseif(get_boxmoe('blog_layout_border') == 'border'){
			$boxmoeborder='blog-border';
			}elseif(get_boxmoe('blog_layout_border') == 'shadow'){
			$boxmoeborder='blog-shadow';}
        foreach ($widgets as $key => $value) {
            register_sidebar(array(
                'name'          => $value,
                'id'            => 'widget_'.$key,
                'before_widget' => '<div class="widget '.$boxmoeborder.' %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="widget-title">',
                'after_title'   => '</h4>'
            ));
        }
    }
    require_once get_template_directory() . '/module/template/widget-set.php';
}

//搜索结果排除所有页面
function search_filter_page($query) {
    if ($query->is_search) {
        $query->set('post_type', 'post');
    }
    return $query;
}
add_filter('pre_get_posts','search_filter_page');

// 开启友情链接
add_filter( 'pre_option_link_manager_enabled', '__return_true' );
