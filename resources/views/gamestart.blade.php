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
        let timeElapsed = 0; // Time in milliseconds
        let timer; // Variable to hold the timer interval ID

        // Function to start the timer
        function startTimer() {
            timer = setInterval(() => {
                timeElapsed += 100; // Increment by 100ms (0.1 second)
                const seconds = Math.floor(timeElapsed / 1000);
                const milliseconds = timeElapsed % 1000;
                document.getElementById('time-elapsed').textContent = `${seconds}s ${milliseconds}ms`;
            }, 100);
        }

        // Function to stop the timer
        function stopTimer() {
            clearInterval(timer);
        }

        // Start the timer when the page loads
        startTimer();
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
                <button type="submit" name="selected_song_id" value="{{ $choice['id'] }}" class="list-group-item list-group-item-action" onclick="stopTimer()">
                    <img src="{{ $choice['album']['image'] ?? 'https://via.placeholder.com/50' }}" alt="{{ $choice['name'] }}" class="me-3" style="width: 50px; height: 50px;">
                    {{ $choice['name'] }}
                </button>
            @endforeach
        </div>
    </form>
</div>
</body>
</html>