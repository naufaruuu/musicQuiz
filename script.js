const corsAnywhereUrl = "https://cors-anywhere.herokuapp.com/";
let selectedArtistId = null;
let selectedArtistName = ""; // To store the artist's name
let selectedArtistPicture = ""; // To store the artist's picture
let gameSongs = [];
let currentQuestion = 0;
let score = 0;

const usedSongs = [];
const results = [];
const cachedAudios = {};
let quizStartTime = null; // To track the start time of the quiz

// Show and hide loading screen
function showLoading() {
    document.getElementById("loadingScreen").classList.remove("d-none");
}

function hideLoading() {
    document.getElementById("loadingScreen").classList.add("d-none");
}

// Enable CORS button logic
document.getElementById("enableCorsBtn").addEventListener("click", () => {
    // Open the original CORS Anywhere page in a new tab
    window.open("https://cors-anywhere.herokuapp.com/corsdemo", "_blank");

    // Inform the user to return after enabling CORS
    alert("Please enable CORS Anywhere on the newly opened page, then click 'Done. Continue' here.");
});

// Done. Continue button logic
document.getElementById("doneCorsBtn").addEventListener("click", () => {
    // Optionally check if CORS is enabled
    fetch("https://cors-anywhere.herokuapp.com/")
        .then(response => {
            if (response.status === 200) {
                console.log("CORS Anywhere is enabled.");
                document.getElementById("step1").classList.add("d-none");
                document.getElementById("step2").classList.remove("d-none");
            } else {
                alert("CORS Anywhere is not enabled. Please try again.");
            }
        })
        .catch(error => {
            alert("Could not verify CORS status. Proceeding anyway.");
            console.error("Error verifying CORS:", error);
            document.getElementById("step1").classList.add("d-none");
            document.getElementById("step2").classList.remove("d-none");
        });
});



// Search Artists
document.getElementById("searchBtn").addEventListener("click", () => {
    showLoading();

    const artistName = document.getElementById("artistSearch").value.trim();
    const searchUrl = `https://api.deezer.com/search/artist?q=${encodeURIComponent(artistName)}`;

    fetch(corsAnywhereUrl + searchUrl)
        .then(response => response.json())
        .then(data => {
            hideLoading();

            const artistList = document.getElementById("artistList");
            artistList.innerHTML = "";

            if (data.data.length > 0) {
                // Limit to 10 artists
                const limitedArtists = data.data.slice(0, 10);
                limitedArtists.forEach(artist => {
                    const artistCard = document.createElement("div");
                    artistCard.classList.add("card", "mb-3");
                    artistCard.style = "width: 100%;";

                    artistCard.innerHTML = `
                        <div class="row g-0">
                            <div class="col-md-2">
                                <img src="${artist.picture_medium}" class="img-fluid rounded-start" alt="${artist.name}">
                            </div>
                            <div class="col-md-10">
                                <div class="card-body">
                                    <h5 class="card-title">${artist.name}</h5>
                                    <button class="btn btn-primary" data-artist-id="${artist.id}">Select Artist</button>
                                </div>
                            </div>
                        </div>
                    `;

                    artistCard.querySelector("button").addEventListener("click", () => {
                        selectedArtistId = artist.id;
                        selectedArtistName = artist.name; // Store artist name
                        selectedArtistPicture = artist.picture_big; // Store artist picture
                        fetchSongsForQuiz(selectedArtistId);
                    });

                    artistList.appendChild(artistCard);
                });
            } else {
                artistList.innerHTML = '<p class="text-danger">No artists found.</p>';
            }
        })
        .catch(error => {
            hideLoading();
            console.error("Error fetching artists:", error);
        });
});

// Fetch Songs for Quiz
function fetchSongsForQuiz(artistId) {
    const songsUrl = `https://api.deezer.com/artist/${artistId}/top?limit=50`;

    fetch(corsAnywhereUrl + songsUrl)
        .then(response => response.json())
        .then(data => {
            gameSongs = data.data;
            if (gameSongs.length > 0) {
                document.getElementById("step2").classList.add("d-none");
                document.getElementById("step3").classList.remove("d-none");

                quizStartTime = new Date(); // Start the timer
                startQuiz();
            } else {
                alert("No songs available for this artist.");
            }
        });
}

// Preload Audio
function preloadAudio(songId, url) {
    if (!cachedAudios[songId]) {
        const audio = new Audio(url);
        audio.preload = "auto";
        cachedAudios[songId] = audio;
    }
}

function getPreloadedAudio(songId) {
    return cachedAudios[songId] || null;
}

// Start Quiz
function startQuiz() {
    if (currentQuestion < 10 && gameSongs.length - usedSongs.length > 0) {
        const availableSongs = gameSongs.filter(song => !usedSongs.includes(song.id));
        const question = availableSongs[Math.floor(Math.random() * availableSongs.length)];
        usedSongs.push(question.id);

        let choices = [];
        while (choices.length < 3) {
            const randomSong = gameSongs[Math.floor(Math.random() * gameSongs.length)];
            if (!choices.some(song => song.title === randomSong.title) && randomSong.id !== question.id) {
                choices.push(randomSong);
            }
        }

        if (!choices.some(song => song.id === question.id)) {
            choices[Math.floor(Math.random() * choices.length)] = question;
        }

        preloadAudio(question.id, question.preview);
        displayQuestion(question, choices);
    } else {
        endQuiz();
    }
}

// Display Question
function displayQuestion(correctSong, choices) {
    document.getElementById("questionText").innerHTML = `
        <strong>Question ${currentQuestion + 1}:</strong> Which song is playing?
    `;

    const choicesList = document.getElementById("choices");
    choicesList.innerHTML = "";

    // Use preloaded <audio> for playback
    const preloadedAudio = getPreloadedAudio(correctSong.id);
    if (preloadedAudio) {
        const audioClone = preloadedAudio.cloneNode();
        audioClone.controls = true;
        document.getElementById("questionText").appendChild(audioClone);
    }

    // Create choice buttons with images
    choices.forEach(choice => {
        const li = document.createElement("li");
        li.classList.add("list-group-item", "list-group-item-action", "d-flex", "align-items-center");

        const img = document.createElement("img");
        img.src = choice.album.cover_small; // Use small-sized cover for the button
        img.alt = choice.title;
        img.classList.add("img-thumbnail", "me-3"); // Add thumbnail styling
        img.style.width = "50px"; // Adjust image size

        const span = document.createElement("span");
        span.textContent = choice.title;

        li.appendChild(img); // Add image to the button
        li.appendChild(span); // Add text to the button

        li.addEventListener("click", () => {
            const correct = choice.id === correctSong.id;

            results.push({
                questionNumber: currentQuestion + 1,
                answered: choice.title,
                correctAnswer: correctSong.title,
                isCorrect: correct,
                audio: preloadedAudio ? preloadedAudio.src : correctSong.preview,
                trackImage: correctSong.album.cover_medium, // Add track image to results
            });

            if (correct) score++;
            currentQuestion++;
            startQuiz();
        });

        choicesList.appendChild(li);
    });
}


// End Quiz
function endQuiz() {
    const step3 = document.getElementById("step3");
    step3.innerHTML = `<h2>Quiz Complete!</h2><p class="text-center">Your final score is <strong>${score}/10</strong>.</p>`;

    // Calculate total time taken
    const quizEndTime = new Date();
    const totalTimeInSeconds = Math.round((quizEndTime - quizStartTime) / 1000);

    // Display artist picture and time taken
    const resultSummary = document.createElement("div");
    resultSummary.classList.add("text-center", "mt-3");
    resultSummary.innerHTML = `
        <img src="${selectedArtistPicture}" class="img-fluid rounded-circle mb-3" style="max-width: 150px;" alt="${selectedArtistName}">
        <p>You completed the "${selectedArtistName}" quiz in ${totalTimeInSeconds} seconds.</p>
    `;
    step3.appendChild(resultSummary);

    // Create a detailed result table
    const resultTable = document.createElement("div");
    resultTable.classList.add("mt-4");

    results.forEach(result => {
        const resultItem = document.createElement("div");
        resultItem.classList.add("mb-3", "p-2", "border", "rounded", "bg-light");

        resultItem.innerHTML = `
            <strong>No ${result.questionNumber}:</strong><br>
            <img src="${result.trackImage}" class="img-fluid rounded mb-2" style="max-width: 100px;" alt="Track Image"><br>
            Answered: ${result.answered} ${result.isCorrect ? "<span class='text-success'>(Correct)</span>" : `<span class='text-danger'>(Wrong)</span>`}<br>
            ${!result.isCorrect ? `Correct Answer: ${result.correctAnswer}<br>` : ""}
            <audio src="${result.audio}" controls class="w-100 mt-2"></audio>
        `;

        resultTable.appendChild(resultItem);
    });

    step3.appendChild(resultTable);
}
