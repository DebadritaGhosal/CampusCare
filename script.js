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
 document.getElementById('personalForm').addEventListener('submit', function(e) {
  e.preventDefault();
  alert('Form submitted!');
});
function logout() {
      localStorage.removeItem("username");
      localStorage.removeItem("password");
      window.location.href = "login.html";
    }