const nameElem = document.getElementById("name");
const typeElem = document.getElementById("type");
const posterElem = document.getElementById("poster");
const tempElem = document.getElementById("temp");
const descElem = document.getElementById("desc");
const f1Elem = document.getElementById("f1");
const f2Elem = document.getElementById("f2");
const f3Elem = document.getElementById("f3");
const f4Elem = document.getElementById("f4");
const searchInput = document.getElementById("search");
const buttonElem = document.getElementById("butt");

function formatDt(dt) {
    const date = new Date(dt * 1000);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString(undefined, options);
}

async function fetchWeather(city) {
    let data;

    try {
        if (navigator.onLine) {
            const response = await fetch(`https://jayagko.infinityfreeapp.com/index.html?weather=${city}`); 
            
            data = await response.json();
            localStorage.setItem(city, JSON.stringify(data));
        } else {
            data = JSON.parse(localStorage.getItem(city));
        }

        if (!data || !data[0]) {
            alert("Weather data unavailable for this city.");
            return;
        }

        const weather = data[0];

        nameElem.textContent = weather.cityname;
        descElem.textContent = weather.descweather;
        tempElem.textContent = `${weather.temp}°C`;
        f1Elem.textContent = `Humidity: ${weather.hum}%`;
        f2Elem.textContent = `Pressure: ${weather.pressure} hPa`;
        f3Elem.textContent = `Wind Speed: ${weather.wind_speed} m/s`;
        f4Elem.textContent = `Wind Direction: ${weather.wind_deg}°`;
        typeElem.textContent = formatDt(weather.dt);
        posterElem.src = `https://openweathermap.org/img/wn/${weather.icon}@2x.png`;

    } catch (error) {
        console.error("Error fetching weather data:", error);
        alert("Failed to fetch weather data. Please try again.");
    }
}

fetchWeather("Lahān");

buttonElem.addEventListener("click", () => {
    const city = searchInput.value.trim();
    if (city) {
        fetchWeather(city);
    }
});
