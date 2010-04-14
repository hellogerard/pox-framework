
<html>
<body>
    <hr/>
    <h3>Add/Edit a post</h3>

    <form action="" method="POST">
        Title: <br/>
        <input type="text" name="aTitle" id="aTitle" value="{$aTitle}" /> <span style="color: #FF0000;">{$aTitleErr}</span>
        <br/>
        Body: <br/>
        <textarea name="aBody" id="aBody">{$aBody}</textarea> <span style="color: #FF0000;">{$aBodyErr}</span>
        <br/>
        <input type="submit" name="submit" id="submit" value="Submit" />
    </form>

</body>
</html>
