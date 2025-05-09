document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu Toggle
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuIcon = document.querySelector('.menu-icon');
    const closeIcon = document.querySelector('.close-icon');
    const overlay = document.querySelector('.mobile-menu-overlay');
    const body = document.body;

    if (menuBtn && mobileMenu && menuIcon && closeIcon && overlay) {
        menuBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            const isActive = mobileMenu.classList.toggle('active');
            body.classList.toggle('no-scroll');
            menuIcon.style.display = isActive ? 'none' : 'block';
            closeIcon.style.display = isActive ? 'block' : 'none';
        });

        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                body.classList.remove('no-scroll');
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            });
        });

        overlay.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            body.classList.remove('no-scroll');
            menuIcon.style.display = 'block';
            closeIcon.style.display = 'none';
        });

        mobileMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    } else {
        console.error('One or more navigation elements are missing.');
    }

    // Category Tabs
    const categoryBtns = document.querySelectorAll('.category-btn');
    if (categoryBtns.length > 0) {
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // TODO: Implement menu item filtering based on category
            });
        });
    }

    // Add to Cart Buttons
    const addToCartBtns = document.querySelectorAll('.add-to-cart');
    if (addToCartBtns.length > 0) {
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const foodCard = btn.closest('.food-card');
                const foodName = foodCard.querySelector('.food-title').textContent;
                const foodPrice = foodCard.querySelector('.food-price').textContent;
                alert(`Added ${foodName} (${foodPrice}) to your cart!`);
                // TODO: Update cart count and store selection in a real app
            });
        });
    }
});