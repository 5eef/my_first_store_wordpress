(() => {
  'use strict';

  const root = document.documentElement;
  const menuButton = document.querySelector('.seef-menu-toggle');
  const navigation = document.querySelector('.seef-primary-nav');
  const searchButton = document.querySelector('.seef-search-toggle');
  const searchPanel = document.querySelector('.seef-header-search');
  const themeButton = document.querySelector('.seef-theme-toggle');

  const setMenu = (open) => {
    if (!menuButton || !navigation) return;
    menuButton.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    const label = menuButton.querySelector('.screen-reader-text');
    if (label) label.textContent = open ? seefStore.menuClose : seefStore.menuOpen;
  };

  menuButton?.addEventListener('click', () => setMenu(menuButton.getAttribute('aria-expanded') !== 'true'));
  navigation?.addEventListener('click', (event) => {
    if (event.target instanceof HTMLAnchorElement) setMenu(false);
  });

  searchButton?.addEventListener('click', () => {
    if (!searchPanel) return;
    const open = searchPanel.hidden;
    searchPanel.hidden = !open;
    searchButton.setAttribute('aria-expanded', String(open));
    if (open) searchPanel.querySelector('input[type="search"]')?.focus();
  });

  themeButton?.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('seef-theme', next); } catch (error) { /* Storage can be unavailable. */ }
    themeButton.setAttribute('aria-label', next === 'dark' ? seefStore.themeLight : seefStore.themeDark);
    themeButton.setAttribute('aria-pressed', String(next === 'dark'));
  });

  if (themeButton) {
    themeButton.setAttribute('aria-pressed', String(root.dataset.theme === 'dark'));
    themeButton.setAttribute('aria-label', root.dataset.theme === 'dark' ? seefStore.themeLight : seefStore.themeDark);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenu(false);
      if (searchPanel && !searchPanel.hidden) {
        searchPanel.hidden = true;
        searchButton?.setAttribute('aria-expanded', 'false');
      }
    }
  });

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const reveals = document.querySelectorAll('[data-reveal]');
  if (reduceMotion || !('IntersectionObserver' in window)) {
    reveals.forEach((item) => item.classList.add('is-visible'));
  } else {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach((item) => observer.observe(item));
  }
})();
