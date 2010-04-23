<html>
<body>
    <h1>{$title}</h1>
    <table border="1">
            <tr><th>ID</th><th>Title</th><th>Created</th></tr>
        {foreach from=$posts item=post}
            <tr><td>{$post->post_id}</td><td><a href="/blog/view/{$post->post_id}">{$post->title}</a></td><td>{$post->time|date_format:"%A %b. %e %Y %l:%M %p"}</td></tr>
        {/foreach}
    </table>

    <a href="/blog/post">Add a post .</a>
</body>
</html>
