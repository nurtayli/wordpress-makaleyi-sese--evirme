<div class="wrap">
    <h1>Ses Botu</h1>

    <?php if (!get_option('openai_api_key')): ?>
        <div class="notice notice-warning">
            <p>OpenAI API anahtarını ayarlardan eklemelisiniz.</p>
        </div>
    <?php endif; ?>

    <h2 class="nav-tab-wrapper">
        <a href="#makaleler-tab" class="nav-tab nav-tab-active">Makaleler</a>
        <a href="#ayarlar-tab" class="nav-tab">Ayarlar</a>
    </h2>

    <div class="tab-content">
        <div id="makaleler-tab" class="tab-pane active">
            <h2>Ses Eklenmemiş Makaleler</h2>
            <?php
            // Sayfalama için gerekli değişkenler
            $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
            $posts_per_page = 10;

            // Makaleleri getir
            $posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'meta_query' => array(
                    array(
                        'key' => '_ses_bot_audio_url',
                        'compare' => 'NOT EXISTS'
                    )
                )
            ));

            // Toplam makale sayısı
            $total_posts = count(get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_ses_bot_audio_url',
                        'compare' => 'NOT EXISTS'
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
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo esc_html($post->post_title); ?></td>
                                <td><?php echo get_the_date('', $post); ?></td>
                                <td>
                                    <button class="button create-audio" 
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        data-content="<?php echo esc_attr(wp_strip_all_tags($post->post_content)); ?>">
                                        Ses Oluştur
                                    </button>
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
                <p>Ses eklenmemiş makale bulunamadı.</p>
            <?php endif; ?>
        </div>

        <div id="ayarlar-tab" class="tab-pane">
            <form method="post" action="options.php">
                <?php 
                settings_fields('ses_bot_settings');
                do_settings_sections('ses_bot_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="openai_api_key">OpenAI API Anahtarı</label>
                        </th>
                        <td>
                            <input type="text" 
                                id="openai_api_key" 
                                name="openai_api_key" 
                                value="<?php echo esc_attr(get_option('openai_api_key')); ?>" 
                                class="regular-text"
                            />
                            <p class="description">
                                <a href="https://platform.openai.com/api-keys" target="_blank">
                                    API anahtarını buradan alabilirsiniz
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Ayarları Kaydet'); ?>
            </form>
        </div>
    </div>
</div>

<style>
.tab-pane {
    display: none;
    padding-top: 20px;
}
.tab-pane.active {
    display: block;
}
.tablenav-pages {
    margin: 1em 0;
}
.tablenav-pages a,
.tablenav-pages span {
    padding: 5px 10px;
    margin: 0 5px;
    border: 1px solid #ddd;
    text-decoration: none;
}
.tablenav-pages .current {
    background: #0073aa;
    color: white;
    border-color: #0073aa;
}
</style> 