<?php
defined('ABSPATH') or die('This file can not be loaded directly.');
class widget_githubrepos extends WP_Widget {

    function __construct(){
        parent::__construct('widget_github_repos', 'Boxmoe Github项目', array('classname' => 'widget_meal'));
    }

    function widget($args, $instance) {
        extract($args);
        
        $title = apply_filters('widget_name', $instance['title']);
        $github_user = $instance['github_user'];
        $sort_by = $instance['sort_by'];
        
        // 获取缓存数据
        $cache_key = 'github_repos_' . $github_user . '_' . $sort_by;
        $repos = get_transient($cache_key);
        
        if(false === $repos) {
            // 如果缓存不存在或已过期,重新获取数据
            $api_url = "https://api.github.com/search/repositories?q=user:{$github_user}&sort={$sort_by}&order=desc&per_page=5";
            $response = wp_remote_get($api_url, array(
                'headers' => array('Accept' => 'application/vnd.github.v3+json')
            ));
            
            if(!is_wp_error($response)) {
                $repos = json_decode(wp_remote_retrieve_body($response));
                // 设置12小时缓存
                set_transient($cache_key, $repos, 24 * HOUR_IN_SECONDS);
            }
        }

        echo $before_widget;
        echo '<h4 class="widget-title">'.$title.'</h4>';
        
        if($repos && isset($repos->items)) {
            foreach($repos->items as $repo) {
                echo '<div class="github-repo-item">';
                echo '<a href="'.$repo->html_url.'" target="_blank">';
                echo '<div class="repo-name">'.$repo->name.'</div>';
                echo '<div class="repo-desc">'.($repo->description ? $repo->description : '暂无描述').'</div>';
                echo '<div class="repo-meta">';
                echo '<span class="updated" title="'.date('Y-m-d', strtotime($repo->updated_at)).'"><i class="fa fa-clock-o"></i> '.human_time_diff(strtotime($repo->updated_at)).'前</span>';
                echo '<span class="stars" title="Star数"><i class="fa fa-star"></i> '.$repo->stargazers_count.'</span>';
                echo '<span class="forks" title="Fork数"><i class="fa fa-code-fork"></i> '.$repo->forks_count.'</span>';
                echo '<span class="language" title="主要编程语言"><i class="fa fa-code"></i> '.($repo->language ? $repo->language : 'other').'</span>';
                echo '</div>';
                
                // 获取仓库活跃度数据缓存
                $activity_cache_key = 'github_activity_' . $github_user . '_' . $repo->name;
                $activity_data = get_transient($activity_cache_key);
                
                if(false === $activity_data) {
                    // 如果缓存不存在，从API获取数据
                    $activity_url = str_replace('api.github.com/search', 'api.github.com/repos/'.$github_user, $repo->url).'/stats/participation';
                    $activity_response = wp_remote_get($activity_url, array(
                        'headers' => array('Accept' => 'application/vnd.github.v3+json')
                    ));
                    
                    if(!is_wp_error($activity_response)) {
                        $activity_data = json_decode(wp_remote_retrieve_body($activity_response));
                        // 设置24小时缓存
                        set_transient($activity_cache_key, $activity_data, 24 * HOUR_IN_SECONDS);
                    }
                }
                
                if($activity_data && isset($activity_data->all)) {
                    // 计算最近12周的平均提交数
                    $recent_commits = array_slice($activity_data->all, -12);
                    $avg_commits = array_sum($recent_commits) / count($recent_commits);
                    
                    // 只有当平均每周提交数大于1时才显示活跃度图表
                    if($avg_commits > 1) {
                        echo '<div class="repo-activity" title="最近52周的活跃度">';
                        echo '<div class="activity-graph">';
                        $max_value = max($recent_commits);
                        foreach($recent_commits as $week_commits) {
                            $height = $max_value > 0 ? ($week_commits / $max_value * 100) : 0;
                            echo '<div class="activity-bar" style="height: '.$height.'%" title="'.$week_commits.' commits"></div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<div class="no-repos">未找到仓库或API请求失败</div>';
        }
        
        echo $after_widget;
    }

    function form($instance) {
        $defaults = array(
            'title' => '我的Github项目',
            'github_user' => 'iAJue',
            'sort_by' => 'stars'
        );
        $instance = wp_parse_args((array) $instance, $defaults);
?>
        <p>
            <label>
                <?php echo __('标题：', 'boxmoe-com') ?>
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" class="widefat" />
            </label>
        </p>
        <p>
            <label>
                <?php echo __('Github用户名：', 'boxmoe-com') ?>
                <input id="<?php echo $this->get_field_id('github_user'); ?>" name="<?php echo $this->get_field_name('github_user'); ?>" type="text" value="<?php echo $instance['github_user']; ?>" class="widefat" />
            </label>
        </p>
        <p>
            <label>
                <?php echo __('排序方式：', 'boxmoe-com') ?>
                <select id="<?php echo $this->get_field_id('sort_by'); ?>" name="<?php echo $this->get_field_name('sort_by'); ?>" class="widefat">
                    <option value="stars" <?php selected($instance['sort_by'], 'stars'); ?>>Stars</option>
                    <option value="updated" <?php selected($instance['sort_by'], 'updated'); ?>>最近更新</option>
                    <option value="created" <?php selected($instance['sort_by'], 'created'); ?>>创建时间</option>
                </select>
            </label>
        </p>
<?php
    }
}

// 添加样式
add_action('wp_head', function() {
    echo '<style>

    .github-repo-item {
        margin-bottom: 20px;
        padding: 20px;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .github-repo-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .github-repo-item a {
        text-decoration: none;
        color: inherit;
    }
    .repo-name {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
    }
    .repo-desc {
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        opacity: 0.85;
    }
    .repo-meta {
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .repo-meta span {
        display: inline-flex;
        align-items: center;
        opacity: 0.85;
        margin-right: 12px;
    }
    .repo-meta i {
        margin-right: 4px;
        font-size: 14px;
    }
    .repo-activity {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    .activity-graph {
        display: flex;
        align-items: flex-end;
        height: 40px;
        gap: 3px;
    }
    .activity-bar {
        flex: 1;
        background: #9BE9A8;
        opacity: 0.7;
        border-radius: 2px;
        min-height: 1px;
        transition: all 0.3s ease;
    }
    .activity-bar:hover {
        opacity: 1;
    }
    .no-repos {
        text-align: center;
        color: #777;
        padding: 20px 0;
    }
    </style>';
});