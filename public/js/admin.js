document.addEventListener('DOMContentLoaded', function() {
    console.log('JS loaded');
    
    // Utiliser des sélecteurs plus robustes
    const wilayaSelect = document.querySelector('select[name$="[wilaya]"]');
    const communeSelect = document.querySelector('select[name$="[commune]"]');
    
    console.log('Wilaya select found:', wilayaSelect);
    console.log('Commune select found:', communeSelect);
    
    if (wilayaSelect && communeSelect) {
        // Initialiser avec une option vide
        communeSelect.innerHTML = '<option value="">Sélectionnez une wilaya d\'abord</option>';
        communeSelect.disabled = true;
        
        wilayaSelect.addEventListener('change', async function() {
            const wilayaId = this.value;
            console.log('Wilaya changed to:', wilayaId);
            
            communeSelect.innerHTML = '<option value="">Chargement...</option>';
            communeSelect.disabled = true;
            
            if (!wilayaId) {
                communeSelect.innerHTML = '<option value="">Sélectionnez une wilaya d\'abord</option>';
                return;
            }


            try {
                const url = `/admin/api/communes?wilayaId=${wilayaId}`;
                console.log('Fetching communes from:', url);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const communes = await response.json();
                console.log('Communes received:', communes);
                console.log('Nombre de communes:', communes.length);
                
                // Vider et recréer le sélecteur pour éviter les conflits
                communeSelect.innerHTML = '';
                communeSelect.appendChild(new Option('Choisissez une commune', ''));
                
                communes.forEach(commune => {
                    console.log('Ajout commune:', commune);
                    const option = document.createElement('option');
                    option.value = commune.id;
                    option.textContent = commune.text;
                    communeSelect.appendChild(option);
                });
                
                // Essayer plusieurs méthodes pour activer le select
                communeSelect.disabled = false;
                communeSelect.removeAttribute('disabled');
                
                console.log('État final du select:', {
                    options: communeSelect.options.length,
                    disabled: communeSelect.disabled,
                    firstOption: communeSelect.options[0]?.textContent
                });
                
                // Forcer un rafraîchissement du DOM
                setTimeout(() => {
                    console.log('État après timeout:', communeSelect.disabled);
                }, 200);
            } catch (error) {
                console.error('Erreur:', error);
                communeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
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