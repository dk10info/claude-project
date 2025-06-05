import './bootstrap';

// Dark mode initialization
document.addEventListener('DOMContentLoaded', function() {
    // Check if dark mode cookie exists and apply it
    const darkModeCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('darkMode='));
    
    if (darkModeCookie && darkModeCookie.split('=')[1] === 'true') {
        document.documentElement.classList.add('dark');
    }
});
