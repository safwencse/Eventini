document.addEventListener('DOMContentLoaded', function() {
  // Animation des champs
  const animateInputGroups = () => {
      const groups = document.querySelectorAll(".input-group");
      groups.forEach((group, i) => {
          group.style.transitionDelay = `${i * 100}ms`;
          group.style.opacity = "1";
          group.style.transform = "translateY(0)";
      });
  };
  animateInputGroups();

  // Menu toggle functionality
  document.querySelector('.menu-toggle').addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('active');
  });

  // Tab switching functionality
  document.getElementById('events-tab').addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('events-section').style.display = 'block';
      document.getElementById('participations-section').style.display = 'none';
      document.querySelectorAll('.content-nav .nav-item').forEach(el => el.classList.remove('active'));
      this.classList.add('active');
  });
  
  document.getElementById('participations-tab').addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('events-section').style.display = 'none';
      document.getElementById('participations-section').style.display = 'block';
      document.querySelectorAll('.content-nav .nav-item').forEach(el => el.classList.remove('active'));
      this.classList.add('active');
  });

  // Initialize map if on map page
  if (document.getElementById('map-page') && document.getElementById('map-page').style.display === 'block') {
      initMap();
  }

  function initMap() {
      const map = L.map('map').setView([36.8065, 10.1815], 13); // Default to Tunisia coordinates
      
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      // Check if events data exists
      const eventsJsonElement = document.getElementById('events-json');
      if (!eventsJsonElement) return;

      const events = JSON.parse(eventsJsonElement.textContent);
      const markers = L.markerClusterGroup();

      // Custom icons
      const createdIcon = L.icon({
          iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet-color-markers@1.0.3/img/marker-icon-2x-orange.png',
          shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41],
          popupAnchor: [1, -34],
          shadowSize: [41, 41]
      });

      const participatedIcon = L.icon({
          iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet-color-markers@1.0.3/img/marker-icon-2x-blue.png',
          shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41],
          popupAnchor: [1, -34],
          shadowSize: [41, 41]
      });

      events.forEach(event => {
          const icon = event.user_relation === 'created' ? createdIcon : participatedIcon;
          const marker = L.marker([event.latitude, event.longitude], { icon })
              .bindPopup(`
                  <div class="map-popup">
                      <h3>${event.title || 'No title'}</h3>
                      <p><strong>Date:</strong> ${event.date || 'N/A'}</p>
                      <p><strong>Location:</strong> ${event.location || 'N/A'}</p>
                      ${event.image ? `<img src="../resources/images/${event.image}" style="max-width:100%;height:100px;object-fit:cover;">` : ''}
                  </div>
              `);
          markers.addLayer(marker);
      });

      map.addLayer(markers);
      
      // Fit bounds to show all markers
      if (events.length > 0) {
          map.fitBounds(markers.getBounds());
      }
  }

  // File input preview for profile picture
  const profileUpload = document.getElementById('profileUpload');
  if (profileUpload) {
      profileUpload.addEventListener('change', function() {
          const file = this.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  document.querySelector('.profile-photo').src = e.target.result;
              }
              reader.readAsDataURL(file);
          }
      });
  }
});

// Ticket management (from your existing code)
document.addEventListener('DOMContentLoaded', function () {
  const addTicketBtn = document.querySelector('.add-type-btn');
  const ticketContainer = document.getElementById('ticket-types-container');

  if (addTicketBtn && ticketContainer) {
      addTicketBtn.addEventListener('click', function () {
          const card = document.createElement('div');
          card.classList.add('ticket-card');

          card.innerHTML = `
              <button type="button" class="delete-ticket-btn">&times;</button>
              <input type="text" name="ticket_name[]" placeholder="Nom du ticket (ex: VIP, Standard...)" required>
              <input type="number" name="ticket_price[]" placeholder="Prix (€)" required>
              <input type="number" name="ticket_quantity[]" placeholder="Quantité" required>
          `;

          ticketContainer.appendChild(card);

          // Delete ticket card on click
          const deleteBtn = card.querySelector('.delete-ticket-btn');
          deleteBtn.addEventListener('click', function () {
              card.remove();
          });
      });
  }
});