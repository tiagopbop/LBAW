@extends('layouts.app')

@section('content')
    <div class="search-container">
        <h1>Search Users</h1>

        <!-- Search Form -->
        <input type="text" id="search-input" placeholder="Search for users..." style="width: 300px; padding: 5px;">
        
        <!-- List of search results -->
        <div id="search-results"></div>
    </div>

    @push('scripts')
    <script>
        // Add an event listener to the search input
        function searchUsers(query = '') {
    // If query is not provided, default to empty
    if (query.length === 0) {
        query = '';
    }

    // Make an AJAX request to search users based on the input query
    fetch(`/searchusers/ajax?query=${query}`)
        .then(response => response.json())
        .then(data => {
            // Clear any previous results
            let resultsContainer = document.getElementById('search-results');
            resultsContainer.innerHTML = '';

            // Check if there are any results
            if (data.length > 0) {
                data.forEach(user => {
                    // Create a button for each user, with a link to their profile
                    let userButton = document.createElement('button');
                    userButton.innerHTML = `${user.username} (ID: ${user.id})`;
                    userButton.onclick = function () {
                        window.location.href = `/profile/${user.username}`;
                    };
                    resultsContainer.appendChild(userButton);
                });
            } else {
                // Show message if no users found
                resultsContainer.innerHTML = '<p>No users found.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Execute search on page load to show all users initially
document.addEventListener('DOMContentLoaded', function() {
    searchUsers(); // Call the search function with an empty query to load all users
});

// Add event listener to the search input to handle typing
document.getElementById('search-input').addEventListener('input', function () {
    let query = this.value;
    searchUsers(query); // Call the search function whenever the user types something
});

        
    </script>
    @endpush
@endsection
