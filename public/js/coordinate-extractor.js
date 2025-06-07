// Fichier: public/js/coordinate-extractor.js

document.addEventListener('DOMContentLoaded', function() {
    const urlField = document.getElementById('google-maps-url');
    const latField = document.getElementById('latitude-field');
    const lngField = document.getElementById('longitude-field');
    
    if (!urlField || !latField || !lngField) {
        return;
    }

    // Créer un bouton d'extraction
    const extractBtn = document.createElement('button');
    extractBtn.type = 'button';
    extractBtn.className = 'btn btn-outline-primary btn-sm mt-2';
    extractBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Extraire les coordonnées';
    extractBtn.onclick = extractCoordinates;
    
    // Insérer le bouton après le champ URL
    urlField.parentNode.appendChild(extractBtn);

    // Extraction automatique quand l'URL change
    urlField.addEventListener('blur', function() {
        if (this.value.trim()) {
            extractCoordinates();
        }
    });

    function extractCoordinates() {
        const url = urlField.value.trim();
        
        if (!url) {
            showMessage('Veuillez saisir une URL Google Maps', 'warning');
            return;
        }

        // Désactiver le bouton pendant le traitement
        extractBtn.disabled = true;
        extractBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Extraction...';

        try {
            const coordinates = extractFromUrl(url);
            
            if (coordinates) {
                latField.value = coordinates.lat;
                lngField.value = coordinates.lng;
                
                // Déclencher l'événement change pour que les champs soient marqués comme modifiés
                latField.dispatchEvent(new Event('change', { bubbles: true }));
                lngField.dispatchEvent(new Event('change', { bubbles: true }));
                
                showMessage(`Coordonnées extraites: ${coordinates.lat}, ${coordinates.lng}`, 'success');
            } else {
                showMessage('Impossible d\'extraire les coordonnées de cette URL', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showMessage('Erreur lors de l\'extraction des coordonnées', 'error');
        }

        // Réactiver le bouton
        extractBtn.disabled = false;
        extractBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Extraire les coordonnées';
    }

    function extractFromUrl(url) {
        const decodedUrl = decodeURIComponent(url);
        
        // Pattern principal: @latitude,longitude
        let matches = decodedUrl.match(/@([-0-9.]+),([-0-9.]+)(?:,|z|m)/);
        if (matches) {
            return {
                lat: parseFloat(matches[1]),
                lng: parseFloat(matches[2])
            };
        }
        
        // Pattern alternatif: !3d!4d
        matches = decodedUrl.match(/!3d([-0-9.]+)!4d([-0-9.]+)/);
        if (matches) {
            return {
                lat: parseFloat(matches[1]),
                lng: parseFloat(matches[2])
            };
        }
        
        // Pattern ll parameter
        matches = decodedUrl.match(/ll=([-0-9.]+),([-0-9.]+)/);
        if (matches) {
            return {
                lat: parseFloat(matches[1]),
                lng: parseFloat(matches[2])
            };
        }
        
        return null;
    }

    function showMessage(message, type) {
        // Supprimer les anciens messages
        const existingAlert = document.querySelector('.coordinate-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        // Créer un nouveau message
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show coordinate-alert mt-2`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insérer après le bouton d'extraction
        extractBtn.parentNode.appendChild(alert);
        
        // Auto-supprimer après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});