export default async function guest({ to, from, next, store }) {
    if (!store.getters.isAuthInitialized) {
        try {
            await store.dispatch('initializeAuth');
        } catch (error) {
            console.error('Auth initialization failed:', error);
            return next();
        }
    }

    if (store.getters.isLoggedIn) {
        return next({
            path: '/dashboard',
            replace: true
        });
    }

    next();
}
