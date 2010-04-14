<html>
<body>
    <h1>{$post->title}</h1>
    <br/>
    {$post->body|nl2br} <br/>
    <br/>
    Created on: {$post->time|date_format:"%A %b. %e %Y %l:%M %p"} <br/>
    <br/>
    <a href="/blog/post/{$post->post_id}">Edit this post .</a> <a href="/blog/delete/{$post->post_id}">Delete this post .</a>
    <hr/>
    <h3>Comments .</h3>
        {foreach from=$post->comments item=comment}
        <div>
            <u>{$comment->name} at {$comment->time|date_format:"%A %b. %e %Y %l:%M %p"}</u><br/>
            {$comment->comment}
        </div>
        <br/>
        {/foreach}
    <hr/>
    <h3>Add a comment .</h3>

    <form action="" method="POST">
        Name: <br/>
        <input type="text" name="name" id="name" value="{$name}" /> <span style="color: #FF0000;">{$nameErr}</span>
        <br/>
        Comment: <br/>
        <textarea name="aComment" id="aComment">{$aComment}</textarea> <span style="color: #FF0000;">{$aCommentErr}</span>
        <br/>
        <input type="submit" name="submit" id="submit" value="Submit" />
    </form>

</body>
</html>
