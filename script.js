const welcomeScreen = document.getElementById('welcome-screen');
const discoverBtn = document.getElementById('discover-btn');
const mainSite = document.getElementById('main-site');
const mobileMenu = document.getElementById('mobile-menu');
const navMenu = document.getElementById('nav-menu');

if (welcomeScreen && discoverBtn && mainSite) {
    discoverBtn.addEventListener('click', (e) => {
        e.preventDefault();
        welcomeScreen.classList.add('fade-out');
        mainSite.classList.add('show-content');

        const esMovil = window.getComputedStyle(mobileMenu).display !== 'none';
        if (esMovil) {
            setTimeout(() => {
                mobileMenu.classList.add('active');
                navMenu.classList.add('active');
            }, 400);
        }
        window.scrollTo({ top: 0 });
    });
}

if (mobileMenu && navMenu) {
    mobileMenu.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
}

const carouselInner = document.querySelector('.carousel-inner');
const carouselSlides = document.querySelectorAll('.carousel-item');
const nextBtn = document.querySelector('.btn-next');
const prevBtn = document.querySelector('.btn-prev');

let currentSlideIndex = 0;

function updateCarouselPosition() {
    if (carouselInner) {
        carouselInner.style.transform = `translateX(-${currentSlideIndex * 100}%)`;
    }
}

if (carouselInner && carouselSlides.length > 0) {
    if (nextBtn && prevBtn) {
        nextBtn.addEventListener('click', () => {
            currentSlideIndex = (currentSlideIndex + 1) % carouselSlides.length;
            updateCarouselPosition();
        });
        prevBtn.addEventListener('click', () => {
            currentSlideIndex = (currentSlideIndex - 1 + carouselSlides.length) % carouselSlides.length;
            updateCarouselPosition();
        });
    }

    setInterval(() => {
        currentSlideIndex = (currentSlideIndex + 1) % carouselSlides.length;
        updateCarouselPosition();
    }, 1000);
}

const lightbox = document.getElementById('lightbox-pantalla');
const lightboxImg = document.getElementById('lightbox-imagen-gigante');
const imagenesCatalogo = document.querySelectorAll('.catalog-card img, .portfolio-card, .producto-img-wrapper img');

if (lightbox && lightboxImg && imagenesCatalogo.length > 0) {
    imagenesCatalogo.forEach(imagen => {
        imagen.addEventListener('click', () => {
            lightboxImg.src = imagen.src;
            lightbox.style.display = 'flex'; 
            document.body.style.overflow = 'hidden'; 
        });
    });

    lightbox.addEventListener('click', () => {
        lightbox.style.display = 'none'; 
        document.body.style.overflow = 'auto';
    });
}

const tiendaSite = document.getElementById('tienda-site');
const selectorButtons = document.querySelectorAll('.selector-btn');

selectorButtons.forEach(button => {
    button.addEventListener('click', () => {
        selectorButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        const target = button.getAttribute('data-target');

        if (target === 'barberia') {
            document.body.classList.remove('tienda-activa');
            if (tiendaSite) {
                tiendaSite.classList.remove('show-content');
                tiendaSite.style.display = 'none';
            }
            if (mainSite) {
                mainSite.style.display = 'block';
                setTimeout(() => mainSite.classList.add('show-content'), 10);
            }
        } else if (target === 'tienda') {
           
            document.body.classList.add('tienda-activa');
            if (mainSite) {
                mainSite.classList.remove('show-content');
                mainSite.style.display = 'none';
            }
            if (tiendaSite) {
                tiendaSite.style.display = 'block';
                setTimeout(() => tiendaSite.classList.add('show-content'), 10);
            }
        }
    });
});