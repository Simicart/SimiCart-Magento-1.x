/**
 * Created by frank on 1/26/18.
 */



function onchangeNoticeType(type) {
    switch (type) {
        case '1':
            $('product_id').up('tr').show();
            $('product_id').className = 'required-entry input-text';
            $('category_id').up('tr').hide();
            $('category_id').className = 'input-text';
            $('notice_url').up('tr').hide();
            $('notice_url').className = 'input-text';
            break;
        case '2':
            $('category_id').up('tr').show();
            $('category_id').className = 'required-entry input-text';
            $('product_id').up('tr').hide();
            $('product_id').className = 'input-text';
            $('notice_url').up('tr').hide();
            $('notice_url').className = 'input-text';
            break;
        case '3':
            $('notice_url').up('tr').show();
            $('notice_url').className = 'required-entry input-text';
            $('product_id').up('tr').hide();
            $('product_id').className = 'input-text';
            $('category_id').up('tr').hide();
            $('category_id').className = 'input-text';
            break;
        default:
            $('product_id').up('tr').show();
            $('product_id').className = 'required-entry input-text';
            $('category_id').up('tr').hide();
            $('category_id').className = 'input-text';
            $('notice_url').up('tr').hide();
            $('notice_url').className = 'input-text';
    }
}


function previewNoti(type) {
    switch (type) {
        case '1':
            $('div_preview_notification').show();
            changeDeviceType($('device_type').getValue());
            changeShowPopup($('show_popup').getValue());
            changeNoticeTitle($('notice_title').getValue());
            changeMessage($('notice_content').getValue());
            if(image_url_uploaded){
                $$(".img_popup").each( function(img){ img.src=image_url_uploaded } );
            }
            if(canifa_icon){
                $$(".img_icon").each( function(img){ img.src=canifa_icon } );
            }
            break;
        case '0':
            $('div_preview_notification').hide();
            break;
        default:
            $('div_preview_notification').hide();
    }
}

function changeDeviceType(type) {
    if ($('preview_notification').getValue() != '0') {

        var top_andorid_preview = $('top_andorid_preview');
        var top_ios_preview = $('top_ios_preview');
        var popup_android_preview = $('popup_android_preview');
        var popup_ios_preview = $('popup_ios_preview');

        var is_show_popup = $('show_popup').getValue();

        switch (type) {

            case '0':
                // show both android and ios devices
                top_andorid_preview.show();
                top_ios_preview.show();
                if(is_show_popup == '1'){
                    popup_android_preview.show();
                    popup_ios_preview.show();
                }else{
                    popup_android_preview.hide;
                    popup_ios_preview.hide();
                }

                break;
            case '1':
                // ios
                top_ios_preview.show();
                if(is_show_popup == '1'){
                    popup_ios_preview.show();
                }else{
                    popup_ios_preview.hide();
                }

                top_andorid_preview.hide();
                popup_android_preview.hide();

                break;
            case '2':
                // android
                top_andorid_preview.show();
                if(is_show_popup == '1'){
                    popup_android_preview.show();
                }else{
                    popup_android_preview.hide();
                }

                top_ios_preview.hide();
                popup_ios_preview.hide();
                break;
        }

    }

}

function changeShowPopup(type) {
    if ($('preview_notification').getValue() != '0') {

        var popup_preview_android = $('popup_android_preview');
        var popup_preview_ios = $('popup_ios_preview');
        var device_type = $('device_type').getValue();

        if (type == '1') {
            // show popup

            if (device_type == '0') {
                // show both android and ios devices
                popup_preview_android.show();
                popup_preview_ios.show();
            } else if (device_type == '1') {
                // only show ios
                popup_preview_ios.show();
                popup_preview_android.hide();
            }
            else if (device_type == '2') {
                // only show android
                popup_preview_android.show();
                popup_preview_ios.hide();
            }
        } else {
            // don't show popup
            popup_preview_android.hide();
            popup_preview_ios.hide();
        }
    }
}

function changeNoticeTitle(title) {

    if ($('preview_notification').getValue() != '0') {

        var title_android_top = $('title_android_top');
        title_android_top.innerHTML = title;

        var title_android_popup = $('title_android_popup');
        title_android_popup.innerHTML = title;

        var title_ios_popup = $('title_ios_popup');
        title_ios_popup.innerHTML = title;

        var title_ios_top = $('title_ios_top');
        title_ios_top.innerHTML = title;
    }

}

function changeMessage(message) {

    if ($('preview_notification').getValue() != '0') {
        console.log('show preview yes');
        var message_android_top = $('message_android_top');
        message_android_top.innerHTML = message;

        var message_ios_top = $('message_ios_top');
        message_ios_top.innerHTML = message;

        var message_android_popup = $('message_android_popup');
        message_android_popup.innerHTML = message;

        var message_ios_popup = $('message_ios_popup');
        message_ios_popup.innerHTML = message;
    }

}

function changeImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            image_url_uploaded = e.target.result;
            $$(".img_popup").each( function(img){ img.src=e.target.result } );

        }

        reader.readAsDataURL(input.files[0]);
    }
}
