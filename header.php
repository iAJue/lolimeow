<?php
/**
 * @package lolimeow@boxmoe themes
 * @link https://www.boxmoe.com
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
?>
<!--
                ~~~~~~~  ~~~~~~~
              ~~~~(╯°□°）╯︵ ┻━┻~~~~
           ~~~~~~~~ (つ ◕_◕ )つ ~~~~~~~~~
      ~~~~~~愿代码有爱 永无BUG~~~~~
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                 (•̀ᴗ•́)و ̑̑加油！
-->
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo  boxmoe_title(); ?></title>
	  <?php echo boxmoe_keywords()?>
    <?php echo boxmoe_description()?>
    <?php echo boxmoe_favicon();?>
	  <?php wp_head(); ?>
	  <?php if(get_boxmoe('banner_height')){?><style>.section-blog-cover{height:<?php echo get_boxmoe('banner_height');?>px;}</style><?php }?>
	  <?php if(get_boxmoe('m_banner_height')){?><style>@media (max-width:767px){.section-blog-cover {height:<?php echo get_boxmoe('m_banner_height');?>px;}}</style><?php }?>

  <body>
    <?php boxmoe_render_preloader(); ?>
  <?php if(get_boxmoe('sakura_tree')){ ?>
      <div class="meiha1"></div>
      <div class="meiha"></div>
    <?php } ?>
  <?php echo boxmoe_load_lantern(); ?>
    <div id="boxmoe_theme_global">
      <section id="boxmoe_theme_header" class="fadein-top">
        <nav class="navbar navbar-expand-lg navbar-bg-box">
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
                  <li class="nav-item">
                    <a href="#" id="theme-mode-toggle" class="nav-link theme-mode-toggle btn" aria-label="切换深色模式" title="切换深色模式">
                      <i class="fa fa-moon-o"></i>
                    </a>
                  </li>
                  <?php if(get_boxmoe('sign_f')){ ?><?php if(!is_user_logged_in() ){ ?>
                  <li class="nav-item">
                    <div class="user-wrapper">
                      <div class="user-no-login">
                        <span class="user-login">
                          <a href="<?php get_login_url(); ?>?r=<?php get_user_url(); ?>" class="signin-loader z-bor">登录</a>
                          <b class="middle-text">
                            <span class="middle-inner">or</span></b>
                        </span>
                        <span class="user-reg">
                          <a href="<?php get_reg_url(); ?>" class="signup-loader l-bor">注册</a></span>
                      </div>
                      <i class="up-new"></i>
                    </div>
                  </li><?php }else{ ?>
                  <li class="nav-item dropdown dropdown-hover nav-item">
                    <a href="#" class="nav-link  dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa fa-user-circle-o"></i><?php $current_user = wp_get_current_user(); echo 'Hello！, ' . esc_html( $current_user->user_login );; ?></a>
                    <ul class="dropdown-menu">
                      <li>
                        <a href="<?php get_user_url(); ?>" class="dropdown-item">
                          <i class="fa fa-address-card-o"></i>会员中心</a>
                      </li>
                      <li>
                        <a href="<?php echo wp_logout_url( home_url() ); ?>" class="dropdown-item">
                          <i class="fa fa-sign-out"></i>注销登录</a>
                      </li><?php } ?>
                    </ul>
                  </li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          </div>
        </nav>
      </section>
      <section class="section-blog-cover fadein-top" <?php echo boxmoe_banner();?>>
        <div class="site-main">
          <h2 class="text-gradient"><?php if(get_boxmoe('banner_font')){echo get_boxmoe('banner_font');}?></h2>
          <?php if(get_boxmoe('hitokoto_on')){?>
          <h1 class="main-title">
            <i class="fa fa-star spinner"></i>
            <span id="hitokoto" class="text-gradient"></span>
          </h1> 
          <?php }?>
        </div>
        <div class="separator separator-bottom separator-skew">
          <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
              <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
            </defs>
            <g class="parallax">
              <use xlink:href="#gentle-wave" x="48" y="0"></use>
              <use xlink:href="#gentle-wave" x="48" y="3"></use>
              <use xlink:href="#gentle-wave" x="48" y="5"></use>
              <use xlink:href="#gentle-wave" x="48" y="7"></use>
            </g>
          </svg>
        </div>
      </section>

