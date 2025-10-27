/**
 * Authentication Middleware
 * Opens login modal if user is not authenticated
 * Keeps user on current page until login is complete
 */
export default function auth({ to, from, next, store, router }) {
    // Check if user is already authenticated
    if (store.getters['auth/isLoggedIn']) {
        return next();
    }

    // Check if token exists
    const token = store.state.token;

    // IMPORTANT: Block navigation completely
    next(false);

    // Open login modal asynchronously (doesn't block the navigation guard)
    setTimeout(() => {
        store.dispatch('openModal', {
            modalName: 'logInModal',
            options: {
                redirectTo: to.fullPath
            }
        }).then(() => {
            router.push(to.fullPath);
        }).catch((error) => {
            console.log('Login cancelled - staying on current page:', from.path);
        });
    }, 0);
}
