import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('layout', () => ({
        isSidebarOpen: false,
        isUserMenuOpen: false,
        isSidebarCollapsed: false,

        init() {
            // Desktop collapse preference survives navigation and reloads.
            this.isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        },

        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },

        toggleSidebarCollapse() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.isSidebarCollapsed);
        },

        toggleUserMenu() {
            this.isUserMenuOpen = !this.isUserMenuOpen;
        },

        closeSidebar() {
            this.isSidebarOpen = false;
        },

        closeUserMenu() {
            this.isUserMenuOpen = false;
        }
    }));
}); 