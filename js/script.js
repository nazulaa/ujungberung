// ===== NAVBAR SCROLL =====
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('nav-scrolled', window.scrollY > 60);
});

// ===== MOBILE MENU =====
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
mobileMenuBtn.addEventListener('click', () => {
  mobileMenu.classList.toggle('hidden');
  mobileMenu.classList.toggle('flex');
});
document.querySelectorAll('.mobile-nav-link').forEach(link => {
  link.addEventListener('click', () => {
    mobileMenu.classList.add('hidden');
    mobileMenu.classList.remove('flex');
  });
});

// ===== BACK TO TOP =====
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  if (backToTop) {
    if (window.scrollY > 400) {
      backToTop.style.opacity = '1';
      backToTop.style.pointerEvents = 'auto';
    } else {
      backToTop.style.opacity = '0';
      backToTop.style.pointerEvents = 'none';
    }
  }
});

// ===== VIDEO PLAYER =====
function getYouTubeId(url) {
  const patterns = [
    /(?:youtube\.com\/watch\?v=)([^&\n]+)/,
    /(?:youtu\.be\/)([^?\n]+)/,
    /(?:youtube\.com\/embed\/)([^?\n]+)/,
  ];
  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match) return match[1];
  }
  return null;
}

function loadVideo() {
  const url = document.getElementById('ytUrl').value.trim();
  if (!url) { alert('Masukkan link YouTube terlebih dahulu!'); return; }
  const videoId = getYouTubeId(url);
  if (!videoId) { alert('Format link tidak valid.\nContoh: https://www.youtube.com/watch?v=xxxxxx'); return; }
  document.getElementById('youtubeFrame').src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
  document.getElementById('videoPlaceholder').classList.add('hidden');
  document.getElementById('videoEmbed').classList.remove('hidden');
}

document.getElementById('ytUrl').addEventListener('keydown', e => { if (e.key === 'Enter') loadVideo(); });

document.querySelectorAll('.video-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.video-item').forEach(vi => vi.classList.remove('active', 'bg-white/10', 'border-white/20'));
    item.classList.add('active', 'bg-white/10', 'border-white/20');
    const url = item.dataset.url;
    if (url) { document.getElementById('ytUrl').value = url; loadVideo(); }
  });
});

function addVideoPrompt() {
  const title = prompt('Judul video:');
  if (!title) return;
  const url = prompt('Link YouTube:');
  if (!url) return;
  if (!getYouTubeId(url)) { alert('Link tidak valid!'); return; }
  const list = document.getElementById('videoItems');
  const el = document.createElement('div');
  el.className = 'video-item flex gap-3 items-center p-3 rounded-2xl cursor-pointer hover:bg-white/10 border border-transparent hover:border-white/20 transition-all';
  el.dataset.url = url;
  el.innerHTML = `<div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center text-xl flex-shrink-0">🎥</div><div><div class="text-white/90 text-[13px] font-medium">${title}</div><div class="text-white/45 text-[11px]">Ditambahkan</div></div>`;
  el.addEventListener('click', () => {
    document.querySelectorAll('.video-item').forEach(vi => vi.classList.remove('active','bg-white/10','border-white/20'));
    el.classList.add('active','bg-white/10','border-white/20');
    document.getElementById('ytUrl').value = url;
    loadVideo();
  });
  list.appendChild(el);
}

// ===== BERITA TABS =====
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('active');
      b.classList.add('bg-surface', 'text-on-surface-variant');
    });
    btn.classList.add('active');
    btn.classList.remove('bg-surface', 'text-on-surface-variant');
    const filter = btn.dataset.tab;
    document.querySelectorAll('.berita-card').forEach(card => {
      const show = filter === 'semua' || card.dataset.cat === filter;
      card.style.display = show ? '' : 'none';
    });
  });
});

// ===== TAHUN OTOMATIS =====
const tahunEl = document.getElementById('tahun');
if (tahunEl) tahunEl.textContent = new Date().getFullYear();

// ===== SCROLL REVEAL =====
const reveals = document.querySelectorAll('.rounded-3xl, .rounded-2xl');
const ro = new IntersectionObserver((entries) => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }, i * 40);
      ro.unobserve(entry.target);
    }
  });
}, { threshold: 0.08 });
reveals.forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(16px)';
  el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  ro.observe(el);
});

// ===== CAROUSEL SLIDER =====
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const totalSlides = slides.length;

function showSlide(n) {
  if (n >= totalSlides) currentSlide = 0;
  if (n < 0) currentSlide = totalSlides - 1;
  const offset = -currentSlide * 100;
  document.getElementById('carouselSlides').style.transform = `translateX(${offset}%)`;
  updateIndicators();
}

function nextSlide() {
  currentSlide++;
  showSlide(currentSlide);
}

function prevSlide() {
  currentSlide--;
  showSlide(currentSlide);
}

function goToSlide(n) {
  currentSlide = n;
  showSlide(currentSlide);
}

function updateIndicators() {
  document.querySelectorAll('.indicator').forEach((ind, i) => {
    ind.classList.toggle('active', i === currentSlide);
  });
}

setInterval(nextSlide, 5000);

// ===== AUTOLOAD VIDEO PERTAMA =====
window.addEventListener('load', () => {
  const firstVideoItem = document.querySelector('.video-item.active');
  if (firstVideoItem) {
    const url = firstVideoItem.dataset.url;
    if (url) {
      document.getElementById('ytUrl').value = url;
      loadVideo();
    }
  }
});

// ===== FORCE VIDEO QUALITY TO 720 HD =====
window.addEventListener('load', () => {
  setTimeout(() => {
    const iframe = document.getElementById('youtubeFrame');
    if (iframe && iframe.src) {
      if (!iframe.src.includes('vq=hd720')) {
        iframe.src += '&vq=hd720';
      }
    }
  }, 1000);
});

// ===== SMOOTH SCROLL REVEAL ANIMATIONS =====
const scrollRevealElements = document.querySelectorAll('section, .rounded-3xl, .rounded-2xl, .berita-card, [class*="card"]');

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
    }
  });
}, {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
});

scrollRevealElements.forEach((el) => {
  el.classList.add('scroll-reveal');
  revealObserver.observe(el);
});

// ===== SMOOTH CARD ANIMATIONS =====
document.querySelectorAll('.grid > *').forEach((card, index) => {
  card.style.animationDelay = `${index * 0.1}s`;
});

// ===== PARALLAX EFFECT ON SCROLL =====
window.addEventListener('scroll', () => {
  const parallaxElements = document.querySelectorAll('[class*="hero"], header img');
  parallaxElements.forEach((el) => {
    const scrollPosition = window.scrollY;
    el.style.transform = `translateY(${scrollPosition * 0.5}px)`;
  });
});
