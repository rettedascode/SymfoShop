// Theme Toggle Functionality
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'dark'; // Default to dark
        this.init();
    }

    init() {
        // Set initial theme
        this.setTheme(this.theme);
        
        // Add event listener to toggle button
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('change', (e) => {
                this.theme = e.target.checked ? 'dark' : 'light';
                this.setTheme(this.theme);
                localStorage.setItem('theme', this.theme);
            });
            
            // Set initial state of toggle
            toggleBtn.checked = this.theme === 'dark';
        }
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', theme === 'dark' ? '#1a1a1a' : '#ffffff');
        }
        
        // Update toggle button state
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.checked = theme === 'dark';
        }
    }

    getCurrentTheme() {
        return this.theme;
    }

    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        this.setTheme(this.theme);
        localStorage.setItem('theme', this.theme);
        
        // Update toggle button
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.checked = this.theme === 'dark';
        }
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Add smooth transitions for theme changes
document.addEventListener('DOMContentLoaded', () => {
    // Add transition class after initial load to enable smooth transitions
    setTimeout(() => {
        document.body.classList.add('theme-transitions');
    }, 100);
}); 