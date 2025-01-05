<!DOCTYPE html>
<html>
<head>
    <title>Quiz Complete</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5 text-center">
    <h1 class="text-center">Quiz Complete!</h1>
    <h2>Your final score is {{ $score }}/10</h2>

    <h3 class="mt-5">Scoreboard for {{ $artistName }}</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Score</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topRecords as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->user->username }}</td>
                    <td>{{ $record->score }}</td>
                    <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
