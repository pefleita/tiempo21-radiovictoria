/**
 * Tiempo21 - Main JS
 * - Menú móvil
 * - Smooth scroll (condicional)
 * - Back to top button
 * - Search overlay
 * - Audio player (radio en vivo)
 */

(function () {
  'use strict';

  const t21Settings = window.t21Settings || {};
  const t21SmoothScroll = t21Settings.smoothScroll !== false;

  /* ─── Menú Móvil ─────────────────────────────────────────────── */
  const navToggle = document.getElementById('nav-toggle');
  const primaryNav = document.getElementById('primary-nav');

  if (navToggle && primaryNav) {
    navToggle.addEventListener('click', function () {
      const isOpen = primaryNav.classList.toggle('is-open');
      navToggle.classList.toggle('is-active', isOpen);
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function (e) {
      if (!navToggle.contains(e.target) && !primaryNav.contains(e.target)) {
        primaryNav.classList.remove('is-open');
        navToggle.classList.remove('is-active');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ─── Search Overlay ─────────────────────────────────────────── */
  const searchToggle = document.getElementById('search-toggle');
  const searchOverlay = document.getElementById('search-overlay');
  const searchClose = document.getElementById('search-close');

  function openSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.add('is-open');
    const input = searchOverlay.querySelector('input[type="search"]');
    if (input) input.focus();
    document.body.style.overflow = 'hidden';
  }

  function closeSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  if (searchToggle) searchToggle.addEventListener('click', openSearch);
  if (searchClose) searchClose.addEventListener('click', closeSearch);

  if (searchOverlay) {
    searchOverlay.addEventListener('click', function (e) {
      if (e.target === searchOverlay) closeSearch();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSearch();
  });

  /* ─── Audio Player ───────────────────────────────────────────── */
  const audioToggle = document.getElementById('audio-toggle');
  const radioStream = document.getElementById('radio-stream');
  const audioIcon = document.getElementById('audio-icon');
  const audioStatus = document.getElementById('audio-status');

  if (audioToggle && radioStream) {
    audioToggle.addEventListener('click', function () {
      if (radioStream.paused) {
        radioStream.play().then(function () {
          audioIcon.className = 'fa-solid fa-pause';
          if (audioStatus) audioStatus.textContent = 'Reproduciendo...';
        }).catch(function () {
          if (audioStatus) audioStatus.textContent = 'Error al reproducir';
        });
      } else {
        radioStream.pause();
        audioIcon.className = 'fa-solid fa-play';
        if (audioStatus) audioStatus.textContent = 'En vivo';
      }
    });

    radioStream.addEventListener('error', function () {
      if (audioStatus) audioStatus.textContent = 'Sin conexión';
      audioIcon.className = 'fa-solid fa-play';
    });
  }

  /* ─── Smooth Scroll (Condicional) ──────────────────────────────── */
  if (t21SmoothScroll) {
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
      anchor.addEventListener('click', function (e) {
        const targetId = anchor.getAttribute('href');
        if (targetId === '#') return;
        const target = document.querySelector(targetId);
        if (target) {
          e.preventDefault();
          const header = document.getElementById('header-navbar');
          const offset = header ? header.offsetHeight + 8 : 72;
          const top = target.getBoundingClientRect().top + window.scrollY - offset;
          window.scrollTo({ top: top, behavior: 'smooth' });
        }
      });
    });
  }

  /* ─── Back to Top Button ──────────────────────────────────────── */
  const backToTopBtn = document.getElementById('back-to-top');
  if (backToTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }, { passive: true });

    backToTopBtn.addEventListener('click', function () {
      const header = document.getElementById('header-navbar');
      const offset = header ? header.offsetHeight + 8 : 72;
      window.scrollTo({
        top: -offset,
        behavior: t21SmoothScroll ? 'smooth' : 'auto'
      });
    });
  }

  /* ─── Navbar Sticky con Scroll Suave ─────────────────────────── */
  const navbar = document.getElementById('header-navbar');
  if (navbar) {
    let lastScroll = 0;
    const scrollThreshold = 10;

    window.addEventListener('scroll', function () {
      const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
      
      if (currentScroll <= 0) {
        navbar.classList.remove('header-navbar--hidden');
        return;
      }

      if (Math.abs(currentScroll - lastScroll) < scrollThreshold) {
        return;
      }

      if (currentScroll > lastScroll && currentScroll > 100) {
        navbar.classList.add('header-navbar--hidden');
      } else {
        navbar.classList.remove('header-navbar--hidden');
      }

      lastScroll = currentScroll <= 0 ? 0 : currentScroll;
    }, { passive: true });
  }

  /* ─── Lite Embed (YouTube Facade) ──────────────────────────────── */
  document.querySelectorAll('.video-lite-embed').forEach(function (container) {
    container.addEventListener('click', function () {
      const embedUrl = container.dataset.embedUrl;
      const videoId = container.dataset.videoId;
      
      if (!embedUrl) return;

      const iframe = document.createElement('iframe');
      iframe.src = embedUrl + '?autoplay=1&rel=0';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      iframe.title = container.closest('.video-card').querySelector('.video-card__title').textContent;
      
      container.innerHTML = '';
      container.appendChild(iframe);
    });
  });

})();
