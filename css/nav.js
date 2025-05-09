document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuIcon = document.querySelector('.menu-icon');
    const closeIcon = document.querySelector('.close-icon');
    const overlay = document.querySelector('.mobile-menu-overlay');
    const body = document.body;

    if (!menuBtn || !mobileMenu || !menuIcon || !closeIcon || !overlay) {
        console.error('One or more navigation elements are missing.');
        return;
    }

    menuBtn.addEventListener('click', (event) => {
        event.stopPropagation();

        // Toggle mobile menu and overlay
        const isActive = mobileMenu.classList.toggle('active');
        body.classList.toggle('no-scroll');

        // Toggle icons
        menuIcon.style.display = isActive ? 'none' : 'block';
        closeIcon.style.display = isActive ? 'block' : 'none';
    });

    // Close menu when clicking a link
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            body.classList.remove('no-scroll');
            menuIcon.style.display = 'block';
            closeIcon.style.display = 'none';
        });
    });

    // Close menu when clicking overlay
    overlay.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        body.classList.remove('no-scroll');
        menuIcon.style.display = 'block';
        closeIcon.style.display = 'none';
    });

    // Prevent clicks inside mobile menu from closing it
    mobileMenu.addEventListener('click', (event) => {
        event.stopPropagation();
    });
});