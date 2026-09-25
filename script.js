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
const lightboxVideo = document.getElementById('lightbox-video-gigante');


document.addEventListener('click', (e) => {
   
    const card = e.target.closest('.portfolio-card, .catalog-card');
    
   
    if (!card || !lightbox) return;

    const img = card.querySelector('img');
    const video = card.querySelector('video');

   
    if (img && img.src && img.getAttribute('src').trim() !== '') {
        if (lightboxVideo) {
            lightboxVideo.style.display = 'none';
            lightboxVideo.pause();
            lightboxVideo.src = '';
        }

        if (lightboxImg) {
            lightboxImg.src = img.src;
            lightboxImg.style.display = 'block';
        }

        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } 
  
    else if (video && video.src && video.getAttribute('src').trim() !== '') {
        if (lightboxImg) {
            lightboxImg.style.display = 'none';
            lightboxImg.src = '';
        }

        if (lightboxVideo) {
            lightboxVideo.src = video.src;
            lightboxVideo.style.display = 'block';
            lightboxVideo.play();
        }

        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
});


if (lightbox) {
    lightbox.addEventListener('click', (e) => {
     
        if (lightboxVideo && e.target === lightboxVideo) return;

        lightbox.style.display = 'none';
        if (lightboxImg) lightboxImg.src = '';
        if (lightboxVideo) {
            lightboxVideo.pause();
            lightboxVideo.src = '';
        }
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

function imprimirRecibo() {
    window.print();
}

function copiarCodigoReserva(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
        alert('¡Código de reserva copiado al portapapeles: ' + codigo + '!');
    }).catch(err => {
        console.error('Error al copiar: ', err);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('adminSearchInput');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            // Selecciona todas las filas del tbody de la tabla admin
            const rows = document.querySelectorAll('.admin-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

function copiarCodigoReserva(codigo) {
    const mostrarToast = () => {
        const toast = document.getElementById('toastNotification');
        if (toast) {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }
    };

    // Intenta usar la API moderna de Portapapeles
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(codigo).then(() => {
            mostrarToast();
        }).catch(() => {
            copiarFormaTradicional(codigo, mostrarToast);
        });
    } else {
        // Método de respaldo para HTTP o conexiones por IP
        copiarFormaTradicional(codigo, mostrarToast);
    }
}

function copiarFormaTradicional(texto, callback) {
    const tempInput = document.createElement("input");
    tempInput.value = texto;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Soporte para móviles

    try {
        document.execCommand("copy");
        callback();
    } catch (err) {
        alert("No se pudo copiar automáticamente. Código: " + texto);
    }

    document.body.removeChild(tempInput);
}

document.addEventListener('DOMContentLoaded', () => {
    const videosTarjeta = document.querySelectorAll('.portfolio-card video');
    videosTarjeta.forEach(video => {
        video.play().catch(error => {
            console.log("Autoplay prevenido por el navegador:", error);
        });
    });
});