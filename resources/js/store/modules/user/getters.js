export const currentUser = (state) => state.currentUser;

export const userDisplayName = (state) => {
  if (!state.currentUser) return null;

  const user = state.currentUser;
  return user.full_name ||
         `${user.first_name || ''} ${user.last_name || ''}`.trim() ||
         user.name ||
         user.username ||
         user.email ||
         'User';
};

export const userInitials = (state) => {
  if (!state.currentUser) return '';

  const user = state.currentUser;
  const firstName = user.first_name || user.name?.split(' ')[0] || '';
  const lastName = user.last_name || user.name?.split(' ')[1] || '';

  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
};

export const isHost = (state) => state.currentUser?.is_host ?? false;

export const hostProfileComplete = (state) => state.currentUser?.host_profile_complete ?? false;

/**
 * Returns the current hosting CTA status:
 * - 'not_host'          → no host_profile yet → show "Become a host" → /become-a-host
 * - 'continue_listing'  → has host_profile + in-progress draft → show "Continue listing"
 * - 'hosting'           → has host_profile, no active draft → show "Hosting"
 */
export const subscription = (state) => state.currentUser?.subscription ?? null;

export const isCommissionFree = (state) => state.currentUser?.subscription?.is_commission_free ?? false;

export const commissionRate = (state) => state.currentUser?.subscription?.commission_rate ?? 12;

export const currentPlan = (state) => state.currentUser?.subscription?.plan_code ?? 'free';


export const hostingCtaStatus = (state) => {
  const user = state.currentUser;
  if (!user || !user.is_host) return 'not_host';
  if (user.has_in_progress_draft) return 'continue_listing';
  return 'hosting';
};
