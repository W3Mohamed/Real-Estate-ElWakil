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

    // Configuration du slider de prix
    const priceSlider = document.getElementById('price-slider');
    const priceMin = document.getElementById('price-min');
    const priceMax = document.getElementById('price-max');
    if(priceSlider){
        console.log('hello');
        function formatPriceAlgeria(value) {
            // Convertir en nombre
            const num = parseFloat(value);
            
            // Formater en milliards si >= 1 milliard
            if (num >= 1000000000) {
                return (num / 1000000000).toFixed(2) + ' MD DA';
            }
            // Formater en millions si >= 1 million
            else if (num >= 1000000) {
                return (num / 1000000).toFixed(2) + ' M DA';
            }
            // Sinon formater avec des séparateurs de milliers
            else {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' DA';
            }
        }

        noUiSlider.create(priceSlider, {
            start: [1000000, 5000000],
            connect: true,
            step: 50000,
            range: {
                'min': 0,
                'max': 10000000000 // Maximum étendu à 10 milliards
            },
            format: {
                // Format pour l'affichage
                to: function(value) {
                    return formatPriceAlgeria(value);
                },
                // Format pour la valeur interne
                from: function(value) {
                    // Convertir de "X.XX M DA" ou "X.XX MD DA" en nombre
                    if (value.indexOf('MD') !== -1) {
                        return Number(value.replace(' MD DA', '')) * 1000000000;
                    } else if (value.indexOf('M') !== -1) {
                        return Number(value.replace(' M DA', '')) * 1000000;
                    } else {
                        return Number(value.replace(/[^\d.-]/g, ''));
                    }
                }
            }
        });

        priceSlider.noUiSlider.on('update', function(values, handle) {
            if (handle === 0) {
                priceMin.value = values[0];
            } else {
                priceMax.value = values[1];
            }
        });

        // Configuration du slider de superficie
        const areaSlider = document.getElementById('area-slider');
        const areaMin = document.getElementById('area-min');
        const areaMax = document.getElementById('area-max');

        noUiSlider.create(areaSlider, {
            start: [50, 200],
            connect: true,
            step: 5,
            range: {
                'min': 0,
                'max': 500
            },
            format: wNumb({
                decimals: 0,
                suffix: ' m²'
            })
        });

        areaSlider.noUiSlider.on('update', function(values, handle) {
            if (handle === 0) {
                areaMin.value = values[0];
            } else {
                areaMax.value = values[1];
            }
        });

        // Personnalisation de l'apparence des sliders
        const sliders = document.querySelectorAll('.noUi-target');
        sliders.forEach(slider => {
            slider.classList.add('bg-gray-200');
            
            // Changer la couleur de la barre de connexion
            const connect = slider.querySelector('.noUi-connect');
            connect.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-indigo-600');
            
            // Styliser les poignées
            const handles = slider.querySelectorAll('.noUi-handle');
            handles.forEach(handle => {
                handle.classList.add('bg-white', 'shadow-md', 'border-2', 'border-blue-500');
                handle.classList.remove('border-0');
                handle.style.width = '20px';
                handle.style.height = '20px';
                handle.style.right = '-10px';
                handle.style.top = '-10px';
                handle.style.borderRadius = '50%';
                
                // Enlever les lignes sur les poignées
                const lines = handle.querySelectorAll('div');
                lines.forEach(line => line.remove());
            });
        });

    }



    // Fonction pour montrer le CTA discrètement
    function showDiscreetCTA() {
        const cta = document.getElementById('discreet-cta');
        cta.classList.remove('hidden');
        cta.classList.add('opacity-0');
        
        setTimeout(() => {
            cta.classList.remove('opacity-0');
        }, 50);
        
        // Disparaît après 5 secondes
        setTimeout(() => {
            cta.classList.add('opacity-0');
            setTimeout(() => cta.classList.add('hidden'), 500);
        }, 5000);
    }
    
    // Affiche toutes les 20 secondes
    setTimeout(showDiscreetCTA, 3000);
    setInterval(showDiscreetCTA, 20000);
    
    // Affiche aussi au survol des boutons
    document.querySelectorAll('.fixed a').forEach(btn => {
        btn.addEventListener('mouseenter', showDiscreetCTA);
    });


});