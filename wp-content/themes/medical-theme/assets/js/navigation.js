/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens.
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const header = document.getElementById('masthead');
        if (!header) return;

        const button = header.querySelector('.menu-toggle');
        const menu = document.getElementById('site-navigation');
        const body = document.body;

        if (!button || !menu) return;

        function toggleMenu() {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            const iconSpan = button.querySelector('.material-symbols-outlined');

            button.setAttribute('aria-expanded', !isExpanded);
            menu.classList.toggle('is-active');
            body.classList.toggle('menu-open');

            if (iconSpan) {
                iconSpan.textContent = isExpanded ? 'menu' : 'close';
            }

            // For older CSS if needed
            header.classList.toggle('toggled');
        }

        button.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleMenu();
        });

        // Close menu when clicking on a link
        const menuLinks = menu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (menu.classList.contains('is-active')) {
                    toggleMenu();
                }
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const isClickInsideMenu = menu.contains(event.target);
            const isClickOnButton = button.contains(event.target);

            if (!isClickInsideMenu && !isClickOnButton && menu.classList.contains('is-active')) {
                toggleMenu();
            }
        });
    });
})();
