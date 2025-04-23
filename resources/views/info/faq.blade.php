@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html>
<head>
    <title>FAQ</title>
</head>
<body>
    <h1>Frequently Asked Questions</h1>
    <p>Here you can find answers to common questions about ManageMe:</p>
    
    <h2>What is ManageMe?</h2>
    <p>ManageMe is an online platform for project management, designed to be simple and intuitive for both individuals and teams.</p>
    
    <h2>Who can use ManageMe?</h2>
    <p>Anyone can use ManageMe, whether you are managing a personal project, a group trip, or a business project. Projects can be private or publicly visible.</p>
    
    <h2>How can I interact with projects?</h2>
    <p>You can create tasks, organize priorities, set due dates, and more. For projects involving multiple people, you can also create a discussion forum to enhance communication.</p>
    
    <h2>Do I need an account to use ManageMe?</h2>
    <p>You can search for and view public projects without an account. However, you need an account to favorite or be part of a project.</p>

    <h2>What roles are available in ManageMe projects?</h2>
    <p>ManageMe offers several roles:
        <ul>
            <li><strong>Project Owner:</strong> Has full control over the project, including deleting it or transferring ownership.</li>
            <li><strong>Project Manager:</strong> Can invite members and assign tasks.</li>
            <li><strong>Members:</strong> Can participate in discussions and complete tasks assigned to them.</li>
        </ul>
    </p>
    
    <h2>What inspired the development of ManageMe?</h2>
    <p>ManageMe was inspired by tools like Trello, Libreboard, and GitHub Projects. It aims to address gaps in existing solutions by offering clarity, organization, and comprehensive features for diverse user needs.</p>
</body>
</html>
@endsection