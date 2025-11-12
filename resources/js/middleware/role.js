/**
 * Role-based Authorization Middleware
 * Checks if authenticated user has required role(s)
 *
 * Usage in route:
 * meta: {
 *   middleware: [auth, role],
 *   roles: ['admin', 'manager'] // User must have one of these roles
 * }
 */
export default function role({ to, from, next, store }) {
    const requiredRoles = to.meta.roles;

    if (!requiredRoles || requiredRoles.length === 0) {
        // No roles specified, allow access
        return next();
    }

    const user = store.state.currentUser;

    if (!user) {
        // User not authenticated (should be caught by auth middleware)
        return next({
            path: '/login',
            query: { redirect: to.fullPath }
        });
    }

    // Check if user has any of the required roles
    const userRole = user.role || user.user_role;
    const userRoles = Array.isArray(user.roles) ? user.roles : [userRole];

    const hasRequiredRole = requiredRoles.some(role =>
        userRoles.includes(role)
    );

    if (!hasRequiredRole) {
        // User doesn't have required role
        return next({
            path: '/unauthorized',
            replace: true
        });
    }

    // User has required role
    next();
}
