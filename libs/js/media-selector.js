
jQuery(document).ready(function($){
    // 画像アップローダー
    var mbMediaFrame,
        currentP,
        currentLabel;
    $('.bbt-meta-fields').on('click', 'p.image-placeholder .button-image-add', function(e){
        e.preventDefault();
        currentP = $(this).parents('p.image-placeholder');
        // すでに開いていたら再利用
        if(mbMediaFrame){
            mbMediaFrame.open();
            return;
        }
        //メディアフレームを初期化
        mbMediaFrame = wp.media.frames.mbMediaFrame = wp.media({
            className: 'media-frame mb-media-frame',
            frame: 'select',
            multiple: false,
            title: '使用する画像をアップロードまたは選択してください。',
            library: {
                type: 'image'
            },
            button: {
                text: '選択した画像を挿入'
            }
        });
        // 選択した場合のイベントをバインド
        mbMediaFrame.on('select', function(){
            // アタッチメントの情報を取得
            var attachment = mbMediaFrame.state().get('selection').first().toJSON(),
                img;
            // アタッチメント情報を保存する
            if(attachment.sizes.thumbnail){
                //サムネイルがあればその画像
                img = '<img src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.title + '" title="' + attachment.title + '" width="' + attachment.sizes.thumbnail.width + '" height="' + attachment.sizes.thumbnail.width + '" />';
            }else{
                //なければフルサイズを取得
                img = '<img src="' + attachment.sizes.full.url + '" alt="' + attachment.title + '" title="' + attachment.title + '" width="' + attachment.sizes.full.width + '" height="' + attachment.sizes.full.width + '" />';
            }
            $(currentP).find('span.image-placeholder').append(img);
            $(currentP).addClass('active').effect('highlight').find('input[type=hidden]').val(attachment.id);
        });
        // メディアフレームを開く
        mbMediaFrame.open();
    });

    // 画像を削除する
    $('.bbt-meta-fields').on('click', 'p.image-placeholder .button-image-delete', function(e){
        e.preventDefault();
        var p = $(this).parents('p.image-placeholder');
        $(p).find('span.image-placeholder img').remove();
        $(p).find('input[type=hidden]').val('');
        $(p).removeClass('active').effect('highlight');
    });
    $(window).on('click', 'a.media-selector', function(e){
        e.preventDefault();
    });
});