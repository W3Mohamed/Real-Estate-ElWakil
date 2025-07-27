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

    // Sélecteurs modifiés pour ManyToMany (EasyAdmin utilise un nom différent)
    const wilayasSelect = document.querySelector('select[name$="[wilayas][]"]');
    
    if (wilayasSelect && communeSelect) {
        console.log('Selectors found');
        
        const wilayaTomSelect = wilayasSelect.tomselect;
        const communeTomSelect = communeSelect.tomselect;
        
        if (!wilayaTomSelect || !communeTomSelect) {
            console.error('Tom Select non trouvé');
            return;
        }
        
        // Fonction pour charger les communes basée sur la PREMIÈRE wilaya sélectionnée
        async function loadCommunes(wilayaIds, communeIdToSelect = null) {
            const firstWilayaId = wilayaIds?.[0];
            console.log(`Loading communes for first wilaya ${firstWilayaId}, will select ${communeIdToSelect}`);
            
            communeTomSelect.disable();
            communeTomSelect.clear();
            communeTomSelect.clearOptions();
            
            if (!firstWilayaId) {
                communeTomSelect.addOption({value: '', text: 'Sélectionnez au moins une wilaya d\'abord'});
                communeTomSelect.enable();
                return;
            }

            try {
                const url = `/admin/api/communes?wilayaId=${firstWilayaId}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const communes = await response.json();
                
                communeTomSelect.clearOptions();
                communeTomSelect.addOption({value: '', text: 'Choisissez une commune'});
                
                communes.forEach(commune => {
                    communeTomSelect.addOption({value: commune.id, text: commune.text});
                });
                
                if (communeIdToSelect) {
                    setTimeout(() => {
                        communeTomSelect.setValue(communeIdToSelect, true);
                    }, 100);
                }
                
                communeTomSelect.enable();
                
            } catch (error) {
                console.error('Erreur:', error);
                communeTomSelect.addOption({value: '', text: 'Erreur de chargement'});
                communeTomSelect.enable();
            }
        }
        
        // Initialisation
        communeTomSelect.disable();
        communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
        
        // Écouteur modifié pour ManyToMany
        wilayaTomSelect.on('change', function(selectedIds) {
            loadCommunes(selectedIds || []);
        });
        
        // Chargement initial
        const initialWilayaIds = wilayaTomSelect.getValue() || [];
        const initialCommuneId = communeTomSelect.getValue();
        if (initialWilayaIds.length > 0) {
            loadCommunes(initialWilayaIds, initialCommuneId);
        }
    }

});