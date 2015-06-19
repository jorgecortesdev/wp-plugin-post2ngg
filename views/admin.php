<div class="wrap dacure">

    <h2>Transform posts into a NextGen Gallery.</h2>

    <!-- Being TABS -->
    <div id="tabs" class="categorydiv">
        <ul class="category-tabs">
            <li class="tabs"><a href="#tabs-1">Single post</a></li>
            <li><a href="#tabs-2">By category</a></li>
        </ul>

        <!-- TAB1 -->
        <div id="tabs-1" class="tabs-panel">
            <br />
            <br />
            <form id="single_post_form" method="post">
                <input name="type" type="hidden" value="single-post">
                Post id: <input id="post_to_transform" name="post_to_transform" type="text" />
                <input type="submit" class="button button-primary button-large"  value="Transform" id='post2ngg_single_submit'/>
            </form>
        </div>

        <!-- TAB2 -->
        <div id="tabs-2" class="tabs-panel">
            <form id="category_posts_form" method="post">
                <ul class="categorychecklist">
                    <?php wp_category_checklist(); ?>
                </ul>
                <input name="type" type="hidden" value="category">
                <input type="submit" class="button button-primary button-large" value="Transform"  id='post2ngg_category_submit'/>
            </form>
        </div>
    </div>
    <!-- End TABS -->

    <div id="post2ngg_post"></div>

    <script id="post-template" type="text/x-handlebars-template">
        <?php include_once (POST2NGG_PLUGIN_PATH . 'views/hb-post.php'); ?>
    </script>


</div>