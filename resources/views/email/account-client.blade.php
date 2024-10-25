<!DOCTYPE html>
<html>
<head>
    <title>Welcome New Client</title>
</head>
<body>
    <h1>Hello, {{ $user->name ?? '' }}</h1>
    <p>Thank you for deal. this is your account client</p>
    <p>Your email: {{ $user->email }}</p>
    <p>Your password: {{ $password }}</p>
    <p>Feel free to ask if you have any questions.</p>
    
    <p>Regards,</p>
    <p>SolarKita HQ</p>
</body>
</html>
