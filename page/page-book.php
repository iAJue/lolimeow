<?php
/**
 * Template Name: Boxmoe书房
 * Description: 书房页面模板
 * @link https://MoeJue.cn
 * author 阿珏酱
 */

defined('ABSPATH') or die('This file can not be loaded directly.');
get_header();

preg_match('/<code class="language-json">(.*)<\/code>/s', get_the_content(), $matches);
if (!empty($matches)) {
    $json_data = json_decode(html_entity_decode($matches[1]), true, 512, JSON_UNESCAPED_UNICODE);
    $content = preg_replace('/<code class="language-json">(.*)<\/code>/s', '', get_the_content());
}else{
    exit('数据格式错误');
}

$current_page = isset($_GET['pn']) ? (int) $_GET['pn'] : 1;
$items_per_page = 24;
$offset = ($current_page - 1) * $items_per_page;
$paginated_data = array_slice($json_data, $offset, $items_per_page);
$total_pages = ceil(count($json_data) / $items_per_page);

function bangumi_pagination($total_pages, $current_page) {
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '"><a class="page-link" href="/book/?pn=' . $i .'">' . $i . '</a></li>';
    }
}

?>
 
<section class="section-blog-breadcrumb container">
    <div class="breadcrumb-head">
        <span>
            <i class="fa fa-home"></i>Page</span>
    </div>
</section>
 
<section id="boxmoe_theme_container">
    <div class="container">
        <div class="post-single blog-border">
            <?php echo $content; ?>
            <div class="bangumi-item">
             <?php foreach ($paginated_data as $book): ?>
                <div class="bangumi-card" style="max-width:140px">
                <a href="<?php echo esc_url($book['url']); ?>" target="_blank">
                    <img src="<?php echo esc_url($book['image']); ?>" alt="<?php echo esc_attr($book['name']); ?>" referrerpolicy="no-referrer" class="bangumi-cover" style="height:160px">
                    <h3 class="bangumi-title"><?php echo esc_html($book['name']); ?></h3>
                </a>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="col-lg-12 col-md-12 pagenav">
                <nav class="d-flex justify-content-center">
                    <ul class="pagination">
                    <?php bangumi_pagination($total_pages, $current_page); ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
 </section>
 
 <?php
 get_footer();
 ?>