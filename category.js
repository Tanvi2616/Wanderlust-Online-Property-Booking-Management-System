// js/category.js
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const categoryType = params.get('type') || '';
  const titleEl = document.getElementById('categoryTitle');
  const listEl = document.getElementById('propertyList');
  const loader = document.getElementById('loader');

  function niceTitle(t) {
    if (!t) return 'All Properties';
    return t.charAt(0).toUpperCase() + t.slice(1) + 's';
  }

  titleEl.textContent = niceTitle(categoryType);

  function imagePath(imageValue) {
    if (!imageValue) return 'images/placeholder.jpg';
    // if absolute URL
    try {
      const u = new URL(imageValue);
      return imageValue; // full URL
    } catch (e) {
      // not a full URL — treat as local filename in images/ folder
      return `images/${imageValue}`;
    }
  }

  function showLoader(show = true) {
    loader.style.display = show ? 'block' : 'none';
  }

  async function loadProperties() {
    showLoader(true);
    listEl.innerHTML = '';
    const url = `./fetch_properties.php${categoryType ? '?type=' + encodeURIComponent(categoryType) : ''}`;

    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        listEl.innerHTML = `<div class="col-12 text-center"><p>No properties found in this category.</p></div>`;
        showLoader(false);
        return;
      }

      const html = data.map(p => {
        const img = imagePath(p.image);
        // sanitize minimal fields (you can enhance)
        const name = p.name || 'Untitled';
        const location = p.location || '';
        const price = p.price !== undefined ? Number(p.price).toLocaleString() : 'N/A';
        const rating = p.ratings || '-';
        const reviews = p.reviews || 0;

        return `
          <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
              <img src="${img}" class="card-img-top" alt="${name}" style="object-fit:cover; height:200px;">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${name}</h5>
                <p class="card-text text-muted mb-1">${location}</p>
                <p class="mb-2"><strong>₹${price}</strong> / night</p>
                <div class="mt-auto">
                  <small class="text-warning">${rating} ⭐</small>
                  <small class="text-muted">(${reviews} reviews)</small>
                </div>
              </div>
            </div>
          </div>`;
      }).join('');

      listEl.innerHTML = html;
    } catch (err) {
      console.error('Error loading properties:', err);
      listEl.innerHTML = `<div class="col-12 text-center"><p class="text-danger">Failed to load properties. Check console/network and that fetch_properties.php is reachable.</p></div>`;
    } finally {
      showLoader(false);
    }
  }

  loadProperties();
});
