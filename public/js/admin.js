document.addEventListener('DOMContentLoaded', function() {
    console.log('JS loaded for commune selector');
    
    const wilayaSelect = document.querySelector('select[name$="[wilaya]"]');
    const communeSelect = document.querySelector('select[name$="[commune]"]');
    
    if (wilayaSelect && communeSelect) {
        console.log('Selectors found');
        
        const wilayaTomSelect = wilayaSelect.tomselect;
        const communeTomSelect = communeSelect.tomselect;
        
        if (!wilayaTomSelect || !communeTomSelect) {
            console.error('Tom Select non trouvé');
            return;
        }
        
        // 1. Récupérer l'ID de la commune directement depuis le sélecteur (EasyAdmin le met dans la valeur)
        const selectedCommuneId = communeTomSelect.getValue();
        console.log('Initial commune value:', selectedCommuneId);
        
        // Fonction pour charger les communes
        async function loadCommunes(wilayaId, communeIdToSelect = null) {
            console.log(`Loading communes for wilaya ${wilayaId}, will select ${communeIdToSelect}`);
            
            communeTomSelect.disable();
            communeTomSelect.clear();
            communeTomSelect.clearOptions();
            
            if (!wilayaId) {
                communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
                communeTomSelect.enable();
                return;
            }

            try {
                const url = `/admin/api/communes?wilayaId=${wilayaId}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const communes = await response.json();
                
                communeTomSelect.clearOptions();
                communeTomSelect.addOption({value: '', text: 'Choisissez une commune'});
                
                communes.forEach(commune => {
                    communeTomSelect.addOption({value: commune.id, text: commune.text});
                });
                
                // Sélectionner la commune si elle existe
                if (communeIdToSelect) {
                    // Vérifier que la commune existe dans les options
                    const communeExists = communes.some(c => c.id == communeIdToSelect);
                    if (communeExists) {
                        console.log(`Selecting commune ${communeIdToSelect}`);
                        // Petit timeout pour laisser Tom Select terminer le traitement
                        setTimeout(() => {
                            communeTomSelect.setValue(communeIdToSelect, true);
                        }, 100);
                    }
                }
                
                communeTomSelect.enable();
                
            } catch (error) {
                console.error('Erreur:', error);
                communeTomSelect.clearOptions();
                communeTomSelect.addOption({value: '', text: 'Erreur de chargement'});
                communeTomSelect.enable();
            }
        }
        
        // Initialisation
        communeTomSelect.disable();
        communeTomSelect.clearOptions();
        communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
        
        // Écouter le changement de wilaya
        wilayaTomSelect.on('change', function(wilayaId) {
            loadCommunes(wilayaId);
        });
        
        // Chargement initial si wilaya est déjà sélectionnée
        const initialWilayaId = wilayaTomSelect.getValue();
        if (initialWilayaId) {
            console.log(`Initial load with wilaya ${initialWilayaId} and commune ${selectedCommuneId}`);
            loadCommunes(initialWilayaId, selectedCommuneId);
        }
    }
});