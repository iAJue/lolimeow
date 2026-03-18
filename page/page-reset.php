<?php 
/**
 * Template Name: Boxmoe找回密码
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
error_reporting(0);
global $wpdb, $user_ID;
function tg_validate_url() {   
	global $post;   
	$page_url = esc_url(get_permalink( $post->ID )); 
	$urlget = strpos($page_url, "?");   
	if ($urlget === false) {   
		$concate = "?";   
	}else{   
		$concate = "&";   
	}   
	return $page_url.$concate;
}
if(!$user_ID){
	if($_POST['action'] == "tg_pwd_reset"){ //判断是否为请求重置密码   
    if ( !wp_verify_nonce( $_POST['tg_pwd_nonce'], "tg_pwd_nonce")) { //检查随机数   
        exit("不要开玩笑");   
    }   
    if(empty($_POST['user_input'])) {   
        echo "<div class='error'>请输入用户名或E-mail地址</div>";   
        exit();   
    }
    //过滤提交的数据   
    $user_input = $wpdb->escape(trim($_POST['user_input']));
    if ( strpos($user_input, '@') ) { //判断用户提交的是邮件还是用户名   
        $user_data = get_user_by_email($user_input); //通过Email获取用户数据   
        if(empty($user_data) || $user_data->caps['administrator'] == 1) { //排除管理员   
            echo "<div class='error'>无效的E-mail地址!</div>";   
            exit();   
        }   
    } else {   
        $user_data = get_userdatabylogin($user_input); //通过用户名获取用户数据   
        if(empty($user_data) || $user_data->caps['administrator'] == 1) { //排除管理员   
            echo "<div class='error'>无效的用户名!</div>";   
            exit();   
        }   
    } 
    $user_login = $user_data->user_login;   
    $user_email = $user_data->user_email;
    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login)); //从数据库中获取密匙   
    if(empty($key)) { //如果为空   
        //generate reset keys生成 keys   
        $key = wp_generate_password(20, false); //生成一个20位随机密码用做密匙   
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login)); //更新到数据库   
    }
    //邮件内容   
    $message = __('有人提交了重置下面账户密码的请求:') . "\r\n\r\n";   
    $message .= get_option('siteurl') . "\r\n\r\n";   
    $message .= sprintf(__('用户名: %s'), $user_login) . "\r\n\r\n";   
    $message .= __('如果不是您本人操作，请忽略这个邮件即可.') . "\r\n\r\n";   
    $message .= __('如果需要重置密码，请访问下面的链接:') . "\r\n\r\n";   
    $message .= tg_validate_url() . "action=reset_pwd&key=$key&login=" . rawurlencode($user_login) . "\r\n"; //注意tg_validate_url()，注意密码重置的链接地址，需要action\key\login三个参数
    if ( $message && !wp_mail($user_email, '密码重置请求', $message) ) {   
        echo "<div class='error'>邮件发送失败-原因未知。</div>";   
        exit();   
    } else {   
        echo "<div class='success'>我们已经在给你发送的邮件中说明了重置密码的各项事宜，请注意查收。</div>";   
        exit();   
    }   
} else {
?>
<?php
    if(isset($_GET['key']) && $_GET['action'] == "reset_pwd") { //如果存在key且action参数似乎reset_pwd   
        $reset_key = $_GET['key']; //获取密匙   
        $user_login = $_GET['login']; //获取用户名   
        $user_data = $wpdb->get_row($wpdb->prepare("SELECT ID, user_login, user_email FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $reset_key, $user_login));   
        //通过key和用户名验证数据   
 
        $user_login = $user_data->user_login;   
        $user_email = $user_data->user_email;   
        if(!empty($reset_key) && !empty($user_data)) {   
            $new_password = wp_generate_password(7, false); //生成7位随机密码   
            //echo $new_password; exit();   
            wp_set_password( $new_password, $user_data->ID ); //重置密码   
            //通过邮件将密码发送给用户   
            $message = __('账户的新密码为:') . "\r\n\r\n";   
            $message .= get_option('siteurl') . "\r\n\r\n";   
            $message .= sprintf(__('用户名: %s'), $user_login) . "\r\n\r\n";   
            $message .= sprintf(__('密码: %s'), $new_password) . "\r\n\r\n";   
            $message .= __('你可以使用你的新密码通过下面的链接登录: ') . get_option('siteurl')."/login" . "\r\n\r\n";   
            if ( $message && !wp_mail($user_email, '密码重置请求', $message) ) {   
                echo '<script type="text/javascript">Swal.fire("Oops...","邮件发送失败-原因未知", "error");</script>';   
                exit();   
            } else {   
                $redirect_to = tg_validate_url()."action=reset_success";//跳转到登陆成功页面(还是本页面地址)   
                wp_safe_redirect($redirect_to);   
                exit();   
            }   
 
        } else{   
            echo('<script type="text/javascript">Swal.fire("Oops...","无效的key.", "error");</script>');   
        }   
    }  
 

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>> 
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if(get_boxmoe('favicon_src')){?><?php echo  boxmoe_favicon();?><?php } ?>
    <title><?php echo  boxmoe_title(); ?></title>
	  <?php echo boxmoe_keywords()?>
    <?php echo boxmoe_description()?>
	  <?php wp_head(); ?>
</head>
  <body>
  <?php boxmoe_render_preloader(); ?>
  <?php echo boxmoe_load_lantern(); ?>
    <div id="boxmoe_theme_global">
      <section id="boxmoe_theme_header" class="fadein-top position-sticky" style="z-index: 3 !important;">
        <nav class="navbar navbar-expand-lg navbar-bg-box blur blur-rounded userheader my-3 py-2">
          <div class="container">
            <a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo get_bloginfo('name'); ?>">
			<?php echo boxmoe_logo(); ?></a>
            <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </span>
            </button>
            <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="navigation" aria-labelledby="offcanvasWithBothOptionsLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">
                 <?php echo boxmoe_logo(); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body">
                <ul class="navbar-nav mx-auto">
				        <?php boxmoe_nav_menu();?>
                </ul>
                <ul class="navbar-nav">
				<li class="nav-item">
                    <a href="#search" class="nav-link search btn">
                      <i class="fa fa-search"></i>
                    </a>
                  </li>

                </ul>
              </div>
            </div>
          </div>
        </nav>
      </section>
<link rel='stylesheet' href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.4/dist/sweetalert2.min.css" type='text/css' media='all' />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.4/dist/sweetalert2.min.js"></script>
<?php if(isset($_GET['action']) && $_GET['action'] == "reset_success") { //如果动作为reset_success就是成功了哇   
   echo '<script type="text/javascript">Swal.fire("Wow...","密码重置成功，已经通过邮件发送给您，请查收。", "success");</script>';
}  	  ?>
<div class="page-header min-vh-75">
      <div class="container">
        <div class="row">
          <div class="col-xl-5 col-lg-6 col-md-8 col-12 px-5 d-flex flex-column">
            <div class="w-100 align-self-end col-12">
                     <div class="text-center mb-7">
                        <h1 class="mb-1 text-gradient">重置密码</h1>
                        <p class="mb-0">您将在60秒内收到一封电子邮件！没收到看看垃圾邮件( ╯□╰ )！</p>
                     </div>
                     <form class="needs-validation mb-5" id="wp_pass_reset" action="" method="post">
                        <div class="mb-3">
                           <label for="forgetEmailInput2" class="form-label">
                              Email
                              <span class="text-danger">*</span>
                           </label>
                           <input type="email" class="form-control" name="user_input" value="" placeholder="输入你的电子邮箱" required="">
                           <input type="hidden" name="action" value="tg_pwd_reset" />   
                           <input type="hidden" name="tg_pwd_nonce" value="<?php echo wp_create_nonce("tg_pwd_nonce"); ?>" />  
                        </div>
                        <div class="d-grid">
                           <button class="btn btn-primary" type="submit" id="submitbtn" name="submit">
                           重置密码</button>
                        </div>
                     </form>
                     <div class="mb-3">
                     <div class="alert alert-primary align-items-center" id="result">
                      <div id="rsmsg"> <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> 稍等(⊙o⊙)？正在发送验证邮件...
                      </div>
                      </div>
      <script type="text/javascript">   
        $("#wp_pass_reset").submit(function() {    
            $('#result').fadeIn();   
            var input_data = $('#wp_pass_reset').serialize();   
            $.ajax({   
                type: "POST",   
                url:  "<?php echo get_permalink( $post->ID ); ?>",   
                data: input_data,   
                success: function(msg){   
                    $('#rsmsg').html(msg);   
                }   
            });   
            return false;   
        });   
        </script> 
                      
                     </div>
                     <div class="text-center">
                        <a href="<?php get_login_url(); ?>" class="icon-link icon-link-hover">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"></path>
                           </svg>
                           <span>返回登录</span>
                        </a>
                     </div>

                  </div>		  
            </div>
          </div>
          <div class="col-md-6">
            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
              <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('<?php echo randpic();?>')"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
		

<?php
   }
    }else{   
        wp_redirect(home_url()); 
		exit;
    }   
get_footer();
?>