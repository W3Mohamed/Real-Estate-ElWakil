// Dans votre script.js ou dans un fichier séparé
console.log('sliders.js chargée');
function initSliders(){   
    // Initialisation du carrousel Hero
    if (document.querySelector('#sliderImg')) {
        new Splide('#sliderImg', {
            type: 'slide',
            autoplay: true,
            interval: 4000,
            pauseOnHover: false,
            arrows: true,
            pagination: true,
            speed: 500,
            rewind: true,
        }).mount();
    }

    // Initialisation du carrousel types
    if (document.querySelector('#types-carousel')) {
        new Splide('#types-carousel', {
            type: 'slide',
            perPage: 8,
            perMove: 1,
            gap: '0.5rem',
            arrows: true,
            pagination: true,
            drag: true,
            breakpoints: {
                1280: { perPage: 7 },
                1024: { perPage: 6 },
                768: { perPage: 5 },
                640: { perPage: 4 },
                480: { perPage: 3 }
            }
        }).mount();
    }

    // Initialisation des carrousels d'images
    document.querySelectorAll('.splideImg').forEach(carousel => {
        new Splide(carousel, {
            type: 'slide',
            rewind: true,
            arrows: true, 
            pagination: true,
            autoplay: false
        }).mount();
    });


    /*=============================================================
                            detail
    ==============================================================*/
    if (document.querySelector('.gallery-main')) {
        var mainSlider = new Splide('.gallery-main', {
            type: 'slide',
            rewind: true,
            pagination: false,
            arrows: true,
        });
        
        // Initialisation des miniatures
        var thumbnailSlider = new Splide('.gallery-thumbnails', {
            fixedWidth: 100,
            fixedHeight: 60,
            gap: 10,
            rewind: true,
            pagination: false,
            isNavigation: true,
            breakpoints: {
                600: {
                    fixedWidth: 60,
                    fixedHeight: 44,
                },
            },
        });
        
        // Synchronisation des sliders
        mainSlider.sync(thumbnailSlider);
        mainSlider.mount();
        thumbnailSlider.mount();
        // Initialiser le carrousel des vidéos Facebook avec une classe unique
        new Splide('.facebook-videos-slider', {
            type: 'slide', // ou 'slide' selon vos besoins
            perPage: 1, // Nombre de vidéos visibles à la fois
            perMove: 1,
            gap: '1rem', // Espace entre les slides
            arrows: true,
            pagination: true,
            // Ajoutez d'autres options selon vos besoins
        }).mount();
    }
}
function initializesAllScripts() {
    console.log('Initialisations des scripts..');
    initSliders();
}
document.addEventListener('turbo:load', initializesAllScripts);
document.addEventListener('turbo:render', initializesAllScripts);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializesAllScripts);
} else {
    initializesAllScripts();
}