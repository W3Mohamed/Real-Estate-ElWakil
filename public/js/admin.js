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
        
        // Désactiver le sélecteur de commune par défaut
        communeTomSelect.disable();
        communeTomSelect.clear();
        communeTomSelect.clearOptions();
        communeTomSelect.addOption({value: '', text: 'Sélectionnez une wilaya d\'abord'});
        
        // Écouter le changement de wilaya via Tom Select
        wilayaTomSelect.on('change', async function(wilayaId) {
            console.log('Wilaya changed to:', wilayaId);
            
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

                // Vider les options existantes
                communeTomSelect.clearOptions();
                
                // Ajouter l'option par défaut
                communeTomSelect.addOption({value: '', text: 'Choisissez une commune'});
                
                // Ajouter les nouvelles options
                communes.forEach(commune => {
                    communeTomSelect.addOption({value: commune.id, text: commune.text});
                });
                
                // Réactiver le sélecteur
                communeTomSelect.enable();
                
            } catch (error) {
                console.error('Erreur:', error);
                communeTomSelect.clearOptions();
                communeTomSelect.addOption({value: '', text: 'Erreur de chargement'});
            }
        });
    } else {
        console.error('Sélecteurs non trouvés:');
        console.error('- Tous les selects:', document.querySelectorAll('select'));
        document.querySelectorAll('select').forEach(select => {
            console.log('Select name:', select.name, 'id:', select.id);
        });
    }
});