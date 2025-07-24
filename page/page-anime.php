<?php
/**
 * Template Name: BoxmoeBiliBili番剧
 * Description: 番剧页面模板
 * @link https://MoeJue.cn
 * author 阿珏酱
 */
defined('ABSPATH') or die('This file can not be loaded directly.');

 get_header();

// 获取番剧数据
function get_bangumi_data($vmid = 142741587, $pn = 1) {
    $cache_key = 'bangumi_data_' . $vmid . '_' . $pn;
    $cache = get_transient($cache_key);
    if (false !== $cache) {
        return [
            'list' => $cache['list'] ?? [],
            'total' => $cache['total'] ?? 0
        ];
    }
    $api_url = 'https://api.bilibili.com/x/space/bangumi/follow/list?vmid=' . $vmid . '&type=1&pn=' . $pn . '&ps=25&playform=web';
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        return [];
    }
    $data = wp_remote_retrieve_body($response);
    $json = json_decode($data, true);
    $filtered_data = [];
    foreach ($json['data']['list'] as $item) {
        $filtered_data[] = [
            'cover' => $item['cover'],
            'url' => $item['url'],
            'title' => $item['title']
        ];
    }
    set_transient($cache_key, ['list' => $filtered_data, 'total' => $json['data']['total']] ?? [], DAY_IN_SECONDS);
    return [
        'list' => $filtered_data,
        'total' => $json['data']['total'] ?? 0
    ];
}
$content = get_the_content();
preg_match('/vmid=(\d+)/', $content, $matches);
if (!empty($matches)) {
    $vmid = $matches[1];
    $content = preg_replace('/vmid=' . $vmid . '/', '', $content);
}else{
    exit('数据格式错误');
}

$current_page = isset($_GET['pn']) ? (int) $_GET['pn'] : 1;
$bangumi_data = get_bangumi_data($vmid,$current_page);
$bangumi_list = $bangumi_data['list'];
$total_pages = ceil($bangumi_data['total'] / 25);

function bangumi_pagination($total_pages, $current_page) {
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '"><a class="page-link" href="/Anime/?pn=' . $i .'">' . $i . '</a></li>';
    }
}

?>
 
<section class="section-blog-breadcrumb container">
    <div class="breadcrumb-head">
        <span>
            <i class="fa fa-home"></i>番剧列表</span>
    </div>
</section>
 
<section id="boxmoe_theme_container">
    <div class="container">
        <div class="post-single blog-border">
            <?php echo $content; ?>
            <div class="bangumi-item">
             <?php foreach ($bangumi_list as $bangumi): ?>
                <div class="bangumi-card">
                <a href="<?php echo esc_url($bangumi['url']); ?>" target="_blank">
                    <img src="<?php echo esc_url($bangumi['cover']); ?>@308w_410h_1c.avif" alt="<?php echo esc_attr($bangumi['title']); ?>" referrerpolicy="no-referrer" class="bangumi-cover">
                    <h3 class="bangumi-title"><?php echo esc_html($bangumi['title']); ?></h3>
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