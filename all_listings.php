<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Property Listings - Wanderlust</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2e0e6b5b1.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="css/style.css">

  <style>
    .property-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      transition: transform 0.2s;
      background: #fff;
    }
    .property-card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
    }
    .property-card img {
      border-radius: 5px;
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .stars i {
      color: #ffc107;
      margin-right: 2px;
    }
    .empty-stars i {
      color: #ccc;
      margin-right: 2px;
    }
  </style>
</head>
<body>
  <!-- Include Navbar -->
  <div id="navbar"></div>

  <div class="container py-5">
    <h2 class="mb-4 text-center">All Property Listings</h2>

    <div id="loader" class="text-center my-5" style="display:none;">
      <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div class="row" id="propertyList"></div>
  </div>

  <!-- Include Footer -->
  <div id="footer"></div>

  <script src="js/loadComponents.js"></script>
  <script>
    function renderStars(rating) {
      let stars = '';
      const fullStars = Math.floor(rating);
      const halfStar = rating - fullStars >= 0.5;
      const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

      for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fa-solid fa-star"></i>';
      }
      if (halfStar) {
        stars += '<i class="fa-solid fa-star-half-stroke"></i>';
      }
      for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="fa-regular fa-star"></i>';
      }
      return stars;
    }

    function renderEmptyStars() {
      return `
        <span class="empty-stars">
          <i class="fa-regular fa-star"></i>
          <i class="fa-regular fa-star"></i>
          <i class="fa-regular fa-star"></i>
          <i class="fa-regular fa-star"></i>
          <i class="fa-regular fa-star"></i>
        </span>
      `;
    }

    document.addEventListener("DOMContentLoaded", function() {
      const propertyList = document.getElementById('propertyList');
      const loader = document.getElementById('loader');
      loader.style.display = 'block';

      // Fetch all properties (no category filter)
      fetch('http://localhost:8080/wanderlust/fetch_properties.php')
        .then(response => response.json())
        .then(data => {
          loader.style.display = 'none';
          if (!data || data.length === 0 || data.message) {
            propertyList.innerHTML = "<p class='text-center'>No properties found.</p>";
            return;
          }
          propertyList.innerHTML = data.map(prop => `
            <div class="col-md-4">
              <div class="property-card">
                <img src="${prop.image}" alt="${prop.name}">
                <h5 class="mt-2">${prop.name}</h5>
                <p>${prop.description}</p>
                <p><strong>Type:</strong> ${prop.property_type}</p>
                <p><strong>Price:</strong> ₹${prop.price}</p>
                <p><strong>Location:</strong> ${prop.location}</p>
                <p><strong>Host:</strong> ${prop.host_name}</p>
                <p><strong>Rating:</strong> 
                   ${prop.reviews > 0 
                    ? `<span class="stars">${renderStars(prop.ratings)}</span>` 
                    : renderEmptyStars()}
                </p>

                <div class="d-flex justify-content-between mt-2">
                  <a href="book.html?property_id=${prop.id}" class="btn btn-primary w-50 me-1">Book Now</a>
                  <a href="property_reviews.php?id=${prop.id}" class="btn btn-outline-warning w-50 ms-1">Reviews</a>
                </div>
              </div>
            </div>
          `).join('');
        })
        .catch(err => {
          loader.style.display = 'none';
          console.error(err);
          propertyList.innerHTML = "<p class='text-center text-danger'>Error fetching properties.</p>";
        });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
