export default async function guest({ to, from, next, store }) {
    if (!store.getters['auth/isAuthInitialized']) {
        try {
            await store.dispatch('auth/initializeAuth');
        } catch (error) {
            console.error('Auth initialization failed:', error);
            return next();
        }
    }

    if (store.getters['auth/isLoggedIn']) {
        return next({
            path: '/dashboard',
            replace: true
        });
    }

    next();
}
