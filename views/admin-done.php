<style>
    .box {
        border: 1px solid;
        color: #9F6000;
        background-color: #FEEFB3;
        margin: 10px 0 0;
        padding: 15px 10px;
    }
    .dacure ul.parent {
        padding: 10px;
        border: solid 1px #CCC;
        background: #FFF;
        display: inline-block;
        margin: 0;
    }
    .dacure ul.children {
        padding: 0 24px 5px;
    }
</style>
<div class="wrap dacure">
    <h2>Transform posts into a NextGen Gallery.</h2>
    <h3>The selected categories were updated...</h3>
    <div class="box">
        <div>
            <ul class="parent">
                <?php wp_list_categories( array( 'title_li' => '', 'include' => $_POST['post_category'] ) ); ?>
            </ul>
        </div>
    </div>
</div>