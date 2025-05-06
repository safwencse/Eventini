<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un événement - Eventini</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assests/css/style_form.css">

</head>
<body>
  <form action="../config/formulaire.php" method="POST" enctype="multipart/form-data" class="event-form">
    <div class="form-header">
      <a href="javascript:history.back()" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
      <h2><i class="fas fa-calendar-plus"></i> Créer un événement</h2>
    </div>

      <div class="input-group">
        <input type="tel" id="telephone" name="telephone" pattern="[0-9]{8}" placeholder=" " required>
        <label for="telephone">Téléphone</label>
      </div>
      <div class="input-group">
        <input type="date" id="date-debut" name="date-debut" placeholder=" " required>
        <label for="date-debut">Date de début</label>
      </div>
      <div class="input-group">
        <input type="date" id="date-fin" name="date-fin" placeholder=" ">
        <label for="date-fin">Date de fin</label>
      </div>
      <div class="input-group">
        <input type="time" id="event-time" name="event-time" placeholder=" " required>
        <label for="event-time">Heure</label>
      </div>
      <div class="input-group wide">
        <input type="text" id="lieu" name="lieu" placeholder=" " required>
        <label for="lieu">Lieu (ville, adresse)</label>
      </div>
      <div class="input-group">
        <select id="type" name="type" required>
          <option value="" disabled selected></option>
          <option value="concert">Concert</option>
          <option value="conference">Conférence</option>
          <option value="atelier">Atelier</option>
          <option value="festival">Festival</option>
          <option value="autre">Autre</option>
        </select>
        <label for="type">Catégorie</label>
      </div>
      <div class="input-group">
        <select id="visibilite" name="visibilite" required>
          <option value="" disabled selected></option>
          <option value="publique">publique</option>
          <option value="prive">privé</option>
        </select>
        <label for="visibilite">Type</label>
      </div>
      <div class="input-group" id="password-group" style="display: none;">
        <input type="password" id="password" name="password" placeholder=" ">
        <label for="password">Mot de passe</label>
      </div>
      <div class="input-group wide">
        <textarea id="description" name="description" rows="4" placeholder=" " required></textarea>
        <label for="description">Description</label>
      </div>
      <div class="input-group">
        <input type="number" id="total-tickets" name="total_tickets" placeholder=" " required min="1">
        <label for="total-tickets">Total tickets</label>
      </div>
    </div>

    <div class="ticket-section">
      <div id="ticket-types-container" class="ticket-types-container">
        <!-- Les types de tickets seront ajoutés ici dynamiquement -->
      </div>
      <button type="button" class="add-type-btn">
        <i class="fas fa-plus-circle"></i> Ajouter un ticket
      </button>
    </div>

    <div class="input-group">
      <label for="image">Image de l'événement :</label>
      <br>
      <br>
      <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="input-group legal-docs">
      <label for="legal-docs">Documents légaux (Kbis, CIN, autorisations...) :</label>
      <br>
      <br>

      <input type="file" id="legal-docs" name="legal-docs[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
      <span class="doc-note">Formats acceptés : PDF, JPG, PNG. Taille maximale : 5MB par fichier.</span>
    </div>

    <button type="submit" class="submit-btn">
      <i class="fas fa-check-circle"></i> Créer l'événement
    </button>
  </form>

  <script>
    // Gestion de l'affichage du champ mot de passe pour les événements privés
    document.getElementById('visibilite').addEventListener('change', function() {
      const passwordGroup = document.getElementById('password-group');
      if (this.value === 'prive') {
        passwordGroup.style.display = 'block';
        passwordGroup.querySelector('input').required = true;
      } else {
        passwordGroup.style.display = 'none';
        passwordGroup.querySelector('input').required = false;
      }
    });

    // Gestion des types de tickets
    document.querySelector('.add-type-btn').addEventListener('click', function() {
      const container = document.getElementById('ticket-types-container');
      const typeCount = container.children.length + 1;
      
      const ticketTypeDiv = document.createElement('div');
      ticketTypeDiv.className = 'ticket-type';
      
      ticketTypeDiv.innerHTML = `
        <input type="text" name="ticket-name[]" placeholder=" " required>
        <label style="position: static; color: var(--gray-dark); margin-bottom: 5px; font-size: 14px;">Nom du ticket</label>
        <input type="number" name="ticket-price[]" placeholder=" " min="0" step="0.01" required>
        <label style="position: static; color: var(--gray-dark); margin-bottom: 5px; font-size: 14px;">Prix (€)</label>
        <input type="number" name="ticket-quantity[]" placeholder=" " min="1" required>
        <label style="position: static; color: var(--gray-dark); margin-bottom: 5px; font-size: 14px;">Quantité</label>
        <button type="button" class="remove-type-btn">
          <i class="fas fa-times"></i>
        </button>
      `;
      
      container.appendChild(ticketTypeDiv);
      
      // Ajouter l'événement de suppression
      ticketTypeDiv.querySelector('.remove-type-btn').addEventListener('click', function() {
        container.removeChild(ticketTypeDiv);
      });
    });

    // Animation des champs lorsqu'ils sont focus
    document.querySelectorAll('input, select, textarea').forEach(element => {
      element.addEventListener('focus', function() {
        this.parentNode.querySelector('label').style.color = 'var(--primary)';
      });
      
      element.addEventListener('blur', function() {
        if (!this.value) {
          this.parentNode.querySelector('label').style.color = 'var(--gray-dark)';
        }
      });
      
      // Initialiser les labels pour les champs pré-remplis
      if (element.value) {
        element.dispatchEvent(new Event('blur'));
      }
    });

    // Validation du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
      const ticketTypes = document.querySelectorAll('.ticket-type');
      if (ticketTypes.length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins un type de ticket.');
      }
    });
  </script>
</body>
</html>