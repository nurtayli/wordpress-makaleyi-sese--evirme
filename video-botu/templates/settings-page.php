<div class="wrap">
    <h1>Ses Botu Ayarları</h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('video_bot_settings');
        do_settings_sections('video_bot_settings');
        ?>

        <div class="card">
            <h2>OpenAI Ayarları</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="openai_api_key">API Anahtarı</label>
                    </th>
                    <td>
                        <input type="password" 
                            id="openai_api_key" 
                            name="openai_api_key" 
                            value="<?php echo esc_attr(get_option('openai_api_key')); ?>" 
                            class="regular-text"
                        />
                        <p class="description">
                            <a href="https://platform.openai.com/api-keys" target="_blank">
                                OpenAI API anahtarınızı buradan alabilirsiniz
                            </a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="openai_model">Model</label>
                    </th>
                    <td>
                        <select id="openai_model" name="openai_model">
                            <option value="tts-1" <?php selected(get_option('openai_model'), 'tts-1'); ?>>
                                TTS-1 (Standart)
                            </option>
                            <option value="tts-1-hd" <?php selected(get_option('openai_model'), 'tts-1-hd'); ?>>
                                TTS-1-HD (Yüksek Kalite)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="openai_voice_type">Ses Tipi</label>
                    </th>
                    <td>
                        <select id="openai_voice_type" name="openai_voice_type">
                            <option value="alloy" <?php selected(get_option('openai_voice_type'), 'alloy'); ?>>Alloy</option>
                            <option value="echo" <?php selected(get_option('openai_voice_type'), 'echo'); ?>>Echo</option>
                            <option value="fable" <?php selected(get_option('openai_voice_type'), 'fable'); ?>>Fable</option>
                            <option value="onyx" <?php selected(get_option('openai_voice_type'), 'onyx'); ?>>Onyx</option>
                            <option value="nova" <?php selected(get_option('openai_voice_type'), 'nova'); ?>>Nova</option>
                            <option value="shimmer" <?php selected(get_option('openai_voice_type', 'shimmer'), 'shimmer'); ?>>Shimmer</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="card">
            <h2>Genel Ayarlar</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="posts_per_page">Sayfa Başına Makale</label>
                    </th>
                    <td>
                        <input type="number" 
                            id="posts_per_page" 
                            name="posts_per_page" 
                            value="<?php echo esc_attr(get_option('posts_per_page', 10)); ?>" 
                            min="1" 
                            max="100"
                            class="small-text"
                        />
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button('Ayarları Kaydet'); ?>
    </form>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
    padding: 20px;
}
.card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
</style> 