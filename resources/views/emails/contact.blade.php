<!DOCTYPE html>
<html>
<head>
    <title>Contact Message</title>
</head>
<body>
    <h2>New Message from {{ $data['name'] }}</h2>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
    <p><strong>Message:</strong></p>
    <p>{{ $data['message'] }}</p>
</body>
</html>
