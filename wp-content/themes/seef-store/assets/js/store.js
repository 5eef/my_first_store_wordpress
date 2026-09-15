(() => {
  'use strict';

  const root = document.documentElement;
  const menuButton = document.querySelector('.seef-menu-toggle');
  const navigation = document.querySelector('.seef-primary-nav');
  const searchButton = document.querySelector('.seef-search-toggle');
  const searchPanel = document.querySelector('.seef-header-search');
  const themeButton = document.querySelector('.seef-theme-toggle');
  const siteHeader = document.querySelector('[data-site-header]');

  const setMenu = (open, restoreFocus = false) => {
    if (!menuButton || !navigation) return;
    menuButton.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    const label = menuButton.querySelector('.screen-reader-text');
    if (label) label.textContent = open ? seefStore.menuClose : seefStore.menuOpen;
    if (!open && restoreFocus) menuButton.focus();
  };

  const setSearch = (open, restoreFocus = false) => {
    if (!searchButton || !searchPanel) return;
    searchPanel.hidden = !open;
    searchButton.setAttribute('aria-expanded', String(open));
    if (open) {
      setMenu(false);
      searchPanel.querySelector('input[type="search"]')?.focus();
    } else if (restoreFocus) {
      searchButton.focus();
    }
  };

  menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') !== 'true';
    if (open) setSearch(false);
    setMenu(open);
  });
  navigation?.addEventListener('click', (event) => {
    if (event.target instanceof HTMLAnchorElement) setMenu(false);
  });

  searchButton?.addEventListener('click', () => {
    setSearch(Boolean(searchPanel?.hidden));
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
      const menuWasOpen = menuButton?.getAttribute('aria-expanded') === 'true';
      const searchWasOpen = searchPanel ? !searchPanel.hidden : false;
      setMenu(false, menuWasOpen);
      setSearch(false, searchWasOpen);
    }
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node) || siteHeader?.contains(event.target)) return;
    setMenu(false);
    setSearch(false);
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
