jQuery(document).ready(function($) {
    // Tab değiştirme
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        var target = $(this).attr('href').substring(1);
        $('.tab-pane').removeClass('active');
        $('#' + target).addClass('active');
    });

    // Ses oluşturma butonu işlevi
    $('.create-audio').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var postId = button.data('post-id');
        var content = button.data('content');

        console.log('İşlem başlatılıyor...');
        console.log('Post ID:', postId);
        console.log('Content uzunluğu:', content.length);

        if (!videoBotAdmin.apiKey) {
            alert('OpenAI API anahtarı eksik. Lütfen ayarlardan ekleyin.');
            return;
        }

        button.prop('disabled', true);
        button.text('Ses Oluşturuluyor...');

        $.ajax({
            url: videoBotAdmin.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'create_audio',
                post_id: postId,
                content: content,
                nonce: videoBotAdmin.nonce,
                voice_type: videoBotAdmin.voiceType,
                model: videoBotAdmin.model
            },
            success: function(response) {
                console.log('Sunucu yanıtı:', response);
                
                if (response.success) {
                    alert('Ses başarıyla oluşturuldu!');
                    button.closest('tr').fadeOut();
                } else {
                    alert('Hata: ' + (response.data || 'Bilinmeyen bir hata oluştu'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Hatası:', {
                    xhr: xhr.responseText,
                    status: status,
                    error: error
                });
                alert('İşlem sırasında bir hata oluştu: ' + error);
            },
            complete: function() {
                button.prop('disabled', false);
                button.text('Ses Oluştur');
            }
        });
    });

    // Video oluşturma butonu işlevi
    $('.create-video').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var postId = button.data('post-id');
        var content = button.data('content');

        console.log('İşlem başlatılıyor...');
        console.log('Post ID:', postId);
        console.log('Content uzunluğu:', content.length);

        if (!videoBotAdmin.apiKey) {
            alert('OpenAI API anahtarı eksik. Lütfen ayarlardan ekleyin.');
            return;
        }

        button.prop('disabled', true);
        button.text('Video Oluşturuluyor...');

        $.ajax({
            url: videoBotAdmin.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'create_video',
                post_id: postId,
                content: content,
                nonce: videoBotAdmin.nonce,
                voice_type: videoBotAdmin.voiceType,
                model: videoBotAdmin.model
            },
            success: function(response) {
                console.log('Sunucu yanıtı:', response);
                
                if (response.success) {
                    alert('Video başarıyla oluşturuldu!');
                    button.closest('tr').fadeOut();
                } else {
                    alert('Hata: ' + (response.data || 'Bilinmeyen bir hata oluştu'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Hatası:', {
                    xhr: xhr.responseText,
                    status: status,
                    error: error
                });
                alert('İşlem sırasında bir hata oluştu: ' + error);
            },
            complete: function() {
                button.prop('disabled', false);
                button.text('Ses Oluştur');
            }
        });
    });

    // Ses önizleme işlevi
    $('.preview-voice').on('click', function() {
        var voice = $(this).data('voice');
        var audio = $('#preview-audio');
        var source = audio.find('source');
        
        // Örnek ses dosyasının URL'sini ayarlayın
        source.attr('src', '/path/to/voice-samples/' + voice + '.mp3');
        audio[0].load();
        audio[0].play();
    });
}); 