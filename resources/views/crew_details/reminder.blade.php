<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
<h2>Welcome to the site {{$name}}</h2>
<br/>
Your registered email-id is {{$email}} , Please click on the below link to create password
<br/>
<a href="{{url('create_passwords', $userid)}}">Click Here</a>
</body>
</html>