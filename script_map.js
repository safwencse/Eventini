// Sample event data
const events = [
    {
        id: 1,
        title: "Tech Conference 2023",
        date: "Oct 15, 2023",
        time: "9:00 AM",
        location: "Convention Center",
        description: "Annual technology conference with keynote speakers",
        lat: 40.7128,
        lng: -74.0060
    },
    {
        id: 2,
        title: "Music Festival",
        date: "Nov 5, 2023",
        time: "12:00 PM",
        location: "Central Park",
        description: "Outdoor music festival featuring local artists",
        lat: 40.7829,
        lng: -73.9654
    },
    {
        id: 3,
        title: "Food Expo",
        date: "Dec 10, 2023",
        time: "10:00 AM",
        location: "Exhibition Hall",
        description: "Showcase of local and international cuisine",
        lat: 40.7505,
        lng: -73.9934
    }
];

let map;
let markers = [];

// Initialize Google Map
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 40.7128, lng: -74.0060 },
        zoom: 12
    });
    
    // Add markers for all events
    events.forEach(event => {
        addMarker(event);
    });
    
    // Populate event list
    populateEventList();
}

// Add marker to map
function addMarker(event) {
    const marker = new google.maps.Marker({
        position: { lat: event.lat, lng: event.lng },
        map: map,
        title: event.title
    });
    
    // Add info window
    const infoWindow = new google.maps.InfoWindow({
        content: `
            <h3>${event.title}</h3>
            <p><strong>Date:</strong> ${event.date}</p>
            <p><strong>Time:</strong> ${event.time}</p>
            <p><strong>Location:</strong> ${event.location}</p>
            <p>${event.description}</p>
        `
    });
    
    marker.addListener("click", () => {
        infoWindow.open(map, marker);
    });
    
    markers.push(marker);
}

// Populate event list in sidebar
function populateEventList() {
    const eventList = document.querySelector(".event-list");
    eventList.innerHTML = "";
    
    events.forEach(event => {
        const eventCard = document.createElement("div");
        eventCard.className = "event-card";
        eventCard.innerHTML = `
            <h3>${event.title}</h3>
            <p class="event-date">${event.date} • ${event.time}</p>
            <p>${event.location}</p>
            <p>${event.description}</p>
        `;
        
        eventCard.addEventListener("click", () => {
            // Remove active class from all cards
            document.querySelectorAll(".event-card").forEach(card => {
                card.classList.remove("active");
            });
            
            // Add active class to clicked card
            eventCard.classList.add("active");
            
            // Center map on event location
            map.setCenter({ lat: event.lat, lng: event.lng });
            map.setZoom(15);
            
            // Close sidebar on mobile
            if (window.innerWidth <= 768) {
                document.querySelector(".sidebar").classList.remove("open");
            }
        });
        
        eventList.appendChild(eventCard);
    });
}

// Toggle sidebar on mobile
document.querySelector(".menu-toggle").addEventListener("click", () => {
    document.querySelector(".sidebar").classList.add("open");
});

document.querySelector(".close-sidebar").addEventListener("click", () => {
    document.querySelector(".sidebar").classList.remove("open");
});

// Search functionality
document.querySelector(".search-container input").addEventListener("input", (e) => {
    const searchTerm = e.target.value.toLowerCase();
    const eventCards = document.querySelectorAll(".event-card");
    
    eventCards.forEach((card, index) => {
        const event = events[index];
        const matches = event.title.toLowerCase().includes(searchTerm) || 
                       event.location.toLowerCase().includes(searchTerm) ||
                       event.description.toLowerCase().includes(searchTerm);
        
        card.style.display = matches ? "block" : "none";
    });
});

// Filter button functionality
document.querySelector(".filter-btn").addEventListener("click", () => {
    alert("Filter functionality would open a filter panel in a real implementation");
});

// Initialize map when Google Maps API is loaded
window.initMap = initMap;