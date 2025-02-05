<?php
/**
 * Plugin Name: Ses Botu
 * Plugin URI: https://yanetkileri.com
 * Description: OpenAI TTS API kullanarak makaleleri sese çeviren WordPress eklentisi
 * Version: 1.0.0
 * Author: YanEtkileri
 * Author URI: https://yanetkileri.com
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

class VideoBotu {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_create_audio', array($this, 'create_audio'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Ses Botu',
            'Ses Botu',
            'manage_options',
            'ses-botu',
            array($this, 'render_pending_page'),
            'dashicons-microphone'
        );

        add_submenu_page(
            'ses-botu',
            'İşlem Bekleyen Makaleler',
            'İşlem Bekleyen Makaleler',
            'manage_options',
            'ses-botu',
            array($this, 'render_pending_page')
        );

        add_submenu_page(
            'ses-botu',
            'İşlem Yapılmış Makaleler',
            'İşlem Yapılmış Makaleler',
            'manage_options',
            'ses-botu-completed',
            array($this, 'render_completed_page')
        );

        add_submenu_page(
            'ses-botu',
            'Ayarlar',
            'Ayarlar',
            'manage_options',
            'ses-botu-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('video_bot_settings', 'openai_api_key');
        register_setting('video_bot_settings', 'openai_voice_type', array('default' => 'shimmer'));
        register_setting('video_bot_settings', 'openai_model', array('default' => 'tts-1'));
        register_setting('video_bot_settings', 'posts_per_page', array('default' => 10, 'type' => 'integer'));
    }

    public function enqueue_admin_assets($hook) {
        if (!in_array($hook, array('toplevel_page_ses-botu', 'ses-botu_page_ses-botu-completed', 'ses-botu_page_ses-botu-settings'))) {
            return;
        }

        wp_enqueue_style(
            'video-bot-admin',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'video-bot-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'video-bot-player',
            plugin_dir_url(__FILE__) . 'assets/js/video-player.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'video-bot-admin',
            'videoBotAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('video-bot-nonce'),
                'apiKey' => get_option('openai_api_key'),
                'voiceType' => get_option('openai_voice_type', 'shimmer'),
                'model' => get_option('openai_model', 'tts-1')
            )
        );
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_script(
            'video-bot-player',
            plugin_dir_url(__FILE__) . 'assets/js/video-player.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function create_audio() {
        try {
            check_ajax_referer('video-bot-nonce', 'nonce');

            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
            $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
            $voice_type = isset($_POST['voice_type']) ? sanitize_text_field($_POST['voice_type']) : 'shimmer';
            $model = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : 'tts-1';

            if (!$post_id || empty($content)) {
                throw new Exception('Geçersiz makale ID\'si veya içerik');
            }

            $api_key = get_option('openai_api_key');
            if (empty($api_key)) {
                throw new Exception('OpenAI API anahtarı eksik');
            }

            error_log('OpenAI API isteği gönderiliyor...');
            
            // API isteği için veriyi hazırla
            $content = substr($content, 0, 4096); // OpenAI karakter sınırı
            $request_body = array(
                'model' => $model,
                'input' => $content,
                'voice' => $voice_type,
                'response_format' => 'mp3'
            );

            $response = wp_remote_post('https://api.openai.com/v1/audio/speech', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => wp_json_encode($request_body),
                'timeout' => 120,
                'data_format' => 'body'
            ));

            if (is_wp_error($response)) {
                error_log('API Hata Detayı: ' . print_r($response, true));
                throw new Exception('API Hatası: ' . $response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if ($response_code !== 200) {
                error_log('API Yanıt Kodu: ' . $response_code);
                error_log('API Yanıt İçeriği: ' . $response_body);
                throw new Exception('API Hatası: HTTP ' . $response_code);
            }

            error_log('Ses dosyası kaydediliyor...');

            $upload_dir = wp_upload_dir();
            $post_title = get_the_title($post_id);
            $sanitized_title = sanitize_title($post_title);
            $filename = $sanitized_title . '-' . $post_id . '-' . time() . '.mp3';
            $file_path = $upload_dir['path'] . '/' . $filename;

            // Ses içeriğini kaydet
            $result = file_put_contents($file_path, $response_body);
            if ($result === false) {
                throw new Exception('Ses dosyası kaydedilemedi. Dosya yolu: ' . $file_path);
            }

            error_log('Medya kütüphanesine ekleniyor...');

            $attachment = array(
                'post_mime_type' => 'audio/mpeg',
                'post_title' => $post_title . ' - Sesli Anlatım',
                'post_content' => sprintf(
                    '%s makalesinin sesli anlatımı. %s', 
                    $post_title,
                    wp_strip_all_tags(substr($content, 0, 200))
                ),
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
            if (is_wp_error($attach_id)) {
                throw new Exception('Medya yükleme hatası: ' . $attach_id->get_error_message());
            }

            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $file_path));

            $audio_url = wp_get_attachment_url($attach_id);

            // Makaleyi güncelle
            $post = get_post($post_id);
            $audio_player = sprintf('[audio src="%s"]', $audio_url);
            $updated_content = $audio_player . "\n\n" . $post->post_content;

            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $updated_content
            ));

            // Meta veriyi güncelle
            update_post_meta($post_id, '_video_bot_audio_url', $audio_url);

            // Alt etiketi ekle
            update_post_meta($attach_id, '_wp_attachment_image_alt', $post_title . ' - Sesli Anlatım');

            wp_send_json_success(array(
                'audio_url' => $audio_url,
                'message' => 'Ses başarıyla oluşturuldu'
            ));

        } catch (Exception $e) {
            error_log('Ses Botu Hatası: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    public function render_pending_page() {
        require_once plugin_dir_path(__FILE__) . 'templates/pending-page.php';
    }

    public function render_completed_page() {
        require_once plugin_dir_path(__FILE__) . 'templates/completed-page.php';
    }

    public function render_settings_page() {
        require_once plugin_dir_path(__FILE__) . 'templates/settings-page.php';
    }
}

function ses_botu() {
    return VideoBotu::get_instance();
}

add_action('plugins_loaded', 'ses_botu');