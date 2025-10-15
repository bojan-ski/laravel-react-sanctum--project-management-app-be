<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        Your Credentials
    </title>
</head>

<body>
    
    <div>
        <h1>Welcome to Project Management App</h1>
    </div>
    
    <div>
        <p>
            Hello <strong>{{ $user->name }}</strong>,
        </p>
                
        <p>
            Your account has been created by the administrator. Below are your login credentials:
        </p>
        
        <div>
            <p>
                <strong>Email:</strong> {{ $user->email }}
            </p>
            <p>
                <strong>Password:</strong> {{ $password }}
            </p>
        </div>
        
        <div>
            <p>
                <strong>⚠️ Security Notice:</strong> For your security, we recommend changing your password after your first login. You can do this from your profile page.
            </p>
        </div>
        
        <p>
            You can now log in to Project Management App and start collaborating on projects!
        </p>
        
        <a href="{{ config('app.frontend_url') }}" class="button">
            Go to Login Page
        </a>
    </div>

</body>
</html>