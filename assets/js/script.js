// Fonction principale pour gérer les langues
function initLanguageSystem() {
    // Fonction pour corriger les styles après traduction
    function fixStylesAfterTranslation() {
        // Réinitialiser les styles perturbés par Google Translate
        document.querySelectorAll('*').forEach(el => {
            el.style.transform = '';
            el.style.position = '';
            el.style.top = '';
            el.style.left = '';
        });
        
        // Réappliquer les classes Tailwind importantes
        setTimeout(() => {
            document.body.classList.add('font-sans', 'antialiased', 'text-gray-900');
        }, 100);
    }

    // Fonction pour activer une langue
    function activateLanguage(lang) {
        // Sauvegarder la préférence
        document.cookie = `googtrans=/auto/${lang}; path=/; max-age=31536000; SameSite=Lax`;
        
        // Gérer la direction
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        document.body.classList.toggle('rtl', lang === 'ar');
        
        // Recharger la traduction
        if(window.google?.translate?.TranslateElement) {
            try {
                google.translate.TranslateElement().refresh();
            } catch(e) {
                console.log('Erreur de rafraîchissement Google Translate:', e);
            }
            setTimeout(fixStylesAfterTranslation, 300);
        } else {
            loadGoogleTranslate();
        }
    }

    // Initialiser Google Translate
    function loadGoogleTranslate() {
        if(!document.getElementById('google-translate-script')) {
            const script = document.createElement('script');
            script.id = 'google-translate-script';
            script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            document.body.appendChild(script);
        }
    }

    // Callback Google Translate
    window.googleTranslateElementInit = function() {
        new google.translate.TranslateElement({
            pageLanguage: 'fr',
            includedLanguages: 'fr,ar',
            layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL,
            autoDisplay: false
        }, 'google_translate_element');
        
        setTimeout(() => {
            // Masquer les éléments Google
            document.querySelectorAll('.goog-te-banner, .goog-te-gadget, .goog-te-combo')
                .forEach(el => el.style.display = 'none');
            
            fixStylesAfterTranslation();
            checkSavedLanguage();
        }, 500);
    };

    // Vérifier la langue sauvegardée
    function checkSavedLanguage() {
        const langCookie = document.cookie.split(';')
                        .find(c => c.trim().startsWith('googtrans='));
        if(langCookie) {
            const lang = langCookie.split('=')[1].split('/').pop();
            if(['ar', 'fr'].includes(lang)) {
                activateLanguage(lang);
                return;
            }
        }
        activateLanguage('fr');
    }

    // Écouteurs d'événements
    document.querySelectorAll('.lang-switcher').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            activateLanguage(btn.dataset.lang);
        });
    });

    // Initialisation
    if(!window.google?.translate) {
        loadGoogleTranslate();
    } else {
        checkSavedLanguage();
    }
}

// Fonction pour animer les compteurs
function initCounters() {
    const counters = document.querySelectorAll('.counter');
    console.log('Nombre de compteurs trouvés:', counters.length);
    
    function animateCounter(counter) {
        const target = parseInt(counter.getAttribute('data-target'));
        let count = 0;
        const speed = 200;
        const increment = target / speed;
        const duration = 2000;
        const stepTime = duration / (target / increment);
        
        console.log('Compteur cible:', target);
        
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
    
    const section = document.getElementById('chiffresCles');
    
    if (section) {
        console.log('Section trouvée avec ID');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log('Section entre dans la vue, démarrage animation');
                    counters.forEach(counter => animateCounter(counter));
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(section);
    } else {
        console.log('Section non trouvée avec ID, animation immédiate');
        counters.forEach(counter => animateCounter(counter));
    }
}

function initRange(){
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
            const minValue = 0;
            const maxValue = formatPriceAlgeria(values[1]);
            priceValuesDisplay.textContent = `${minValue} - ${maxValue}`;
        }

        noUiSlider.create(priceSlider, {
            start: [0, 500000000], // Départ de 0 à 500M (50MD)
            connect: true,
            step: 100000, // Pas de 100 000 DA (0.1M)
            range: {
                'min': 0,
                'max': 500000000 // Maximum à 500M (50MD)
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
            start: [0, 10000], // Curseur min à 0, max à 10000
            connect: true,
            step: 5,
            range: {
                'min': 0,
                'max': 10000
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
    
        // Initialisation
        updateAreaDisplay([0, 10000]);
        
        areaSlider.noUiSlider.on('update', function(values, handle) {
            const numericValues = values.map(Number);
            areaMinInput.value = numericValues[0];
            areaMaxInput.value = numericValues[1];
            updateAreaDisplay(numericValues);
        });
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
}

function initCommunes(){
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
}

// Fonction pour initialiser tous les scripts
function initializeAllScripts() {
    console.log('Initialisation des scripts...');
    initLanguageSystem();
    initCounters();
    initRange();
    initCommunes();
    initStickyTypes();
    
    // Ajoutez ici d'autres fonctions d'initialisation si nécessaire
}

// Écouteurs d'événements pour Turbo
document.addEventListener('turbo:load', initializeAllScripts);
document.addEventListener('turbo:render', initializeAllScripts);

// Écouteur pour le chargement initial
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAllScripts);
} else {
    initializeAllScripts();
}

console.log('hello world');

    // Fonction pour montrer le CTA discrètement
    // function showDiscreetCTA() {
    //     const cta = document.getElementById('discreet-cta');
    //     cta.classList.remove('hidden');
    //     cta.classList.add('opacity-0');
        
    //     setTimeout(() => {
    //         cta.classList.remove('opacity-0');
    //     }, 50);
        
    //     // Disparaît après 5 secondes
    //     setTimeout(() => {
    //         cta.classList.add('opacity-0');
    //         setTimeout(() => cta.classList.add('hidden'), 500);
    //     }, 5000);
    // }
    
    // // Affiche toutes les 20 secondes
    // setTimeout(showDiscreetCTA, 3000);
    // setInterval(showDiscreetCTA, 20000);
    
    // Affiche aussi au survol des boutons
    // document.querySelectorAll('.fixed a').forEach(btn => {
    //     btn.addEventListener('mouseenter', showDiscreetCTA);
    // });

    function initStickyTypes() {
        const stickySection = document.getElementById('types-sticky-section');
        if (!stickySection) return; // Stop si l'élément n'existe pas
        
        const originalOffset = stickySection.offsetTop;
        const navbar = document.querySelector('nav'); // Adaptez ce sélecteur
        const navbarHeight = navbar ? navbar.offsetHeight : 64; // 64px par défaut
    
        window.addEventListener('scroll', () => {
            const shouldSticky = window.scrollY > stickySection.offsetTop - navbarHeight;
            
            stickySection.classList.toggle('fixed', shouldSticky);
            stickySection.classList.toggle('top-16', shouldSticky);
            stickySection.classList.toggle('left-0', shouldSticky);
            stickySection.classList.toggle('right-0', shouldSticky);
            stickySection.classList.toggle('z-40', shouldSticky);
            stickySection.classList.toggle('shadow-md', shouldSticky);
            
            // Compensation pour le contenu suivant
            const nextElement = stickySection.nextElementSibling;
            if (nextElement) {
                nextElement.classList.toggle('mt-[180px]', shouldSticky);
            }
            // Retour à la position initiale quand on remonte assez
            if (window.scrollY <= originalOffset - navbarHeight) {
                stickySection.classList.remove('fixed', 'top-16', 'left-0', 'right-0', 'z-40', 'shadow-md', 'w-full');
            }
        });
    }
