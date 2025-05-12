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
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    const priceValuesDisplay = document.getElementById('price-values');

    if(priceSlider) {
        function formatPriceAlgeria(value) {
            // Convertir en nombre
            const num = parseFloat(value);
            
            // Formater en milliards (MD) si >= 100 millions (1MD = 10 000 000 DA)
            if (num >= 10000000) {
                return (num / 10000000).toFixed(1) + ' MD';
            }
            // Formater en millions (M) si >= 100 000 (1M = 10 000 DA)
            else if (num >= 10000) {
                return (num / 10000).toFixed(1) + ' M';
            }
            // Sinon afficher en DA normaux
            else {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' DA';
            }
        }

        // Fonction pour mettre à jour l'affichage des valeurs
        function updatePriceDisplay(values) {
            const minValue = formatPriceAlgeria(values[0]);
            const maxValue = formatPriceAlgeria(values[1]);
            priceValuesDisplay.textContent = `${minValue} - ${maxValue}`;
        }

        noUiSlider.create(priceSlider, {
            start: [0, 10000000], // Départ de 0 à 10M (1MD)
            connect: true,
            step: 100000, // Pas de 100 000 DA (0.1M)
            range: {
                'min': 0,
                'max': 100000000 // Maximum à 100M (10MD)
            },
            format: {
                // Format pour l'affichage
                to: function(value) {
                    return Math.round(value);
                },
                // Format pour la valeur interne
                from: function(value) {
                    return Number(value);
                }
            }
        });

        // Mise à jour des inputs cachés et de l'affichage
        priceSlider.noUiSlider.on('update', function(values, handle) {
            const numericValues = values.map(Number);
            
            if (handle === 0) {
                priceMinInput.value = numericValues[0];
            } else {
                priceMaxInput.value = numericValues[1];
            }
            
            updatePriceDisplay(numericValues);
        });

        // Initialiser l'affichage
        const initialValues = priceSlider.noUiSlider.get();
        updatePriceDisplay(initialValues);
    }

    // Configuration du slider de superficie
    const areaSlider = document.getElementById('area-slider');
    const areaMinInput = document.getElementById('area-min');
    const areaMaxInput = document.getElementById('area-max');
    const areaValuesDisplay = document.getElementById('area-values');

    if(areaSlider) {
        function updateAreaDisplay(values) {
            const minValue = Math.round(values[0]);
            const maxValue = Math.round(values[1]);
            areaValuesDisplay.textContent = `${minValue} - ${maxValue} m²`;
        }

        noUiSlider.create(areaSlider, {
            start: [0, 500],
            connect: true,
            step: 5,
            range: {
                'min': 0,
                'max': 500
            },
            format: {
                to: function(value) {
                    return Math.round(value);
                },
                from: function(value) {
                    return Number(value);
                }
            }
        });

        areaSlider.noUiSlider.on('update', function(values, handle) {
            const numericValues = values.map(Number);
            
            if (handle === 0) {
                areaMinInput.value = numericValues[0];
            } else {
                areaMaxInput.value = numericValues[1];
            }
            
            updateAreaDisplay(numericValues);
        });

        // Initialiser l'affichage
        const initialValues = areaSlider.noUiSlider.get();
        updateAreaDisplay(initialValues);
    }

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




    const wilayaSelect = document.getElementById('wilaya-select');
    const communeSelect = document.getElementById('commune-select');
    
    if (wilayaSelect && communeSelect) {
        wilayaSelect.addEventListener('change', function() {
            const wilayaId = this.value;
            
            // Réinitialiser la commune
            communeSelect.innerHTML = '<option value="">Commune</option>';
            communeSelect.disabled = !wilayaId;
            
            if (wilayaId) {
                fetch(`/get-communes/${wilayaId}`)
                    .then(response => response.json())
                    .then(communes => {
                        communes.forEach(commune => {
                            const option = document.createElement('option');
                            option.value = commune.id;
                            option.textContent = `${commune.nom} (${commune.code_postal})`;
                            communeSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
        
        // Si une wilaya est déjà sélectionnée au chargement
        if (wilayaSelect.value) {
            wilayaSelect.dispatchEvent(new Event('change'));
        }
    }

});