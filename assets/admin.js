document.addEventListener('DOMContentLoaded', function() {
    // Gestion du drag and drop pour les images
    const collectionHolder = document.querySelector('.field-collection');
    
    if (collectionHolder) {
        collectionHolder.addEventListener('dragover', e => {
            e.preventDefault();
            collectionHolder.classList.add('dragover');
        });
        
        collectionHolder.addEventListener('dragleave', () => {
            collectionHolder.classList.remove('dragover');
        });
        
        collectionHolder.addEventListener('drop', e => {
            e.preventDefault();
            collectionHolder.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                const lastAddButton = collectionHolder.querySelector('.field-collection-add-button');
                if (lastAddButton) {
                    lastAddButton.click();
                    
                    setTimeout(() => {
                        const lastFileInput = collectionHolder.querySelector('.image-upload:last-child');
                        if (lastFileInput) {
                            lastFileInput.files = e.dataTransfer.files;
                            const event = new Event('change', { bubbles: true });
                            lastFileInput.dispatchEvent(event);
                        }
                    }, 100);
                }
            }
        });
    }
});