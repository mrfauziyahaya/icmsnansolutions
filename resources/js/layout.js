import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('layout', () => ({
        isSidebarOpen: false,
        isUserMenuOpen: false,

        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
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