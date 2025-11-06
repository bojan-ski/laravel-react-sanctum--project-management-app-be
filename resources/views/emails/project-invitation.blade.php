<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        Project Invitation
    </title>
</head>

<body>
    
    <div>
        <h1>
            Project Invitation
        </h1>
    </div>
    
    <div>
        <p>
            Hello <strong>{{ $invitedUser->name }}</strong>,
        </p>
                
        <p>
            <strong>{{ $inviter->name }}</strong> has invited you to join project <strong>{{ $project->title }}</strong>
        </p>
        
        <div>
            <p>
                {{ $project->description }}
            </p>

            <p>
                <strong>Deadline:</strong> {{ $project->deadline->format('F j, Y') }}
            </p>
        </div>

        <a href="{{ config('app.frontend_url') }}/projects/{{ $project->id }}" class="button">
            View Project
        </a>
    </div>

</body>
</html>