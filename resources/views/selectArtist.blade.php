<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Artist</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .artist-card:hover {
            background-color: #f0f8ff;
            cursor: pointer;
        }
        #loadingSpinner {
            display: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Select Artist</h4>
                    </div>
                    <div class="card-body">
                        <div id="loadingSpinner" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Indexing songs. Please wait...</p>
                        </div>
                        <div id="artistList">
                            <div class="row gy-3">
                                @foreach ($artists as $artist)
                                    <div class="col-12">
                                        <form method="POST" action="{{ route('selectArtist') }}" class="artist-card rounded border p-2">
                                            @csrf
                                            <input type="hidden" name="artist_id" value="{{ $artist['id'] }}">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $artist['picture'] ?? 'https://via.placeholder.com/100' }}" 
                                                     alt="{{ $artist['name'] }}" 
                                                     class="rounded me-3" 
                                                     style="width: 100px; height: 100px; object-fit: cover;">
                                                <h5 class="mb-0">{{ $artist['name'] }}</h5>
                                            </div>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Add loading spinner when an artist is selected
        document.querySelectorAll('.artist-card').forEach(card => {
            card.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent immediate form submission
                document.getElementById('artistList').style.display = 'none';
                document.getElementById('loadingSpinner').style.display = 'block';
                this.submit(); // Submit the form after showing the spinner
            });
        });
    </script>
</body>
</html>
