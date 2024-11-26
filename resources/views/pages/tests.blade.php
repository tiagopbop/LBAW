@extends('layouts.app')

@section('title', 'tests')

@section('content')
    <section id="tests">


        <!-- Logout Button -->

        <body>
            <p><strong>Username:</strong> {{ $username }}</p>
            <p><strong>Email:</strong> {{ $email }}</p>
        tudo testado=?    

        <div class="container">
            <h1>peepeepoopoo</h1>
            <a href="{{ route('projects.create') }}" style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                Create Project
            </a>
        </div>
            <!-- Search Bar -->
        <div>
            <h4>Search Projects</h4>
            <input type="text" id="search-bar" placeholder="Type to search projects..." />
        </div>

        <!-- Projects List -->
        <ul id="search-results">
    <!-- Display all projects initially -->
    @if($projects->isEmpty())
        <li>No projects available. Use the search bar to find projects.</li>
    @else
        @foreach ($projects as $project)
            <li data-id="{{ $project->id }}">
                {{ $project->project_title }}: {{ $project->project_description }}
            </li>
        @endforeach
    @endif
</ul>
    </body>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchBar = document.getElementById('search-bar');
    const resultsList = document.getElementById('search-results');

    // Fetch and display projects
    function fetchProjects(query = '') {
        fetch(`/search-projects?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsList.innerHTML = ''; // Clear previous results
                if (data.length > 0) {
                    data.forEach(project => {
                        const listItem = document.createElement('li');
                        listItem.textContent = `${project.project_title}: ${project.project_description}`;
                        resultsList.appendChild(listItem);
                    });
                } else {
                    resultsList.innerHTML = '<li>No results found.</li>'; // Display no results message
                }
            })
            .catch(error => console.error('Error fetching projects:', error));
    }

    // Trigger search on Enter key press
    searchBar.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') { // Check if Enter key was pressed
            const query = searchBar.value.trim();
            fetchProjects(query); // Fetch projects based on query
        }
    });
});
</script>
@endpush