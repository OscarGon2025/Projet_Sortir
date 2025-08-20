import { Controller } from "@hotwired/stimulus";
import L from "leaflet";

// Con este controller podemos enganchar Leaflet a Symfony
export default class extends Controller {
    static targets = ["map", "latitude", "longitude"];

    connect() {
        // Inicializamos el mapa centrado en París
        this.map = L.map(this.mapTarget).setView([48.8566, 2.3522], 13);

        // Añadimos el tile layer (mapa base de OpenStreetMap)
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors"
        }).addTo(this.map);

        // Marcador que se actualizará al hacer clic
        this.marker = null;

        // Evento: clic en el mapa
        this.map.on("click", (e) => {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Si ya existe un marcador, lo movemos; si no, lo creamos
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng]).addTo(this.map);
            }

            // Abrimos popup con las coordenadas
            this.marker.bindPopup(`Marcador en: ${lat.toFixed(5)}, ${lng.toFixed(5)}`).openPopup();

            // Actualizamos los campos del formulario si existen
            if (this.hasLatitudeTarget) this.latitudeTarget.value = lat.toFixed(6);
            if (this.hasLongitudeTarget) this.longitudeTarget.value = lng.toFixed(6);
        });
    }
}
