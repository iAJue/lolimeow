<?php
defined('ABSPATH') or die('This file can not be loaded directly.');
class widget_githubactivities extends WP_Widget {

    function __construct(){
        parent::__construct('widget_github_activities', 'Boxmoe Github活跃足迹', array('classname' => 'widget_meal'));
    }

    function widget($args, $instance) {
        extract($args);
        
        $title = apply_filters('widget_name', $instance['title']);
        $github_user = $instance['github_user'];
        $limit = $instance['limit'];
        
        // 获取缓存数据
        $cache_key = 'github_activities_' . $github_user;
        $activities = get_transient($cache_key);
        
        if(false === $activities) {
            // 如果缓存不存在或已过期,重新获取数据
            $api_url = "https://api.github.com/users/{$github_user}/events?per_page={$limit}";
            $response = wp_remote_get($api_url, array(
                'headers' => array('Accept' => 'application/vnd.github.v3+json')
            ));
            
            if(!is_wp_error($response)) {
                $activities = json_decode(wp_remote_retrieve_body($response));
                // 设置12小时缓存
                set_transient($cache_key, $activities, 12 * HOUR_IN_SECONDS);
            }
        }

        echo $before_widget;
        echo '<h4 class="widget-title">'.$title.'</h4>';
        
        if($activities && is_array($activities)) {
            echo '<div class="github-activities">';
            $count = 0;
            foreach($activities as $activity) {
                if($count >= $limit) break;
                
                $event_type = $this->get_event_type($activity->type);
                if(!$event_type) continue;
                
                $repo_name = $activity->repo->name;
                $created_at = date('Y-m-d H:i', strtotime($activity->created_at));
                
                $payload = json_decode(json_encode($activity->payload), true);
                $description = '';
                $title = '';
                
                if($activity->type === 'IssuesEvent') {
                    $title = isset($payload['issue']['title']) ? $payload['issue']['title'] : '';
                    $description = isset($payload['issue']['body']) ? $payload['issue']['body'] : '';
                } elseif($activity->type === 'IssueCommentEvent') {
                    $title = isset($payload['issue']['title']) ? $payload['issue']['title'] : '';
                    $description = isset($payload['comment']['body']) ? $payload['comment']['body'] : '';
                } else {
                    if(isset($payload['description'])) {
                        $description = $payload['description'];
                    } elseif(isset($payload['commits']) && !empty($payload['commits'])) {
                        $description = $payload['commits'][0]['message'];
                    }
                }
                
                echo '<div class="item">';
                echo '<div class="icon"><i class="fa '.$event_type['icon'].'"></i></div>';
                echo '<div class="content">';
                echo '<div class="title">'.$event_type['text'].' <a href="https://github.com/'.$repo_name.'" target="_blank">'.$repo_name.'</a></div>';
                if(($activity->type === 'IssuesEvent' || $activity->type === 'IssueCommentEvent') && $title) {
                    echo '<div class="issue-title">'.esc_html($title).'</div>';
                }
                if($description) {
                    echo '<div class="desc">'.esc_html(wp_trim_words($description, 20)).'</div>';
                }
                echo '<div class="activity-time"><i class="fa fa-clock-o"></i> '.human_time_diff(strtotime($activity->created_at)).'前</div>';
                echo '</div>';
                echo '</div>';
                
                $count++;
            }
            echo '</div>';
        } else {
            echo '<div class="no-activities">未找到活动记录或API请求失败</div>';
        }
        
        echo $after_widget;
    }

    private function get_event_type($type) {
        $event_types = array(
            'PushEvent' => array(
                'text' => '推送到仓库',
                'icon' => 'fa-code-fork'
            ),
            'CreateEvent' => array(
                'text' => '创建',
                'icon' => 'fa-plus-circle'
            ),
            'IssuesEvent' => array(
                'text' => '发起Issue',
                'icon' => 'fa-exclamation-circle'
            ),
            'PullRequestEvent' => array(
                'text' => '发起PR',
                'icon' => 'fa-code-fork'
            ),
            'WatchEvent' => array(
                'text' => 'Star了仓库',
                'icon' => 'fa-star'
            ),
            'ForkEvent' => array(
                'text' => 'Fork了仓库',
                'icon' => 'fa-code-fork'
            ),
            'IssueCommentEvent' => array(
                'text' => '评论了Issue',
                'icon' => 'fa-comment'
            ),
            'DeleteEvent' => array(
                'text' => '删除',
                'icon' => 'fa-trash'
            ),
            'ReleaseEvent' => array(
                'text' => '发布版本',
                'icon' => 'fa-tag'
            )
        );
        
        return isset($event_types[$type]) ? $event_types[$type] : null;
    }

    function form($instance) {
        $defaults = array(
            'title' => '我的Github足迹',
            'github_user' => 'iAJue',
            'limit' => 5
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
                <?php echo __('显示数量：', 'boxmoe-com') ?>
                <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" class="widefat" min="1" max="10" />
            </label>
        </p>
<?php
    }
}



// 添加样式
add_action('wp_head', function() {
    echo '<style>
    .item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 16px;
        background: linear-gradient(145deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03));
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        background: linear-gradient(145deg, rgba(255,255,255,0.12), rgba(255,255,255,0.05));
        border-color: rgba(155,233,168,0.3);
    }
    .icon {
        width: 40px;
        height: 40px;
        margin-right: 20px;
        background: linear-gradient(135deg, rgba(155,233,168,0.2), rgba(155,233,168,0.1));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(155,233,168,0.1);
    }
    .icon i {
        font-size: 16px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .content {
        flex: 1;
    }
    .title {
        font-size: 15px;
        margin-bottom: 8px;
        font-weight: 500;
        line-height: 1.4;
    }
    .title a {
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        padding-bottom: 2px;
    }
    .title a:hover {
        text-shadow: 0 0 8px rgba(155,233,168,0.3);
    }
    .title a::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background: #7BC98C;
        transition: width 0.3s ease;
    }
    .title a:hover::after {
        width: 100%;
    }
    .issue-title {
        font-size: 14px;
        font-weight: 500;
    }
    .desc {
        font-size: 13px;
        margin: 5px 0;
        line-height: 1.5;
    }
    .time {
        font-size: 13px;
        display: flex;
        align-items: center;
        margin-top: 8px;
        color: rgba(255,255,255,0.5);
    }
    .time i {
        margin-right: 6px;
        font-size: 12px;
        opacity: 0.8;
    }
    .no-activities {
        text-align: center;
        color: rgba(255,255,255,0.5);
        padding: 30px 0;
        font-size: 14px;
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        border: 1px dashed rgba(255,255,255,0.1);
    }
    </style>';
});