document.addEventListener('DOMContentLoaded', () => {
  const mainContent = document.getElementById('main-content');

  document.body.addEventListener('click', e => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

    e.preventDefault(); // prevent full page reload
    fetchPage(href);
  });

  async function fetchPage(url) {
    try {
      const res = await fetch(url);
      const html = await res.text();

      // parse returned HTML
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      // replace main content
      const newContent = doc.getElementById('main-content');
      if (newContent) mainContent.innerHTML = newContent.innerHTML;

      // optional: update document title
      document.title = doc.title;

      // reinitialize any page JS
      if (typeof initPage === 'function') initPage();
    } catch (err) {
      console.error('AJAX page load error:', err);
    }
  }
});
