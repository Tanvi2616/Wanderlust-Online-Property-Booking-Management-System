// js/loadComponents.js

// Function to dynamically load HTML components (like navbar or footer)
async function loadHTML(selector, url) {
  const el = document.querySelector(selector);
  if (!el) return;

  try {
    const res = await fetch(url, { cache: "no-store" }); // avoid cached version
    if (!res.ok) {
      console.error('Failed to load', url, res.status);
      return;
    }
    const html = await res.text();
    el.innerHTML = html;
    return el;
  } catch (e) {
    console.error('Failed to load component', url, e);
  }
}

// Function to update navbar links based on user role
function updateDashboardLink() {
  const role = sessionStorage.getItem('user_role'); // 'customer' or 'owner'
  const userName = sessionStorage.getItem('user_name'); // optional
  const dashboardLink = document.getElementById('dashboardLink');
  const loginLink = document.getElementById('loginLink');

  if (!dashboardLink) return;

  const link = dashboardLink.querySelector('a');
  if (!link) return;

  // Set dashboard redirect based on role
  if (role === 'customer') {
    link.href = 'customer_dashboard.html';
    if (loginLink) loginLink.style.display = 'none';
  } else if (role === 'owner') {
    link.href = 'owner_dashboard.html';
    if (loginLink) loginLink.style.display = 'none';
  } else {
    link.href = 'login.html';
    if (loginLink) loginLink.style.display = 'block';
  }

  // Optional: show user name on dashboard link
  if (userName && role) {
    link.innerText = `${userName} Dashboard`;
  } else {
    link.innerText = 'Dashboard';
  }
}





// Load navbar and footer, then update based on login role
document.addEventListener('DOMContentLoaded', async () => {
  await loadHTML('#navbar', 'navbar.html'); // load navbar first
  updateDashboardLink(); // update after navbar is loaded

  await loadHTML('#footer', 'footer.html'); // load footer
});
