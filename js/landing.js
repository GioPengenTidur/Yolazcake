/* ==================== LANDING PAGE JAVASCRIPT ==================== */
/* Converted from Next.js/React to vanilla JavaScript */

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
  initScrollAnimations();
  initStatisticsCounter();
  initTestimonialsCarousel();
  initGalleryLightbox();
  initSmoothScroll();
});

// ==================== SCROLL ANIMATIONS ====================
function initScrollAnimations() {
  const animatedElements = document.querySelectorAll('.fade-up, .fade-in-left, .fade-in-right');
  
  const observerOptions = {
    threshold: 0.15,
    rootMargin: '-50px 0px'
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        // Don't unobserve - keep animation triggers for one-time effect
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  animatedElements.forEach(el => {
    observer.observe(el);
  });
}

// ==================== STATISTICS COUNTER ====================
function initStatisticsCounter() {
  const statValues = document.querySelectorAll('.stat-value');
  
  const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px'
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-target'));
        animateCounter(el, target);
        observer.unobserve(el);
      }
    });
  }, observerOptions);
  
  statValues.forEach(el => {
    observer.observe(el);
  });
}

function animateCounter(element, target) {
  const duration = 2000;
  const steps = 60;
  const increment = target / steps;
  let current = 0;
  
  const timer = setInterval(() => {
    current += increment;
    if (current >= target) {
      element.textContent = target.toLocaleString();
      clearInterval(timer);
    } else {
      element.textContent = Math.floor(current).toLocaleString();
    }
  }, duration / steps);
}

// ==================== TESTIMONIALS CAROUSEL ====================
let currentTestimonial = 0;
const totalTestimonials = 4;

function initTestimonialsCarousel() {
  const dots = document.querySelectorAll('.testimonials-dots .dot');
  
  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      goToTestimonial(index);
    });
  });
  
  // Auto-advance every 5 seconds
  setInterval(() => {
    nextTestimonial();
  }, 5000);
}

function goToTestimonial(index) {
  const cards = document.querySelectorAll('.testimonial-card');
  const dots = document.querySelectorAll('.testimonials-dots .dot');
  
  // Remove active class from all
  cards.forEach(card => {
    card.classList.remove('active');
  });
  dots.forEach(dot => {
    dot.classList.remove('active');
  });
  
  // Add active class to current
  cards[index].classList.add('active');
  dots[index].classList.add('active');
  
  currentTestimonial = index;
}

function nextTestimonial() {
  const next = (currentTestimonial + 1) % totalTestimonials;
  goToTestimonial(next);
}

function prevTestimonial() {
  const prev = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials;
  goToTestimonial(prev);
}

// ==================== GALLERY LIGHTBOX ====================
const galleryImages = [
  {
    src: "https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=1200&h=1200&fit=crop",
    alt: "Cafe interior with warm lighting"
  },
  {
    src: "https://images.unsplash.com/photo-1486427944544-d2c6e14c8fe1?w=1200&h=1200&fit=crop",
    alt: "Artisan coffee preparation"
  },
  {
    src: "https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1200&h=1200&fit=crop",
    alt: "Delicious pastries display"
  },
  {
    src: "https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=1200&h=1200&fit=crop",
    alt: "Signature layered cake"
  },
  {
    src: "https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=1200&h=1200&fit=crop",
    alt: "Latte art"
  },
  {
    src: "https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=1200&h=1200&fit=crop",
    alt: "Elegant dessert presentation"
  },
  {
    src: "https://images.unsplash.com/photo-1587080413959-06b859fb107d?w=1200&h=1200&fit=crop",
    alt: "Fashion boutique corner"
  }
];

function initGalleryLightbox() {
  const lightbox = document.getElementById('lightbox');
  
  // Close on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeLightbox();
    }
  });
}

function openLightbox(index) {
  const lightbox = document.getElementById('lightbox');
  const lightboxImage = document.getElementById('lightbox-image');
  
  lightboxImage.src = galleryImages[index].src;
  lightboxImage.alt = galleryImages[index].alt;
  
  lightbox.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  const lightbox = document.getElementById('lightbox');
  lightbox.classList.remove('active');
  document.body.style.overflow = '';
}

// ==================== SMOOTH SCROLL ====================
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
}

// ==================== SCROLL TO TOP ====================
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

// ==================== PARALLAX EFFECT (Optional) ====================
// Add parallax scrolling effect to hero section
// Slower fade - requires more scrolling before buttons disappear
window.addEventListener('scroll', function() {
  const scrolled = window.pageYOffset;
  const hero = document.querySelector('.hero-content-wrapper');
  
  if (hero && scrolled < window.innerHeight) {
    // Delay fade until user scrolls at least 200px, then fade slowly
    const fadeStart = 200; // Start fading after 200px scroll
    const fadeDistance = window.innerHeight * 0.8; // Fade over 80% of viewport height
    
    let opacity = 1;
    if (scrolled > fadeStart) {
      opacity = 1 - ((scrolled - fadeStart) / fadeDistance);
    }
    
    const scale = 1 - (scrolled / (window.innerHeight * 3));
    const translateY = scrolled * 0.2;
    
    hero.style.opacity = Math.max(0, opacity);
    hero.style.transform = `translateY(${translateY}px) scale(${Math.max(0.95, scale)})`;
  }
});

// ==================== NAVBAR SCROLL EFFECT ====================
// Make navbar more opaque on scroll
let lastScroll = 0;

window.addEventListener('scroll', function() {
  const nav = document.querySelector('nav');
  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 100) {
    nav.style.boxShadow = '0 8px 30px rgba(0,0,0,0.15)';
  } else {
    nav.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
  }
  
  lastScroll = currentScroll;
});

// ==================== PRODUCT CARD HOVER EFFECTS ====================
document.querySelectorAll('.product-card').forEach(card => {
  card.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-8px)';
  });
  
  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
  });
});

// ==================== COFFEE & BAKERY ITEM HOVER ====================
document.querySelectorAll('.cb-item').forEach(item => {
  item.addEventListener('mouseenter', function() {
    const name = this.querySelector('.cb-item-name');
    if (name) {
      name.style.color = '#C9A961';
    }
  });
  
  item.addEventListener('mouseleave', function() {
    const name = this.querySelector('.cb-item-name');
    if (name) {
      name.style.color = '';
    }
  });
});

// ==================== FASHION ITEM HOVER ====================
document.querySelectorAll('.fashion-item').forEach(item => {
  item.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-8px)';
  });
  
  item.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
  });
});

// ==================== BUTTON HOVER EFFECTS ====================
document.querySelectorAll('.btn-primary, .btn-secondary, .btn-outline').forEach(btn => {
  btn.addEventListener('mouseenter', function() {
    this.style.transform = 'scale(1.03)';
  });
  
  btn.addEventListener('mouseleave', function() {
    this.style.transform = 'scale(1)';
  });
  
  btn.addEventListener('mousedown', function() {
    this.style.transform = 'scale(0.97)';
  });
  
  btn.addEventListener('mouseup', function() {
    this.style.transform = 'scale(1.03)';
  });
});

// ==================== SOCIAL BUTTON HOVER ====================
document.querySelectorAll('.social-btn, .footer-social-btn').forEach(btn => {
  btn.addEventListener('mouseenter', function() {
    this.style.transform = 'scale(1.1) translateY(-2px)';
  });
  
  btn.addEventListener('mouseleave', function() {
    this.style.transform = 'scale(1) translateY(0)';
  });
});

// ==================== NAV BUTTON HOVER ====================
document.querySelectorAll('.nav-btn').forEach(btn => {
  btn.addEventListener('mouseenter', function() {
    this.style.transform = 'scale(1.1)';
  });
  
  btn.addEventListener('mouseleave', function() {
    this.style.transform = 'scale(1)';
  });
});

// ==================== QUICK ADD BUTTON ====================
document.querySelectorAll('.quick-add').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    // Add to cart animation
    this.style.transform = 'scale(0.9)';
    setTimeout(() => {
      this.style.transform = 'scale(1)';
    }, 150);
    
    // You can add actual cart functionality here
    console.log('Added to cart!');
  });
});

// ==================== IMAGE LAZY LOADING ====================
// Add lazy loading to images
document.querySelectorAll('img').forEach(img => {
  if (!img.hasAttribute('loading')) {
    img.setAttribute('loading', 'lazy');
  }
});

// ==================== SPARKLE ANIMATION ====================
const sparkles = document.querySelectorAll('.sparkle-icon');
sparkles.forEach(sparkle => {
  sparkle.style.animation = 'sparkleRotate 4s linear infinite';
});

console.log('YOLAZCAKE Landing Page Initialized!');
