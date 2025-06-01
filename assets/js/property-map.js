// assets/js/property-map.js

// Fonction globale pour créer une icône avec prix
function createPriceIcon(price, isCurrentProperty = false) {
    const formattedPrice = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
    
    const backgroundColor = isCurrentProperty ? '#dc2626' : '#2563eb';
    const textColor = 'white';
    
    return L.divIcon({
        html: `
            <div style="position: relative; text-align: center;">
                <!-- Prix au-dessus -->
                <div style="
                    background: ${backgroundColor};
                    color: ${textColor};
                    padding: 2px 6px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: bold;
                    white-space: nowrap;
                    margin-bottom: 2px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    border: 1px solid white;
                ">${formattedPrice} DA</div>
                <!-- Flèche de localisation -->
                <div style="
                    width: 0;
                    height: 0;
                    border-left: 8px solid transparent;
                    border-right: 8px solid transparent;
                    border-top: 12px solid ${backgroundColor};
                    margin: 0 auto;
                    filter: drop-shadow(0 2px 2px rgba(0,0,0,0.3));
                "></div>
            </div>
        `,
        className: isCurrentProperty ? 'current-property-marker' : 'other-property-marker',
        iconSize: [120, 40],
        iconAnchor: [60, 40],
        popupAnchor: [0, -40]
    });
}

function initPropertyMap() {
    const mapContainer = document.getElementById('property-map');
    if (!mapContainer || !mapContainer.dataset.property) return;
    
    try {
        const propertyData = JSON.parse(mapContainer.dataset.property);
        console.log('Initialisation carte:', propertyData);

        if (!propertyData.latitude || !propertyData.longitude) {
            mapContainer.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-500"><p>Coordonnées non disponibles</p></div>';
            return;
        }

        // Vider le contenu de chargement
        mapContainer.innerHTML = '';

        // Attendre que Leaflet soit complètement chargé et que le DOM soit prêt
        setTimeout(() => {
            // Vérifier que le conteneur est visible et a des dimensions
            const rect = mapContainer.getBoundingClientRect();
            console.log('Dimensions du conteneur:', rect);

            if (rect.width === 0 || rect.height === 0) {
                console.warn('Le conteneur de la carte n\'a pas de dimensions');
                return;
            }

            // Initialiser la carte avec un zoom plus large pour voir les autres biens
            const map = L.map('property-map', {
                preferCanvas: false,
                attributionControl: true,
                zoomControl: true
            }).setView([propertyData.latitude, propertyData.longitude], 12); // Zoom réduit pour voir plus de biens

            // Ajouter les tuiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Ajouter le marqueur du bien actuel avec prix visible
            const currentIcon = createPriceIcon(propertyData.prix, true);
            const currentMarker = L.marker([propertyData.latitude, propertyData.longitude], { icon: currentIcon })
                .addTo(map);

            // Popup pour le bien actuel
            const currentPopupContent = `
                <div class="property-popup">
                    <div class="price">${new Intl.NumberFormat('fr-FR').format(propertyData.prix)} DA</div>
                    <div class="title">${propertyData.libelle}</div>
                    <div class="address">${propertyData.adresse}</div>
                    <div style="color: #dc2626; font-weight: bold; font-size: 12px;">📍 Bien actuel</div>
                </div>
            `;
            currentMarker.bindPopup(currentPopupContent);

            // Charger immédiatement tous les autres biens
            loadAllProperties(map, propertyData.id);

            // Forcer le recalcul de la taille après un court délai
            setTimeout(() => {
                map.invalidateSize();
                console.log('Carte redimensionnée');
            }, 100);

            // Ajouter un délai supplémentaire pour s'assurer que la carte se redimensionne correctement
            setTimeout(() => {
                map.invalidateSize();
                // Recentrer la vue si nécessaire
                map.setView([propertyData.latitude, propertyData.longitude], map.getZoom());
            }, 500);

            console.log('Carte initialisée avec succès');

        }, 150); // Délai légèrement augmenté

    } catch (error) {
        console.error('Erreur carte:', error);
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="w-full h-full flex items-center justify-center text-red-500"><p>Erreur de chargement de la carte</p></div>';
        }
    }
}

// Fonction pour charger tous les autres biens
async function loadAllProperties(map, currentPropertyId) {
    console.log('Chargement de tous les biens...');
    
    try {
        const response = await fetch('/api/biens');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const properties = await response.json();
        
        console.log('Biens récupérés:', properties.length);
        
        properties.forEach(property => {
            // Ne pas afficher le bien actuel
            if (property.id === currentPropertyId) return;
            
            if (property.latitude && property.longitude) {
                // Créer une icône avec prix pour chaque bien en utilisant la fonction globale
                const otherIcon = createPriceIcon(property.prix, false);
                const otherMarker = L.marker([property.latitude, property.longitude], { icon: otherIcon }).addTo(map);
                
                const otherPopupContent = `
                    <div class="property-popup">
                        <div class="price">${new Intl.NumberFormat('fr-FR').format(property.prix)} DA</div>
                        <div class="title">${property.libelle}</div>
                        <div class="address">${property.adresse}</div>
                        <a href="/detail?id=${property.id}" class="view-btn">Voir détails</a>
                    </div>
                `;
                otherMarker.bindPopup(otherPopupContent);
            }
        });
        
        console.log('Tous les biens chargés');
    } catch (error) {
        console.error('Erreur lors du chargement des biens:', error);
    }
}

function initializeScript() {
    console.log('Initialisation du script...');
    
    // Attendre un peu plus longtemps pour s'assurer que tout est chargé
    if (document.readyState === 'complete') {
        initPropertyMap();
    } else {
        window.addEventListener('load', () => {
            // Petit délai supplémentaire pour être sûr
            setTimeout(initPropertyMap, 100);
        });
    }
}

document.addEventListener('turbo:load', initializeScript);
document.addEventListener('turbo:render', initializeScript);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeScript);
} else {
    initializeScript();
}