<?php
$apiKey = "...";

function fetchWeather($url) {

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(curl_errno($ch)){
        curl_close($ch);
        return ["error" => "Błąd połączenia z API"];
    }
    curl_close($ch);

    if($httpCode !== 200){
        return ["error" => "API zwróciło błąd HTTP: $httpCode"];
    }
    return json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WeatherApp</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
<div class="container">
<header class="head">
WeatherApp
<img src="pngtree-partly-cloudy-weather-vector-png-image_7125412.png">
</header>

<main class="main">
<h1>Podaj miasto</h1>
<div class="card">
<form method="POST" id="weatherForm">
<input name="city" placeholder="Podaj miasto">
<button type="submit">Sprawdź pogodę</button>
<input type="hidden" name="selected_date" id="selected_date">
</form>

<div id="loading" class="loading"></div>
<p id="loadingText">Ładowanie danych...</p>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$city = filter_input(INPUT_POST,'city',FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_POST,'selected_date',FILTER_SANITIZE_STRING);

if(!$city){
echo "<p class='error'>Nie podałeś miasta</p>";
}
elseif(!$date){
echo "<p class='error'>Nie wybrałeś daty</p>";
}
else{
$cityEncoded = urlencode($city);
$url = "https://api.openweathermap.org/data/2.5/forecast?q=$cityEncoded&appid=$apiKey&units=metric&lang=pl";
$data = fetchWeather($url);
if(isset($data["error"])){
echo "<p class='error'>{$data["error"]}</p>";
}
elseif($data["cod"] != "200"){
echo "<p class='error'>Miasto nie istnieje</p>";
}
else{
$temps = [];
$opis = "";
$icon = "";

foreach($data['list'] as $forecast){
$forecastDate = date("Y-m-d",strtotime($forecast['dt_txt']));

if($forecastDate == $date){
$temps[] = $forecast['main']['temp'];
$opis = $forecast['weather'][0]['description'];
$icon = $forecast['weather'][0]['icon'];
}
}

if($temps){
$avgTemp = round(array_sum($temps)/count($temps),1);
echo "<div class='result'>";
echo "<h2>".$data['city']['name']."</h2>";
echo "<img src='https://openweathermap.org/img/wn/$icon@2x.png'>";
echo "<p>Data: $date</p>";
echo "<p>Średnia temperatura: $avgTemp °C</p>";
echo "<p>$opis</p>";
echo "</div>";
}
else{
echo "<p class='error'>Brak prognozy dla tej daty</p>";
}
}
}
}
?>
</div>
</main>
<aside class="aside">
<h1>Wybierz datę</h1>
<div class="card">
<input type="text" id="calendar" placeholder="Wybierz datę">
</div>
</aside>

<footer class="foot">
Konstanty Traciński
</footer>
</div>

<script>
flatpickr("#calendar",{
inline:true,
dateFormat:"Y-m-d",
minDate:"today",
maxDate:new Date().fp_incr(5),
onChange:function(selectedDates,dateStr){
document.getElementById("selected_date").value=dateStr;
}
});
</script>

<script>
document.getElementById("weatherForm").addEventListener("submit",function(){
document.getElementById("loading").style.display="block";
document.getElementById("loadingText").style.display="block";
});
</script>
</body>
</html>
