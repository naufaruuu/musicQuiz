<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Artist</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Search Artist</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('searchArtist') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="artist_name" class="form-label">Artist Name</label>
                                <input type="text" id="artist_name" name="artist_name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
