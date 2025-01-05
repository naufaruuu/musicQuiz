<!DOCTYPE html>
<html>
<head>
    <title>Quiz Complete</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .artist-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: auto;
            display: block;
        }
        .question-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            padding: 20px;
            margin-bottom: 15px;
        }
        .track-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5 text-center">
    <h1 class="text-center">Quiz Complete!</h1>
    <img src="{{ $artistImage }}" alt="Artist" class="artist-image mt-3">
    <h2 class="mt-4">Your final score is <strong>{{ $score }}/10</strong></h2>
    <p class="text-muted">You completed the "{{ $artistName }}" quiz in <strong>{{ $duration }} seconds</strong>.</p>

    <h3 class="mt-5">Scoreboard for {{ $artistName }}</h3>
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-dark">
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

    <h3 class="mt-5">Question Corrections</h3>
    @foreach ($questions as $index => $question)
    <div class="question-card d-flex align-items-center">
        <img src="{{ $question['image'] }}" alt="Track" class="track-image">
        <div class="flex-grow-1">
            <h5>No {{ $index + 1 }}:</h5>
            <p><strong>Answered:</strong> {{ $question['user_answer'] }}
                @if ($question['is_correct'])
                    <span class="text-success">(Correct)</span>
                @else
                    <span class="text-danger">(Wrong)</span>
                @endif
            </p>
            @if (!$question['is_correct'])
                <p><strong>Correct Answer:</strong> {{ $question['correct_answer'] }}</p>
            @endif
            <p><strong>Time Elapsed:</strong> {{ $question['elapsed_time'] }} seconds</p>
            <audio controls>
                <source src="{{ $question['preview'] }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
    </div>
@endforeach

</div>
</body>
</html>
