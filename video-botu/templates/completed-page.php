<div class="wrap">
    <h1>İşlem Yapılmış Makaleler</h1>

    <?php
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $posts_per_page = get_option('posts_per_page', 10);

    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'meta_query' => array(
            array(
                'key' => '_video_bot_audio_url',
                'compare' => 'EXISTS'
            )
        )
    ));

    $total_posts = count(get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_video_bot_audio_url',
                'compare' => 'EXISTS'
            )
        )
    )));

    $total_pages = ceil($total_posts / $posts_per_page);

    if ($posts): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Tarih</th>
                    <th>Video</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): 
                    $video_url = get_post_meta($post->ID, '_video_bot_audio_url', true);
                ?>
                    <tr>
                        <td><?php echo esc_html($post->post_title); ?></td>
                        <td><?php echo get_the_date('', $post); ?></td>
                        <td>
                            <?php echo wp_video_shortcode(array('src' => $video_url)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p>İşlem yapılmış makale bulunamadı.</p>
    <?php endif; ?>
</div> 