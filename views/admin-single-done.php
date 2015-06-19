<style>
    .box {
        border: 1px solid;
        color: #9F6000;
        background-color: #FEEFB3;
        margin: 10px 0 0;
        padding: 15px 10px;
    }
</style>
<div class="wrap dacure">
    <h2>Transform posts into a NextGen Gallery.</h2>
    <div class="box">
        <div>
            The post with id <a href="<?php echo get_edit_post_link( $_POST['post_to_transform'] ); ?>"><?php echo $_POST['post_to_transform']; ?></a> was updated
        </div>
    </div>
</div>