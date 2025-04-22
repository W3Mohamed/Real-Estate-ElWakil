document.addEventListener('DOMContentLoaded', function() {
    console.log('JS loaded');
    
    // Utiliser des sélecteurs plus robustes
    const wilayaSelect = document.querySelector('select[name$="[wilaya]"]');
    const communeSelect = document.querySelector('select[name$="[commune]"]');
    
    if (wilayaSelect && communeSelect) {
        // On récupère les instances Tom Select
        const wilayaTomSelect = wilayaSelect.tomselect;
        const communeTomSelect = communeSelect.tomselect;
        
        if (!wilayaTomSelect || !communeTomSelect) {
            console.error('Tom Select non trouvé');
            return;
        }
        
        // Fonction pour charger les communes
        async function loadCommunes(wilayaId) {
            console.log('Loading communes for wilaya:', wilayaId);
            
            // Désactiver et vider le sélecteur de commune pendant le chargement
            communeTomSelect.disable();
            communeTomSelect.clear();
            communeTomSelect.clearOptions();
            communeTomSelect.addOption({value: '', text: 'Chargement...'});
            
            if (!wilayaId) {
                communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
                return;
            }

            try {
                const url = `/admin/api/communes?wilayaId=${wilayaId}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const communes = await response.json();
                console.log('Communes received:', communes.length);
                
                // Vider les options existantes
                communeTomSelect.clearOptions();
                
                // Ajouter l'option par défaut
                communeTomSelect.addOption({value: '', text: 'Choisissez une commune'});
                
                // Ajouter les nouvelles options
                communes.forEach(commune => {
                    communeTomSelect.addOption({value: commune.id, text: commune.text});
                });
                
                // Si nous sommes en mode édition et qu'il y a une commune déjà sélectionnée
                const selectedCommuneId = communeSelect.getAttribute('data-selected-commune');
                if (selectedCommuneId) {
                    communeTomSelect.setValue(selectedCommuneId);
                }
                
                // Réactiver le sélecteur
                communeTomSelect.enable();
                
            } catch (error) {
                console.error('Erreur:', error);
                communeTomSelect.clearOptions();
                communeTomSelect.addOption({value: '', text: 'Erreur de chargement'});
            }
        }
        
        // Désactiver le sélecteur de commune par défaut
        communeTomSelect.disable();
        communeTomSelect.clear();
        communeTomSelect.clearOptions();
        communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
        
        // Écouter le changement de wilaya via Tom Select
        wilayaTomSelect.on('change', function(wilayaId) {
            loadCommunes(wilayaId);
        });
        
        // Charger les communes pour la wilaya initiale si elle est déjà sélectionnée
        const initialWilayaId = wilayaTomSelect.getValue();
        if (initialWilayaId) {
            // On ajoute un petit délai pour s'assurer que TomSelect est complètement initialisé
            setTimeout(() => {
                loadCommunes(initialWilayaId);
            }, 100);
        }
    } else {
        console.error('Sélecteurs non trouvés');
    }
});