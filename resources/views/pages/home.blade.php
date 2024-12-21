@extends('layouts.app')

@section('title', 'tests')

@section('content')
    <section id="tests">

        <body>  
            
            <!-- Search Bar -->
            <div class="profile-container">
                <h2>Search Projects</h2>
                <input type="text" class="profile-container" style="display: flex; justify-content: center; align-items: center; height: 80%; width: 95%; " id="search-bar" placeholder="Type to search projects..." />
            </div>

            <!-- Filter Buttons -->
            <div class="filter-container" style="margin: 10px 0;">
                <button class="filter-button" data-filter="charity">Charity</button>
                <button class="filter-button" data-filter="open source">Open Source</button>
                <button class="filter-button" data-filter="">Clear Filters</button>
            </div>

            <!-- Projects List -->
            <ul id="search-results">
                <!-- Display all projects initially -->
                @if($projects->isEmpty())
                    <li>No projects available. Use the search bar to find projects.</li>
                @else
                    @foreach ($projects as $project)
                        <div class="search-projects">
                            <li data-id="{{ $project->project_id }}" style="display: inline-block; width: 107%; text-align: center; margin-bottom: 10px; width: 100%;">

                                <a href="{{ route('projects.show', $project) }}" class="search-projects-buttons">
                                    {{ $project->project_title }}: {{ $project->project_description }}
                                </a>

                            </li>
                        </div>
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
    const filterButtons = document.querySelectorAll('.filter-button');
    let activeFilter = '';

    // Fetch and display projects
    function fetchProjects(query = '', filter = '') {
        const params = new URLSearchParams({ query, filter });
        fetch(`/search-projects?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                resultsList.innerHTML = ''; // Clear previous results

                if (data.length > 0) {
                    data.forEach(project => {
                        const listItem = document.createElement('div');
                        listItem.setAttribute('data-id', project.project_id); // Adding the data-id

                        const link = document.createElement('a');
                        link.href = `/projects/${project.project_id}`; // Correctly link to the project page
                        link.className = 'search-projects-buttons'; // Add the button styling class
                        link.style = 'width: 96%; margin-bottom: 10px;';
                        link.textContent = `${project.project_title}: ${project.project_description}`; // Text content of the project
                        listItem.appendChild(link); // Append the link to the list item
                        resultsList.appendChild(listItem); // Append the list item to the results list
                    });
                } else {
                    resultsList.innerHTML = '<div>No results found.</div>'; // Display no results message
                }
            })
            .catch(error => console.error('Error fetching projects:', error));
    }

    // Trigger search on Enter key press
    searchBar.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') { // Check if Enter key was pressed
            const query = searchBar.value.trim();
            fetchProjects(query, activeFilter); // Fetch projects based on query and active filter
        }
    });

    // Filter button click handling
    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            activeFilter = this.getAttribute('data-filter');
            const query = searchBar.value.trim();
            fetchProjects(query, activeFilter); // Fetch projects based on query and selected filter
        });
    });
});
</script>
@endpush