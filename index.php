<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$city = isset($_GET['weather']) && !empty($_GET['weather']) ? $_GET['weather'] : 'Lahān';

$serverName = 'sql202.infinityfree.com'; 
$userName = 'if0_39411497'; 
$password = 'xeO5cEJp1cw'; 
$dbName = 'if0_39411497_jayagko'; 
$apiKey = '4313b01ea90e47f7a4786aa946fac0a4';

$conn = mysqli_connect($serverName, $userName, $password);
if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $dbName");
mysqli_select_db($conn, $dbName);

$createTable = "
    CREATE TABLE IF NOT EXISTS WeatherForecast (
        cityname VARCHAR(100),
        temp VARCHAR(100),
        descweather VARCHAR(100),
        hum VARCHAR(100),
        icon VARCHAR(250),
        pressure VARCHAR(100),
        wind_speed VARCHAR(100),
        wind_deg VARCHAR(100),
        dt BIGINT
    )";
mysqli_query($conn, $createTable);

$cityEscaped = mysqli_real_escape_string($conn, $city);
$query = "SELECT * FROM WeatherForecast WHERE cityname = '$cityEscaped' LIMIT 1";
$result = mysqli_query($conn, $query);

$fetchNewData = false;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $dataTimestamp = (int)$row['dt'];
    $currentTimestamp = time();
    if (($currentTimestamp - $dataTimestamp) > 3600) {
        $fetchNewData = true;
    } else {
        echo json_encode([$row]);
        exit();
    }
} else {
    $fetchNewData = true;
}

if ($fetchNewData) {
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityEscaped&appid=$apiKey&units=metric";
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        http_response_code(502);
        echo json_encode(["error" => "Failed to fetch weather data from API."]);
        exit();
    }

    $data = json_decode($response, true);

    if (!isset($data['name'])) {
        http_response_code(404);
        echo json_encode(["error" => "City not found in weather API."]);
        exit();
    }

    $name = mysqli_real_escape_string($conn, $data['name']);
    $temp = $data['main']['temp'];
    $descweather = mysqli_real_escape_string($conn, $data['weather'][0]['description']);
    $hum = $data['main']['humidity'];
    $icon = mysqli_real_escape_string($conn, $data['weather'][0]['icon']);
    $pressure = $data['main']['pressure'];
    $wind_speed = $data['wind']['speed'];
    $wind_deg = $data['wind']['deg'];
    $dt = $data['dt'];

    mysqli_query($conn, "DELETE FROM WeatherForecast WHERE cityname = '$name'");

    $insertQuery = "
        INSERT INTO WeatherForecast (cityname, temp, descweather, hum, icon, pressure, wind_speed, wind_deg, dt)
        VALUES ('$name', '$temp', '$descweather', '$hum', '$icon', '$pressure', '$wind_speed', '$wind_deg', '$dt')
    ";
    mysqli_query($conn, $insertQuery);

    $result = mysqli_query($conn, "SELECT * FROM WeatherForecast WHERE cityname = '$name' LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    echo json_encode([$row]);
    exit();
}
?>
