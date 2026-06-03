// =======================
// CONFIG GENERAL
// =======================
const osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
const osmAttr = '&copy; OpenStreetMap contributors';

// =======================
// MAPA BASE
// =======================
var mymap = L.map("leaflet-map").setView([51.505, -0.09], 13);
L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttr }).addTo(mymap);

// =======================
// MAPA CON MARCADOR
// =======================
var markermap = L.map("leaflet-map-marker").setView([51.505, -0.09], 13);

L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttr }).addTo(markermap);

L.marker([51.5, -0.09]).addTo(markermap);

L.circle([51.508, -0.11], {
    color: "#0ab39c",
    fillColor: "#0ab39c",
    fillOpacity: 0.5,
    radius: 500
}).addTo(markermap);

L.polygon([
    [51.509, -0.08],
    [51.503, -0.06],
    [51.51, -0.047]
], {
    color: "#405189",
    fillColor: "#405189"
}).addTo(markermap);

// =======================
// MAPA CON POPUPS
// =======================
var popupmap = L.map("leaflet-map-popup").setView([51.505, -0.09], 13);

L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttr }).addTo(popupmap);

L.marker([51.5, -0.09])
    .addTo(popupmap)
    .bindPopup("<b>Hello world!</b><br />I am a popup.")
    .openPopup();

L.circle([51.508, -0.11], 500, {
    color: "#f06548",
    fillColor: "#f06548",
    fillOpacity: 0.5
}).addTo(popupmap).bindPopup("I am a circle.");

L.polygon([
    [51.509, -0.08],
    [51.503, -0.06],
    [51.51, -0.047]
], {
    color: "#405189",
    fillColor: "#405189"
}).addTo(popupmap).bindPopup("I am a polygon.");

// =======================
// MAPA CON ICONOS
// =======================
var customiconsmap = L.map("leaflet-map-custom-icons").setView([51.5, -0.09], 13);

L.tileLayer(osmUrl, { attribution: osmAttr }).addTo(customiconsmap);

var LeafIcon = L.Icon.extend({
    options: {
        iconSize: [45, 45],
        iconAnchor: [22, 94],
        popupAnchor: [-3, -76]
    }
});

var greenIcon = new LeafIcon({
    iconUrl: "assets/images/logo-sm.png"
});

L.marker([51.5, -0.09], { icon: greenIcon }).addTo(customiconsmap);

// =======================
// MAPA INTERACTIVO
// =======================
var interactivemap = L.map("leaflet-map-interactive-map").setView([37.8, -96], 4);

function getColor(e) {
    return e > 1000 ? "#405189" :
           e > 500  ? "#516194" :
           e > 200  ? "#63719E" :
           e > 100  ? "#7480A9" :
           e > 50   ? "#8590B4" :
           e > 20   ? "#97A0BF" :
                      "#A8B0C9";
}

function style(e) {
    return {
        weight: 2,
        opacity: 1,
        color: "white",
        dashArray: "3",
        fillOpacity: 0.7,
        fillColor: getColor(e.properties.density)
    };
}

L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttr }).addTo(interactivemap);

if (typeof statesData !== "undefined") {
    L.geoJson(statesData, { style: style }).addTo(interactivemap);
}

// =======================
// MAPA CON CAPAS
// =======================
var cities = L.layerGroup();

L.marker([39.61, -105.02]).bindPopup("Littleton").addTo(cities);
L.marker([39.74, -104.99]).bindPopup("Denver").addTo(cities);
L.marker([39.73, -104.8]).bindPopup("Aurora").addTo(cities);
L.marker([39.77, -105.23]).bindPopup("Golden").addTo(cities);

var grayscale = L.tileLayer(osmUrl, { attribution: osmAttr });
var streets = L.tileLayer(osmUrl, { attribution: osmAttr });

var layergroupcontrolmap = L.map("leaflet-map-group-control", {
    center: [39.73, -104.99],
    zoom: 10,
    layers: [streets, cities]
});

var baseLayers = {
    "Mapa": streets,
    "Grises": grayscale
};

var overlays = {
    "Ciudades": cities
};

L.control.layers(baseLayers, overlays).addTo(layergroupcontrolmap);