<!DOCTYPE html>
<html>
<head>
    <title>Music Quiz Game</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Music Quiz Game</h1>
    <h2 class="text-center mt-4">Question {{ $questionNumber }}: Which song is playing?</h2>
    <h3 class="text-center">Time Elapsed: <span id="time-elapsed">0</span> seconds</h3>
<script>
    let timeElapsed = 0;
    const timer = setInterval(() => {
        timeElapsed++;
        document.getElementById('time-elapsed').textContent = timeElapsed;
    }, 1000);
</script>


    <div class="text-center mt-3">
        <!-- Audio Player with Autoplay -->
        <audio controls autoplay>
            <source src="{{ $currentSong['preview'] }}" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    </div>

    <form method="POST" action="{{ route('game.answer') }}" class="mt-4">
        @csrf
        <input type="hidden" name="song_id" value="{{ $currentSong['id'] }}">
        <input type="hidden" name="question_number" value="{{ $questionNumber }}">
        
        <div class="list-group">
            @foreach ($choices as $choice)
                <button type="submit" name="selected_song_id" value="{{ $choice['id'] }}" class="list-group-item list-group-item-action">
                    <img src="{{ $choice['album']['image'] ?? 'https://via.placeholder.com/50' }}" alt="{{ $choice['name'] }}" class="me-3" style="width: 50px; height: 50px;">
                    {{ $choice['name'] }}
                </button>
            @endforeach
        </div>
    </form>
</div>
</body>
</html>
