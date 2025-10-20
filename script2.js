function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById("moonIcon");
    body.classList.toggle("dark-mode");
    const theme = body.classList.contains("dark-mode") ? "dark" : "light";
    document.cookie = `theme=${theme}; path=/; max-age=31536000`;
    icon.textContent = theme === "dark" ? "☀" : "☾";
}
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = document.cookie.includes('theme=dark') ? 'dark' : 'light';
    const icon = document.getElementById("moonIcon");
    
    if (savedTheme === 'dark') {
        document.body.classList.add("dark-mode");
        icon.textContent = "☀";
    }
});