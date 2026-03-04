<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherApp</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contener">
    <header class="head">WeatherApp
        <img src="pngtree-partly-cloudy-weather-vector-png-image_7125412.png" alt="Zdjęcie pogoda">
    </header>
    <main class="main"><h1>Podaj miasto:</h1></br>
    <div class="temperatura">
<form method="POST" id="weatherForm">
    <input name="city" placeholder="Podaj Miasto">
    <button type="submit">Wyszukaj</button> 
    <input type="hidden" name="selected_date" id="selected_date">
</form>
<div id="loading" class="loading"></div>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $apiKey = "...";
    $city = trim($_POST['city'] ?? "");
    $selectedDate = $_POST['selected_date'] ?? "";

    if (empty($city)) {
        echo "<span class='tlo-napisu error'>Błąd: Nie podałeś miasta.</span>";
    }

    elseif (empty($selectedDate)) {
        echo "<span class='tlo-napisu error'>Błąd: Nie wybrałeś daty.</span>";
    }

    else {

        $city = urlencode($city);
        $url = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric&lang=pl";

        $response = @file_get_contents($url);

        if ($response === FALSE) {
            echo "<span class='tlo-napisu error'>Błąd: Nie udało się pobrać danych.</span>";
        }

        else {

            $data = json_decode($response, true);

            if ($data["cod"] != "200") {
                echo "<span class='tlo-napisu error'>Błąd: Miasto nie istnieje.</span>";
            }

            else {

                $temps = [];
                $opis = "";

                foreach ($data['list'] as $forecast) {

                    $forecastDate = date("Y-m-d", strtotime($forecast['dt_txt']));

                    if ($forecastDate == $selectedDate) {
                        $temps[] = $forecast['main']['temp'];
                        $opis = $forecast['weather'][0]['description'];
                    }
                }

                if (!empty($temps)) {

                    $avgTemp = round(array_sum($temps) / count($temps), 2);

                    echo "<span class='tlo-napisu'>Miasto: " . $data['city']['name'] . "</span><br>";
                    echo "<span class='tlo-napisu'>Data: " . $selectedDate . "</span><br>";
                    echo "<span class='tlo-napisu'>Średnia temperatura: " . $avgTemp . " °C</span><br>";
                    echo "<span class='tlo-napisu'>Opis: " . $opis . "</span><br>";
                }

                else {
                    echo "<span class='tlo-napisu error'>Brak prognozy dla tej daty.</span>";
                }
            }
        }
    }
}
?>
</div>
    </main>
    <aside class="aside"><h1>Podaj datę:</h1></br>
<div class="datatlo">
<input type="text" id="calendar" placeholder="Wybierz date↓">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#calendar", {
    inline: true,
    dateFormat: "Y-m-d",

    minDate: "today", 
    maxDate: new Date().fp_incr(5), 

    onChange: function(selectedDates, dateStr) {
        document.getElementById("selected_date").value = dateStr;
    }
});
</script>
<script>
document.getElementById("weatherForm").addEventListener("submit", function(e) {

    document.getElementById("loading").style.display = "block";

    const results = document.querySelectorAll(".tlo-napisu, .error");
    results.forEach(el => el.style.display = "none");

});
</script>
</div>
</aside>
    <footer class="foot">Konstanty Traciński</footer>
</div>
</body>
</html>