document.addEventListener("DOMContentLoaded", function () {
    // Crear el mapa centrado en Rennes por defecto
    const map = L.map("map").setView([48.1173, -1.6778], 13);

    // Capa base de OSM
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker;

    document.getElementById("searchBtn").addEventListener("click", function () {
        const postalCode = document.getElementById("postalCode").value.trim();
        const city = document.getElementById("city").value.trim();
        const address = document.getElementById("address").value.trim();

        if (!postalCode && !city && !address) {
            alert("Veuillez remplir au moins un champ pour la recherche !");
            return;
        }

        // Construir la query para Nominatim
        let query = "";
        if (address) query += address + ", ";
        if (city) query += city + ", ";
        if (postalCode) query += postalCode + ", ";
        query += "France";

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = data[0].lat;
                    const lon = data[0].lon;

                    map.setView([lat, lon], 14);

                    if (marker) {
                        marker.setLatLng([lat, lon]);
                    } else {
                        marker = L.marker([lat, lon]).addTo(map);
                    }

                    marker.bindPopup(data[0].display_name).openPopup();
                } else {
                    alert("Aucun résultat trouvé.");
                }
            })
            .catch(err => console.error(err));
    });
});
