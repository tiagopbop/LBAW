@extends('layouts.app')

@section('title', 'tests')

@section('content')
    <section id="tests">

        <body>  
            
            <!-- Search Bar -->
            <div class="profile-container">
                <h2>Search Projects</h2>
                <input type="text" class="profile-container" style="display: flex; justify-content: center; align-items: center; height: 80%; width: 95%;" id="search-bar" placeholder="Type to search projects..." />
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

            <!-- Sort Buttons -->
            <div class="sort-container" style="margin: 10px 0;">
                <h3>Sort by:</h3>
                <select id="sort-by">
                    <option value="project_creation_date">Creation Date</option>
                    <option value="updated_at">Updated Date</option>
                    <option value="archived_status">Archived Status</option>
                </select>
                <button id="toggle-sort-order">Ascending</button>
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
                    <a href="{{ route('projects.show', $project) }}" class="search-projects-buttons" style="width: 96%; margin-bottom: 10px;">
                        {{ $project->project_title }}: {{ $project->project_description }}
                        <!-- Creation Date, Updated Date, and Archived Status inline -->
                        <span>Created at: {{ optional($project->project_creation_date)->format('Y-m-d') ?? 'Created: Not available' }} |</span>
                        <span>Updated at: {{ optional($project->updated_at)->format('Y-m-d') ?? 'Updated: Not available' }} |</span>
                        <span>{{ $project->archived_status ? 'Archived: Yes' : 'Archived: No' }}</span>
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
    const sortBySelect = document.getElementById('sort-by');
    const toggleSortOrderButton = document.getElementById('toggle-sort-order');
    let isAscending = true;

    // Fetch and display projects
    function fetchProjects(query = '', filters = [], sortBy = 'project_creation_date', sortOrder = 'asc') {
        const params = new URLSearchParams({ query, sort_by: sortBy, sort_order: sortOrder });
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
                    link.innerHTML = `
                        ${project.project_title}: ${project.project_description}
                        <span>Created at: ${project.project_creation_date ? new Date(project.project_creation_date).toLocaleDateString() : 'Created: Not available'} |</span>
                        <span>Updated at: ${project.updated_at ? new Date(project.updated_at).toLocaleDateString() : 'Updated: Not available'} |</span>
                        <span>${project.archived_status ? 'Archived: Yes' : 'Archived: No'}</span>
                    `;
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
        const sortBy = sortBySelect.value;
        const sortOrder = isAscending ? 'asc' : 'desc';
        fetchProjects(query, filters, sortBy, sortOrder);
    }

    // Event listeners for search, filters, and sorting
    searchBar.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            updateResults();
        }
    });

    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateResults);
    });

    sortBySelect.addEventListener('change', updateResults);

    toggleSortOrderButton.addEventListener('click', function () {
        isAscending = !isAscending;
        toggleSortOrderButton.textContent = isAscending ? 'Ascending' : 'Descending';
        updateResults();
    });

    // Initial fetch for projects when the page loads
    updateResults();
});
</script>
@endpush
