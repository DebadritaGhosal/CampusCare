function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById("moonIcon");
    body.classList.toggle("dark-mode");
  
    // Save theme to localStorage
    const theme = body.classList.contains("dark-mode") ? "dark" : "light";
    localStorage.setItem("theme", theme);
  
    // Toggle icon
    icon.textContent = theme === "dark" ? "☀︎" : "☾";
  }
document.getElementById('profileLink').addEventListener('click', function() {
});
  // Load theme on page load
  window.onload = () => {
    const savedTheme = localStorage.getItem("theme");
    const icon = document.getElementById("moonIcon");
  
    if (savedTheme === "dark") {
      document.body.classList.add("dark-mode");
      icon.textContent = "☀︎";
    } else {
      icon.textContent = "☾";
    }
  };
window.onload = function() {
  const userEmail = localStorage.getItem('userEmail');
  if (userEmail) {
    document.getElementById('profileEmail').textContent = userEmail;
  }
};  
  // listings
  const imageContainerElement = document.querySelector('.imageContainer');
  const btn = document.querySelector('.btn');
  
  function loadMoreCards() {
    const rand = Math.floor(Math.random() * 6) + 1;
    const cardToClone = document.getElementById(`set${rand}`);
    
    if (cardToClone) {
      const clone = cardToClone.cloneNode(true);
      imageContainerElement.appendChild(clone);
    }
  }
  
  
  btn.addEventListener('click', () => {
      for(let i = 0; i < 6; i++)
      {
          loadMoreCards();
      }
  })
  
  // filtering
  const listings = [
    {
      id: 1,
      title: "Advanced Calculus Textbook",
      price: 500,
      image: "list1.png",
      location: "North Block",
      condition: "Like New",
      seller: "Alex Chen",
      category: "books"
    },
    {
      id: 2,
      title: "MacBook Pro 13\" (2020)",
      price: 20000,
      image: "list2.png",
      location: "South Block",
      condition: "Excellent",
      seller: "Sarah Kim",
      category: "electronics"
    },
    {
      id: 3,
      title: "Study Desk & Chair Set",
      price: 3500,
      image: "list3.png",
      location: "East Block",
      condition: "Good",
      seller: "Mike Johnson",
      category: "furniture"
    },
    {
      id: 4,
      title: "Organic Chemistry Lab Kit",
      price: 900,
      image: "list4.png",
      location: "West Block",
      condition: "Good",
      seller: "Emma Davis",
      category: "Lab Requirements"
    },
    {
      id: 5,
      title: "Gaming Headset",
      price: 5000,
      image: "list5.png",
      location: "North Block",
      condition: "Very Good",
      seller: "Ryan Lee",
      category: "electronics"
    },
    {
      id: 6,
      title: "Mini Refrigerator",
      price: 15000,
      image: "list6.png",
      location: "South Block",
      condition: "Like New",
      seller: "Lisa Wang",
      category: "electronics"
    }
  ];
  
  function renderListings(data) {
    const container = document.querySelector('.imageContainer');
    container.innerHTML = '';
  
    data.forEach(item => {
      const card = document.createElement('div');
      card.className = 'list_cotainer';
      card.innerHTML = `
        <div class="topimg">
          <img src="${item.image}">
        </div>
        <div class="content">
          <div class="parting">
            <h2>${item.title}</h2>
            <h3>Rs.${item.price}/-</h3>
          </div>
          <p>${item.title} available in ${item.location}</p>
          <div class="parting">
            <p><a href="#">⚲ ${item.location}</a></p>
            <h4>${item.condition}</h4>
          </div>
          <div class="parting1">
            <h5>Seller:</h5>
            <h6>${item.seller}</h6>
          </div>
          <button>🗨Contact Seller</button>
        </div>
      `;
      container.appendChild(card);
    });
  }
  function applyFilters() {
    const keyword = document.getElementById("searchInput").value.toLowerCase();
    const category = document.getElementById("categoryFilter").value;
    const location = document.getElementById("locationFilter").value;
    const price = document.getElementById("priceFilter").value;
  
    const filtered = listings.filter(item => {
      const matchKeyword = item.title.toLowerCase().includes(keyword);
      const matchCategory = !category || item.category === category;
      const matchLocation = !location || item.location.toLowerCase().includes(location);
      const matchPrice =
        !price ||
        (price === 'low' && item.price < 500) ||
        (price === 'medium' && item.price >= 500 && item.price <= 2000) ||
        (price === 'high' && item.price > 2000);
  
      return matchKeyword && matchCategory && matchLocation && matchPrice;
    });
  
    renderListings(filtered);
  }
  // Search as typed
  document.getElementById("searchInput").addEventListener("input", applyFilters);
  
  // Filters change
  document.getElementById("categoryFilter").addEventListener("change", applyFilters);
  document.getElementById("locationFilter").addEventListener("change", applyFilters);
  document.getElementById("priceFilter").addEventListener("change", applyFilters);
  
  // drop list
  function showTab(tabId) {
    const tabs = document.querySelectorAll(".tab-content");
    tabs.forEach(tab => {
      tab.style.display = "none";
    });
  
    document.getElementById(tabId).style.display = "block";
  }