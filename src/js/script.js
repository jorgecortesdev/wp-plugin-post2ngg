jQuery(document).ready(function($) {
    jQuery('#tabs').tabs();

    jQuery('#single_post_form').submit(function(e) {
        e.preventDefault();

        jQuery('#post_to_transform').attr('disabled', true);
        jQuery('#post2ngg_single_submit').attr('disabled', true);
        jQuery('#post2ngg_category_submit').attr('disabled', true);

        var input_post = jQuery('#post_to_transform');
        if (input_post.val() == 0) {
            var post_html = post_tpl({
                error: 'Invalid post id.'
            });
            jQuery('#post2ngg_post').html(post_html);
            jQuery('#post_to_transform').attr('disabled', false);
            jQuery('#post2ngg_single_submit').attr('disabled', false);
            jQuery('#post2ngg_category_submit').attr('disabled', false);
            return true;
        }

        var post_id = input_post.val();
        var posts = [
            post_id
        ];
        single_post(posts, 0);
    });

    jQuery('#category_posts_form').submit(function(e) {
        e.preventDefault();

        var categories = [];
        $('.categorychecklist :checked').each(function() {
            categories.push($(this).val());
        });

        jQuery.ajax({
            dataType: 'json',
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'post2ngg_action_process_categories',
                categories: categories
            }
        }).done(function(response) {
            single_post(response.posts, 0);
        });

        jQuery('#post_to_transform').attr('disabled', true);
        jQuery('#post2ngg_single_submit').attr('disabled', true);
        jQuery('#post2ngg_category_submit').attr('disabled', true);
    });
});

function single_post(posts, counter) {
    post_id = posts[counter];

    if (posts.length == counter) {
        return true;
    }

    var post = jQuery("#post-template").html();
    var post_tpl = Handlebars.compile(post);

    jQuery.ajax({
        dataType: 'json',
        type: 'post',
        url: ajaxurl,
        data: {
            action: 'post2ngg_action_create_post_gallery',
            post_id: post_id
        }
    }).done(function(response) {
        if (response == undefined) {
            var post_html = post_tpl({
                error: 'Something went terrible wrong.'
            });
            jQuery('#post2ngg_post').append(post_html);
            jQuery('#post_to_transform').attr('disabled', false);
            jQuery('#post2ngg_single_submit').attr('disabled', false);
            jQuery('#post2ngg_category_submit').attr('disabled', false);
        } else if (response.post_images == undefined) {
            var post_html = post_tpl({
                error: response.error
            });
            jQuery('#post2ngg_post').append(post_html);
            jQuery('#post_to_transform').attr('disabled', false);
            jQuery('#post2ngg_single_submit').attr('disabled', false);
            jQuery('#post2ngg_category_submit').attr('disabled', false);
        } else {
            var post_html = post_tpl(response);

            jQuery('#post2ngg_post').append(post_html);
            jQuery("#progressbar-" + post_id).progressbar({
                value: false
            });

            import_image(response, 0);

            jQuery('#post_to_transform').val('');
            jQuery('#post_to_transform').attr('disabled', false);
            jQuery('#post2ngg_single_submit').attr('disabled', false);
            jQuery('#post2ngg_category_submit').attr('disabled', false);
        }
        jQuery(document).scrollTop(jQuery(document).height());
        single_post(posts, counter + 1);
    });
}

function import_image(data, counter) {
    var images = data.post_images;
    var gallery_id = data.gallery_id;
    var post_id = data.post_id;

    if (images.length == counter) {
        return true;
    }

    var target = jQuery(event.target),
        progressbar = jQuery("#progressbar-" + post_id),
        progressbarValue = progressbar.find(".ui-progressbar-value"),
        progressLabel = progressbar.find(".progress-label");

    progressbar.progressbar("option", {
        value: Math.floor(((counter + 1) / images.length) * 100),
        change: function() {
            progressLabel.text(progressbar.progressbar('value') + '%');
        },
        complete: function() {
            progressLabel.text('Done!');
        }
    });

    jQuery.ajax({
        dataType: 'json',
        type: 'post',
        url: ajaxurl,
        data: {
            action: 'post2ngg_action_import_images_to_gallery',
            post_id: post_id,
            gallery_id: gallery_id,
            image_src: images[counter],
            total_images: images.length
        }
    }).done(function(response) {
        import_image(data, counter + 1);
    }).fail(function(jqXHR, texStatus) {
        alert(texStatus);
    });
}