{{# if error}}
<table>
    <tr>
        <td><div class="alert-box alert-box-error"><span>error: </span>{{error}}</div></td>
    </tr>
</table>
{{else}}
<table class="post2ngg-table">​
    <tr>
        <td>Thumbnail</td>
        <td>Title</td>
        <td>Category</td>
        <td>Images</td>
        <td>Progress</td>
        <td>Actions</td>
    </tr>
    <tr>
        <td style="width: 60px;"><img width="60" height="60" src="{{post_featured_image}}"></td>
        <td style="width: 400px;"><strong><a href="<?php echo admin_url('post.php?post={{post_id}}&amp;action=edit');?>">{{post_title}}</a></strong></td>
        <td style="width: 100px;">{{post_category}}</td>
        <td>{{post_total_images}}</td>
        <td style="width: 400px;">
            <div id="progressbar-{{post_id}}"><div class="progress-label">Loading...</div></div>
        </td>
        <td><a target="_blank" href="{{post_permalink}}">View post</a></td>
    </tr>
</table>
{{/if}}