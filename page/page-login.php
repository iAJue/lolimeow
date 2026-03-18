<?php
/**
 * Template Name: Boxmoe登录页
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
if(!isset($_SESSION))
session_start(); 
$redirect_to=''; 
if( isset($_POST['md_token']) && ($_POST['md_token'] == $_SESSION['md_token'])) {
  $error = '';
  $secure_cookie = false;
  $user_name = sanitize_user( $_POST['log'] );
  $user_password = $_POST['pwd'];
  if ( empty($user_name) || ! validate_username( $user_name ) ) {
    $error .= '<strong>错误</strong>：请输入有效的用户名。<br />';
    $user_name = '';
  }  
  if( empty($user_password) ) {
    $error .= '<strong>错误</strong>：请输入密码。<br />';
  } 
  if($error == '') {
    // If the user wants ssl but the session is not ssl, force a secure cookie.
    if ( !empty($user_name) && !force_ssl_admin() ) {
      if ( $user = get_user_by('login', $user_name) ) {
        if ( get_user_option('use_ssl', $user->ID) ) {
          $secure_cookie = true;
          force_ssl_admin(true);
        }
      }
    }	  
    $redirect_to = ''.site_url().'?page_id='.get_boxmoe('users_page').'';	
    if ( !$secure_cookie && is_ssl() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
    $secure_cookie = false;	
    $creds = array();
    $creds['user_login'] = $user_name;
    $creds['user_password'] = $user_password;
    $creds['remember'] = !empty( $_POST['rememberme'] );
    $user = wp_signon( $creds, $secure_cookie );
    if ( is_wp_error($user) ) {
      $error .= $user->get_error_message();
    }
    else {
      unset($_SESSION['md_token']);
      wp_safe_redirect($redirect_to);
    }
  }

  unset($_SESSION['md_token']);
}
$rememberme = !empty( $_POST['rememberme'] ); 
$token = md5(uniqid(rand(), true));
$_SESSION['md_token'] = $token;
?>
<?php 
if (!is_user_logged_in()) {?>
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
<section>  
<link rel='stylesheet' href="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/limonte-sweetalert2/11.4.4/sweetalert2.min.css" type='text/css' media='all' />
<script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/limonte-sweetalert2/11.4.4/sweetalert2.min.js"></script>
<div class="page-header min-vh-75">
<div class="container">
        <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                     <div class="text-center mb-7">
                        <h1 class="mb-1 text-gradient">欢迎回来</h1>
                        <p class="mb-0">
                        如果你还没有账户
                           <a href="<?php get_reg_url(); ?>" class="text-primary  text-gradient">点击注册</a>
                        </p>
                     </div>
                     <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" class="needs-validation mb-6">
                        <div class="mb-3">
                           <label for="log" class="form-label">电子邮件/用户名<span class="text-danger">*</span></label>
                           <input type="text"  name="log" id="log"  class="form-control" required="">
                        </div>
                        <div class="mb-3">
                           <label for="pwd" class="form-label">密码</label>
                              <input type="password" name="pwd" id="pwd" class="form-control" required="">
                        </div>
                        <div class="mb-3">
                           <div class="d-flex align-items-center justify-content-between">
                              <div class="form-check">
                                 <input class="form-check-input" type="checkbox"  id="rememberme" value="1" <?php checked( $rememberme ); ?>>
                                 <label class="form-check-label" for="rememberme">记住账号</label>
                              </div>

                              <div><a href="<?php get_reset_url(); ?>" class="text-primary  text-gradient">找回密码</a></div>
                           </div>
                        </div>

                        <div class="d-grid">
                        <input type="hidden" name="md_token" value="<?php echo $token; ?>" />
                        <input type="hidden" name="redirect_to" value="<?php if(!empty($redirect_to)){echo $redirect_to;}else{echo get_user_url();} ?>" />
                           <button class="btn btn-primary" type="submit">登录账号</button>
                        </div>
                     </form>
                  </div>
                  

          <div class="col-md-6">
            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
              <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('<?php echo randpic();?>')"></div>
            </div>
          </div>
        </div>
      </div>
	  </div>
<?php if(!empty($error)) {echo '<script type="text/javascript">Swal.fire("Oops...","'.$error.'", "error");</script>';}?>	  	
<?php get_footer(); } else {
echo "<script type='text/javascript'>window.location='".site_url()."?page_id=".get_boxmoe('users_page')."'</script>";
} ?>

	  