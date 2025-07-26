document.addEventListener('DOMContentLoaded', function() {
    console.log('JS loaded');
    // Utilisez les sélecteurs spécifiques à EasyAdmin
    const wilayaSelect = document.querySelector('#Bien_wilaya');
    const communeSelect = document.querySelector('#Bien_commune');

    if (wilayaSelect && communeSelect) {
        // Initialiser avec un option vide
        communeSelect.innerHTML = '<option value="">Sélectionnez une wilaya d\'abord</option>';
        communeSelect.disabled = true;
        
        wilayaSelect.addEventListener('change', async function() {
            const wilayaId = this.value;
            communeSelect.innerHTML = '<option value="">Chargement...</option>';
            communeSelect.disabled = true;
            
            if (!wilayaId) {
                communeSelect.innerHTML = '<option value="">Sélectionnez une wilaya d\'abord</option>';
                return;
            }
            
            try {
                const response = await fetch(`/admin/api/communes?wilayaId=${wilayaId}`);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const communes = await response.json();
                
                communeSelect.innerHTML = communes.length 
                    ? '<option value="">Choisissez une commune</option>'
                    : '<option value="">Aucune commune disponible</option>';
                
                communes.forEach(commune => {
                    const option = new Option(commune.text, commune.id);
                    communeSelect.add(option);
                });
                
                communeSelect.disabled = false;
            } catch (error) {
                console.error('Erreur:', error);
                communeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            }
        });
    }

});