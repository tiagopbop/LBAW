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
    <h3>Filters:</h3>
    <label>
        <input type="checkbox" class="filter-checkbox" value="Charity"> Charity
    </label>
    <label>
        <input type="checkbox" class="filter-checkbox" value="open source"> Open Source
    </label>
    <label>
        <input type="checkbox" class="filter-checkbox" value="looking for people"> Looking for people
    </label>
</div>


            <!-- Projects List -->
            <ul id="search-results">
                <!-- Display all projects initially -->
                @if($projects->isEmpty())
                    <li>No projects available. Use the search bar to find projects.</li>
                @else
                    @foreach ($projects as $project)
                        <div class="search-projects">
                            <li data-id="{{ $project->project_id }}" style="display: inline-block; width: 96%; text-align: center; margin-bottom: 10px; width: 100%;position: relative;left: -21px;">

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
    const filterCheckboxes = document.querySelectorAll('.filter-checkbox');

    // Fetch and display projects
    function fetchProjects(query = '', filters = []) {
        const params = new URLSearchParams({ query });
        filters.forEach(filter => params.append('filters[]', filter)); // Send multiple filters as an array

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
                    resultsList.innerHTML = '<li>No results found.</li>';
                }
            })
            .catch(error => console.error('Error fetching projects:', error));
    }

    // Trigger search and filters
    function updateResults() {
        const query = searchBar.value.trim();
        const filters = Array.from(filterCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
        fetchProjects(query, filters);
    }

    // Event listeners for search and filters
    searchBar.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            updateResults();
        }
    });

    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateResults);
    });
});

</script>
@endpush