<template>
    <div class="bg-white dark:bg-gray-900">
        <!-- HERO SECTION -->
        <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24 md:py-36">
                <div class="max-w-3xl">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-primary-500/20 border border-primary-500/30 rounded-full px-4 py-1.5 mb-8">
                        <span class="w-2 h-2 rounded-full bg-primary-400 animate-pulse"></span>
                        <span class="text-sm font-medium text-primary-300">Early access — limited to 100 founding hosts</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold leading-tight mb-6">
                        List first.<br />
                        <span class="text-primary-400">Earn from day one.</span>
                    </h1>

                    <p class="text-xl text-gray-300 mb-4 max-w-2xl leading-relaxed">
                        Acomody is launching soon. Founding hosts get
                        <strong class="text-white">0% commission for the entire first year</strong>
                        and guaranteed top placement when we go live.
                    </p>

                    <p class="text-sm font-medium text-primary-300 mb-10">
                        Be among the first 100 hosts on the platform.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <button
                            class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold bg-primary-500 hover:bg-primary-400 text-white rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40"
                            @click="handleCta"
                        >
                            List your property
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">No credit card required. Listing takes under 5 minutes.</p>
                </div>
            </div>

            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none select-none" aria-hidden="true">
                <div class="absolute top-16 right-16 w-96 h-96 bg-primary-500 rounded-full blur-3xl"></div>
                <div class="absolute bottom-8 right-48 w-64 h-64 bg-primary-400 rounded-full blur-2xl"></div>
            </div>
        </section>

        <!-- URGENCY / SOCIAL COUNT BAR -->
        <section class="bg-primary-50 dark:bg-gray-800 border-y border-primary-100 dark:border-gray-700 py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
                    <!-- Joined count -->
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div
                                v-for="i in 5"
                                :key="i"
                                class="w-8 h-8 rounded-full bg-primary-200 dark:bg-primary-800 border-2 border-white dark:border-gray-800 flex items-center justify-center"
                            >
                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="font-bold text-primary-600 dark:text-primary-400">23 hosts</span> already joined
                        </p>
                    </div>

                    <!-- Progress bar -->
                    <div class="flex items-center gap-4 w-full sm:w-auto sm:min-w-72">
                        <div class="flex-1">
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1.5">
                                <span>Founding spots claimed</span>
                                <span class="font-semibold text-primary-600 dark:text-primary-400">23 / 100</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: 23%"></div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">77 left</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- WHAT IS ACOMODY -->
        <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">What is Acomody?</span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3 mb-6">
                        A smarter way to rent out your property.
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        Acomody is a property rental platform built for hosts who want more control and fewer fees. List your apartment, house, villa, cabin — any type of accommodation — and connect directly with guests looking to book short or long stays.
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        Unlike legacy platforms, Acomody keeps things simple: you set your own prices, choose your own rules, and communicate directly with guests before confirming a booking. No surprises.
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        We're currently in pre-launch — building the platform alongside our first hosts to make sure it works the way you actually need it to.
                    </p>

                    <!-- iCal sync highlight -->
                    <div class="flex items-start gap-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 rounded-xl p-4">
                        <div class="text-2xl shrink-0">🔄</div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Already listing on Airbnb or Booking.com?</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Acomody supports two-way iCal calendar sync. Add your existing calendars and your availability stays in sync across all platforms — no double bookings, no manual work.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        v-for="stat in platformStats"
                        :key="stat.label"
                        class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700"
                    >
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mb-1">{{ stat.value }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- BENEFITS SECTION -->
        <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14">
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Founding host perks</span>
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3">Why list now?</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-3 max-w-xl mx-auto">
                    These perks are exclusive to our first 100 hosts. Once the spots are gone, they're gone.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="benefit in benefits"
                    :key="benefit.title"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-7 border border-gray-100 dark:border-gray-700 hover:shadow-lg dark:hover:shadow-gray-900/50 transition-shadow"
                >
                    <div class="text-3xl mb-4">{{ benefit.icon }}</div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ benefit.title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ benefit.desc }}</p>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="py-20 bg-gray-50 dark:bg-gray-800/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-14">
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Simple process</span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3">From listing to launch in 3 steps</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div
                        v-for="(step, i) in steps"
                        :key="i"
                        class="text-center"
                    >
                        <div class="w-20 h-20 mx-auto bg-primary-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mb-6 shadow-lg shadow-primary-600/25">
                            {{ i + 1 }}
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs mx-auto leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SOCIAL PROOF -->
        <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Early hosts love it</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-3">From our founding host community</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="testimonial in testimonials"
                    :key="testimonial.name"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-7 border border-gray-100 dark:border-gray-700 flex flex-col"
                >
                    <!-- Stars -->
                    <div class="flex gap-1 mb-5">
                        <svg
                            v-for="n in 5"
                            :key="n"
                            class="w-4 h-4 text-amber-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed italic flex-1 mb-6">"{{ testimonial.quote }}"</p>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-bold text-primary-600 dark:text-primary-400 shrink-0">
                            {{ testimonial.name[0] }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ testimonial.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ testimonial.location }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA SECTION -->
        <section class="py-24 bg-gradient-to-br from-gray-900 to-primary-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
                <span class="inline-block text-xs font-semibold text-primary-300 uppercase tracking-widest mb-5">Limited time offer</span>
                <h2 class="text-5xl font-bold text-white mb-6">Become a founding host.</h2>
                <p class="text-xl text-gray-300 mb-4 max-w-2xl mx-auto leading-relaxed">
                    0% commission. Top placement. Direct access to our team.
                </p>
                <p class="text-base text-primary-300 font-medium mb-10">
                    Only <strong class="text-white">77 spots</strong> remaining out of 100.
                </p>
                <button
                    class="inline-flex items-center gap-2 px-10 py-5 text-lg font-semibold bg-primary-500 hover:bg-primary-400 text-white rounded-xl transition-all shadow-xl shadow-primary-500/30 hover:shadow-primary-500/50"
                    @click="handleCta"
                >
                    Claim your founding spot
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
                <p class="mt-4 text-sm text-gray-500">No credit card required. Takes under 5 minutes.</p>
            </div>
        </section>

        <!-- MINIMAL FOOTER -->
        <footer class="bg-gray-900 border-t border-gray-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <img src="/images/acomody.png" alt="Acomody" class="h-7 brightness-0 invert" />
                        <span class="text-gray-500 text-sm">— Platform in early access phase</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-gray-400">
                        <router-link :to="{ name: 'page-terms' }" class="hover:text-white transition-colors">Terms</router-link>
                        <router-link :to="{ name: 'page-privacy-policy' }" class="hover:text-white transition-colors">Privacy</router-link>
                        <a href="mailto:hello@acomody.com" class="hover:text-white transition-colors">hello@acomody.com</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ColdStartLanding',

    data() {
        return {
            platformStats: [
                { value: 'Any type', label: 'Apartments, houses, villas, cabins and more' },
                { value: '0%', label: 'Commission for founding hosts in year one' },
                { value: '5 min', label: 'Average time to create a listing' },
                { value: '100%', label: 'Control over your prices, rules and guests' },
            ],
            benefits: [
                {
                    icon: '🎯',
                    title: '0% commission — entire first year',
                    desc: 'Founding hosts keep 100% of every booking. No platform fees until year two. That\'s thousands saved from day one.',
                },
                {
                    icon: '🚀',
                    title: 'Top placement at launch',
                    desc: 'Your property gets priority position in search results when we open to guests. First in line, every time.',
                },
                {
                    icon: '📅',
                    title: 'First bookings, guaranteed',
                    desc: 'We personally promote founding host properties to our pre-launch guest waitlist of thousands of travelers.',
                },
                {
                    icon: '⚡',
                    title: 'List in under 5 minutes',
                    desc: 'Our streamlined creation flow gets your property live without the complexity of other platforms.',
                },
                {
                    icon: '🔄',
                    title: 'Sync with Airbnb, Booking & more',
                    desc: 'Connect your existing calendars via iCal. Availability stays in sync automatically — no double bookings, no manual updates.',
                },
                {
                    icon: '💬',
                    title: 'Direct line to our team',
                    desc: 'Founding hosts get a dedicated channel and direct access to our founders. Your feedback shapes the product.',
                },
            ],
            steps: [
                {
                    title: 'Create your listing',
                    desc: 'Add your property details, photos, and pricing in a simple step-by-step flow. Done in minutes.',
                },
                {
                    title: 'We review and prepare',
                    desc: 'Our team personally reviews and optimizes your listing for maximum visibility at launch.',
                },
                {
                    title: 'Start receiving bookings',
                    desc: 'When we launch, your listing goes live first — visible to thousands of pre-registered guests.',
                },
            ],
            testimonials: [
                {
                    name: 'Marija T.',
                    location: 'Belgrade, Serbia',
                    quote: 'Listed my apartment in 4 minutes. The team was responsive and the whole process felt personal. Excited for launch.',
                },
                {
                    name: 'Ivan P.',
                    location: 'Novi Sad, Serbia',
                    quote: 'The 0% commission deal was the reason I joined. Most platforms take 15–20% right away. This is a no-brainer.',
                },
                {
                    name: 'Ana J.',
                    location: 'Split, Croatia',
                    quote: 'Being able to talk directly to the founders was refreshing. They actually listened to my feedback.',
                },
            ],
        };
    },

    computed: {
        ...mapGetters('auth', ['isLoggedIn']),
        ...mapGetters('user', ['hostingCtaStatus']),
    },

    methods: {
        ...mapActions(['openModal']),

        handleCta() {
            if (!this.isLoggedIn) {
                this.openModal({
                    modalName: 'signUpModal',
                    options: { redirectTo: '/hosting/profile?next=listing-create' },
                });
                return;
            }

            if (this.hostingCtaStatus === 'not_host') {
                this.$router.push({ name: 'page-host-profile', query: { next: 'listing-create' } });
            } else if (this.hostingCtaStatus === 'continue_listing') {
                this.$router.push({ name: 'page-listing-create' });
            } else {
                this.$router.push({ name: 'page-hosting-home' });
            }
        },
    },
};
</script>
