document.addEventListener('DOMContentLoaded', function () {
    console.log('hello world');
    
    const counters = document.querySelectorAll('.counter');
    console.log('Nombre de compteurs trouvés:', counters.length); // Vérifie si les éléments sont trouvés
    
    // Animation simple, démarrage immédiat sans observer
    function animateCounter(counter) {
        const target = parseInt(counter.getAttribute('data-target'));
        let count = 0;
        const speed = 200;
        const increment = target / speed;
        const duration = 2000; // Durée totale de l'animation en ms
        const stepTime = duration / (target / increment);
        
        console.log('Compteur cible:', target); // Pour vérifier les valeurs cibles
        
        function update() {
            if (count < target) {
                count += increment;
                counter.innerText = Math.ceil(count);
                setTimeout(update, stepTime);
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
        
        update();
    }
    
    // Option 1: Démarrer l'animation immédiatement
    counters.forEach(counter => {
        animateCounter(counter);
    });
    
    // Option 2: Utiliser l'ID que vous avez ajouté
    const section = document.getElementById('chiffresCles');
    
    if (section) {
        console.log('Section trouvée avec ID');
        
        // Vérifier si la section est visible dès le chargement
        const rect = section.getBoundingClientRect();
        const isVisible = (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
        
        if (isVisible) {
            console.log('Section visible, démarrage animation');
            counters.forEach(counter => animateCounter(counter));
        } else {
            // Si pas visible initialement, utiliser IntersectionObserver
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        console.log('Section entre dans la vue, démarrage animation');
                        counters.forEach(counter => animateCounter(counter));
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 }); // Diminuer le seuil pour détecter plus tôt
            
            observer.observe(section);
        }
    } else {
        console.log('Section non trouvée avec ID, utilisation du plan B');
        // Plan B: Si la section avec ID n'est pas trouvée, animer quand même
        counters.forEach(counter => animateCounter(counter));
    }
});