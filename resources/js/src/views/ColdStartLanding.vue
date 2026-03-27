<template>
    <div class="bg-white dark:bg-gray-900">
        <!-- HERO SECTION -->
        <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24 md:py-36">
                <div class="max-w-3xl">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-primary-500/20 border border-primary-500/30 rounded-full px-4 py-1.5 mb-8">
                        <span class="w-2 h-2 rounded-full bg-primary-400 animate-pulse"></span>
                        <span class="text-sm font-medium text-primary-300">{{ $t('badge') }}</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold leading-tight mb-6">
                        {{ $t('hero_line1') }}<br />
                        <span class="text-primary-400">{{ $t('hero_line2') }}</span>
                    </h1>

                    <i18n-t keypath="hero_subtitle" tag="p" class="text-xl text-gray-300 mb-4 max-w-2xl leading-relaxed">
                        <template #highlight>
                            <strong class="text-white">{{ $t('hero_highlight') }}</strong>
                        </template>
                    </i18n-t>

                    <p class="text-sm font-medium text-primary-300 mb-10">
                        {{ $t('hero_founding') }}
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <button
                            class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold bg-primary-500 hover:bg-primary-400 text-white rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40"
                            @click="handleCta"
                        >
                            {{ $t('hero_cta') }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">{{ $t('hero_no_cc') }}</p>
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
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ $t('joined', { count: joinedCount }) }}</span>
                        </p>
                    </div>

                    <!-- Progress bar -->
                    <div class="flex items-center gap-4 w-full sm:w-auto sm:min-w-72">
                        <div class="flex-1">
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1.5">
                                <span>{{ $t('spots_claimed') }}</span>
                                <span class="font-semibold text-primary-600 dark:text-primary-400">{{ joinedCount }} / 100</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" :style="{ width: joinedCount + '%' }"></div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">{{ $t('spots_left', { count: spotsLeft }) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- WHAT IS ACOMODY -->
        <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">{{ $t('what_label') }}</span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3 mb-6">
                        {{ $t('what_title') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">{{ $t('what_p1') }}</p>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">{{ $t('what_p2') }}</p>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">{{ $t('what_p3') }}</p>

                    <!-- iCal sync highlight -->
                    <div class="flex items-start gap-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 rounded-xl p-4">
                        <div class="text-2xl shrink-0">🔄</div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">{{ $t('ical_title') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('ical_desc') }}</p>
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
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">{{ $t('benefits_label') }}</span>
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3">{{ $t('benefits_title') }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-3 max-w-xl mx-auto">{{ $t('benefits_subtitle') }}</p>
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
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">{{ $t('how_label') }}</span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3">{{ $t('how_title') }}</h2>
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
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $t('proof_title') }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-3">{{ $t('proof_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="testimonial in testimonials"
                    :key="testimonial.name"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-7 border border-gray-100 dark:border-gray-700 flex flex-col"
                >
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

        <!-- PLAN COMPARISON -->
        <section class="py-20 bg-gray-50 dark:bg-gray-800/40">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">{{ $t('plans_label') }}</span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-3">{{ $t('plans_title') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-3 max-w-xl mx-auto">{{ $t('plans_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Free -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-7">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $t('plan_free') }}</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-1">€0</p>
                        <p class="text-xs text-rose-500 mb-5">{{ $t('plan_free_commission') }}</p>
                        <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_unlimited_listings') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_f_tools') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_f_ai_limited') }}
                            </li>
                        </ul>
                    </div>

                    <!-- Early Host (highlighted) -->
                    <div class="relative bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-7 text-white shadow-xl shadow-primary-600/30">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span class="bg-amber-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full shadow whitespace-nowrap">⭐ {{ $t('plan_early_badge') }}</span>
                        </div>
                        <p class="text-sm font-medium text-white/80 mb-1">{{ $t('plan_early') }}</p>
                        <p class="text-3xl font-bold mb-1">€0</p>
                        <p class="text-xs text-green-300 mb-5">{{ $t('plan_early_commission') }}</p>
                        <ul class="space-y-2.5 text-sm text-white/90">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_unlimited_listings') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_f_tools') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_early_f_commission') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_f_ai_limited') }}
                            </li>
                        </ul>
                        <button
                            class="mt-6 w-full py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-colors text-sm"
                            @click="handleCta"
                        >
                            {{ $t('hero_cta') }}
                        </button>
                    </div>

                    <!-- Club -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-7">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Club</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-1">€29<span class="text-base font-normal text-gray-400">/mo</span></p>
                        <p class="text-xs text-green-600 dark:text-green-400 mb-5">{{ $t('plan_club_commission') }}</p>
                        <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_unlimited_listings') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_f_tools') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_club_ai') }}<sup>*</sup>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_club_link_in_bio') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ $t('plan_club_fair_price_meter') }}
                            </li>
                        </ul>
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-6 text-center">{{ $t('plan_club_ai_footnote') }}</p>
            </div>
        </section>

        <!-- FAQ -->
        <section class="py-20 max-w-3xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $t('faq_title') }}</h2>
            </div>
            <div class="space-y-3">
                <div
                    v-for="(item, i) in faqs"
                    :key="i"
                    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                >
                    <button
                        class="w-full flex items-center justify-between text-left px-6 py-5 gap-4"
                        @click="openFaq = openFaq === i ? null : i"
                    >
                        <span class="font-medium text-gray-900 dark:text-white text-sm">{{ item.q }}</span>
                        <svg
                            class="w-5 h-5 text-gray-400 shrink-0 transition-transform"
                            :class="{ 'rotate-180': openFaq === i }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="openFaq === i" class="px-6 pb-5 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ item.a }}
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA SECTION -->
        <section class="py-24 bg-gradient-to-br from-gray-900 to-primary-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
                <span class="inline-block text-xs font-semibold text-primary-300 uppercase tracking-widest mb-5">{{ $t('final_label') }}</span>
                <h2 class="text-5xl font-bold text-white mb-6">{{ $t('final_title') }}</h2>
                <p class="text-xl text-gray-300 mb-4 max-w-2xl mx-auto leading-relaxed">
                    {{ $t('final_subtitle') }}
                </p>
                <i18n-t keypath="final_remaining" tag="p" class="text-base text-primary-300 font-medium mb-10">
                    <template #count>
                        <strong class="text-white">{{ spotsLeft }}</strong>
                    </template>
                </i18n-t>
                <button
                    class="inline-flex items-center gap-2 px-10 py-5 text-lg font-semibold bg-primary-500 hover:bg-primary-400 text-white rounded-xl transition-all shadow-xl shadow-primary-500/30 hover:shadow-primary-500/50"
                    @click="handleCta"
                >
                    {{ $t('final_cta') }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
                <p class="mt-4 text-sm text-gray-500">{{ $t('final_no_cc') }}</p>
            </div>
        </section>

        <!-- MINIMAL FOOTER -->
        <footer class="bg-gray-900 border-t border-gray-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <img src="/images/acomody.png" alt="Acomody" class="h-7 brightness-0 invert" />
                        <span class="text-gray-500 text-sm">— {{ $t('footer_phase') }}</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-gray-400">
                        <router-link :to="{ name: 'page-terms' }" class="hover:text-white transition-colors">{{ $t('footer_terms') }}</router-link>
                        <router-link :to="{ name: 'page-privacy-policy' }" class="hover:text-white transition-colors">{{ $t('footer_privacy') }}</router-link>
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
            joinedCount: 23,
            totalSpots: 100,
            openFaq: null,
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

        spotsLeft() {
            return this.totalSpots - this.joinedCount;
        },

        platformStats() {
            return [
                { value: this.$t('stat_any_type'), label: this.$t('stat_any_type_desc') },
                { value: '0%', label: this.$t('stat_commission_desc') },
                { value: this.$t('stat_time'), label: this.$t('stat_time_desc') },
                { value: '100%', label: this.$t('stat_control_desc') },
            ];
        },

        benefits() {
            return [
                { icon: '🎯', title: this.$t('b1_title'), desc: this.$t('b1_desc') },
                { icon: '🚀', title: this.$t('b2_title'), desc: this.$t('b2_desc') },
                { icon: '📅', title: this.$t('b3_title'), desc: this.$t('b3_desc') },
                { icon: '⚡', title: this.$t('b4_title'), desc: this.$t('b4_desc') },
                { icon: '🔄', title: this.$t('b5_title'), desc: this.$t('b5_desc') },
                { icon: '💬', title: this.$t('b6_title'), desc: this.$t('b6_desc') },
            ];
        },

        steps() {
            return [
                { title: this.$t('s1_title'), desc: this.$t('s1_desc') },
                { title: this.$t('s2_title'), desc: this.$t('s2_desc') },
                { title: this.$t('s3_title'), desc: this.$t('s3_desc') },
            ];
        },

        faqs() {
            return [
                { q: this.$t('faq1_q'), a: this.$t('faq1_a') },
                { q: this.$t('faq2_q'), a: this.$t('faq2_a') },
                { q: this.$t('faq3_q'), a: this.$t('faq3_a') },
                { q: this.$t('faq4_q'), a: this.$t('faq4_a') },
            ];
        },
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

<i18n lang="yaml">
en:
  badge: "Early access — limited to 100 founding hosts"
  hero_line1: "List first."
  hero_line2: "Earn from day one."
  hero_subtitle: "Acomody is launching soon. Founding hosts get {highlight} and guaranteed top placement when we go live."
  hero_highlight: "0% commission for 6 months"
  hero_founding: "Be among the first 100 hosts on the platform."
  hero_cta: "List your property"
  hero_no_cc: "No credit card required. Listing takes under 5 minutes."
  joined: "{count} hosts already joined"
  spots_claimed: "Founding spots claimed"
  spots_left: "{count} left"
  what_label: "What is Acomody?"
  what_title: "A smarter way to rent out your property."
  what_p1: "Acomody is a property rental platform built for hosts who want more control and fewer fees. List your apartment, house, villa, cabin — any type of accommodation — and connect directly with guests looking to book short or long stays."
  what_p2: "Unlike legacy platforms, Acomody keeps things simple: you set your own prices, choose your own rules, and communicate directly with guests before confirming a booking. No surprises."
  what_p3: "We're currently in pre-launch — building the platform alongside our first hosts to make sure it works the way you actually need it to."
  ical_title: "Already listing on Airbnb or Booking.com?"
  ical_desc: "Acomody supports two-way iCal calendar sync. Add your existing calendars and your availability stays in sync across all platforms — no double bookings, no manual work."
  benefits_label: "Founding host perks"
  benefits_title: "Why list now?"
  benefits_subtitle: "These perks are exclusive to our first 100 hosts. Once the spots are gone, they're gone."
  how_label: "Simple process"
  how_title: "From listing to launch in 3 steps"
  proof_title: "Early hosts love it"
  proof_subtitle: "From our founding host community"
  final_label: "Limited time offer"
  final_title: "Become a founding host."
  final_subtitle: "0% commission. Top placement. Direct access to our team."
  final_remaining: "Only {count} spots remaining out of 100."
  final_cta: "Claim your founding spot"
  final_no_cc: "No credit card required. Takes under 5 minutes."
  footer_phase: "Platform in early access phase"
  footer_terms: "Terms"
  footer_privacy: "Privacy"
  stat_any_type: "Any type"
  stat_any_type_desc: "Apartments, houses, villas, cabins and more"
  stat_commission_desc: "Commission for founding hosts for 6 months from launch"
  stat_time: "5 min"
  stat_time_desc: "Average time to create a listing"
  stat_control_desc: "Control over your prices, rules and guests"
  b1_title: "0% commission — 6 months from launch"
  b1_desc: "Founding hosts keep 100% of every booking for 6 months after platform launch. No platform cut. That's hundreds saved before you even think about upgrading."
  b2_title: "Top placement at launch"
  b2_desc: "Your property gets priority position in search results when we open to guests. First in line, every time."
  b3_title: "First bookings, guaranteed"
  b3_desc: "We personally promote founding host properties to our pre-launch guest waitlist of thousands of travelers."
  b4_title: "List in under 5 minutes"
  b4_desc: "Our streamlined creation flow gets your property live without the complexity of other platforms."
  b5_title: "Sync with Airbnb, Booking & more"
  b5_desc: "Connect your existing calendars via iCal. Availability stays in sync automatically — no double bookings, no manual updates."
  b6_title: "Direct line to our team"
  b6_desc: "Founding hosts get a dedicated channel and direct access to our founders. Your feedback shapes the product."
  s1_title: "Create your listing"
  s1_desc: "Add your property details, photos, and pricing in a simple step-by-step flow. Done in minutes."
  s2_title: "We review and prepare"
  s2_desc: "Our team personally reviews and optimizes your listing for maximum visibility at launch."
  s3_title: "Start receiving bookings"
  s3_desc: "When we launch, your listing goes live first — visible to thousands of pre-registered guests."
  plans_label: "Transparent pricing"
  plans_title: "Simple, honest plans"
  plans_subtitle: "Register now and get 0% commission for 6 months after platform launch — the Early Host benefit, automatically applied."
  plan_free: "Free"
  plan_free_commission: "12% commission per booking"
  plan_early: "Early Host"
  plan_early_badge: "You get this"
  plan_early_commission: "0% commission — 6 months from platform launch"
  plan_early_f_commission: "0% commission for 6 months from launch"
  plan_club_commission: "6% commission, always"
  plan_unlimited_listings: "Unlimited listings"
  plan_f_tools: "Calendar & booking tools"
  plan_f_ai_limited: "AI Receptionist (limited)"
  plan_club_ai: "AI Receptionist (full)"
  plan_club_ai_footnote: "* AI Receptionist usage is virtually unlimited. To ensure quality for all hosts, very high usage may require extra credits."
  plan_club_link_in_bio: "Link in bio tool"
  plan_club_fair_price_meter: "Fair price meter"
  faq_title: "Questions & answers"
  faq1_q: "How long does the 0% commission last?"
  faq1_a: "6 months from the date the platform launches publicly. After that, your account reverts to the standard Free plan (12% commission) unless you upgrade to Club (€29/mo, 6% commission)."
  faq2_q: "Do I need a credit card to sign up?"
  faq2_a: "No. Signing up and creating your listing is completely free. No payment details required."
  faq3_q: "When does the early host offer end?"
  faq3_a: "The offer is available while our early host program is active. We will notify you before it ends."
  faq4_q: "How many properties can I list?"
  faq4_a: "Both Free and Club plans support unlimited listings. On Club (€29/mo) you also get a lower 6% commission and full AI Receptionist."

sr:
  badge: "Rani pristup — ograničeno na 100 osnivačkih domaćina"
  hero_line1: "Objavi prvi."
  hero_line2: "Zarađuj od prvog dana."
  hero_subtitle: "Acomody se uskoro lansira. Osnivački domaćini dobijaju {highlight} i garantovano top pozicioniranje kada se platforma pokrene."
  hero_highlight: "0% provizije tokom 6 meseci"
  hero_founding: "Budite među prvih 100 domaćina na platformi."
  hero_cta: "Oglasite svoju nekretninu"
  hero_no_cc: "Nije potrebna kreditna kartica. Kreiranje oglasa traje manje od 5 minuta."
  joined: "{count} domaćina se već pridružilo"
  spots_claimed: "Zauzeto osnivačkih mesta"
  spots_left: "{count} preostalo"
  what_label: "Šta je Acomody?"
  what_title: "Pametniji način iznajmljivanja vaše nekretnine."
  what_p1: "Acomody je platforma za iznajmljivanje nekretnina napravljena za domaćine koji žele više kontrole i manje naknada. Oglašujte stan, kuću, vilu, kolibu — bilo koji tip smeštaja — i povežite se direktno sa gostima."
  what_p2: "Za razliku od legacy platformi, Acomody je jednostavan: vi određujete cene, birate pravila i direktno komunicirate sa gostima pre potvrde rezervacije. Bez iznenađenja."
  what_p3: "Trenutno smo u pre-lansiranju — gradimo platformu zajedno sa prvim domaćinima kako bismo osigurali da funkcioniše onako kako vam stvarno treba."
  ical_title: "Već oglašavate na Airbnb-u ili Booking.com-u?"
  ical_desc: "Acomody podržava dvosmerni iCal sinhronizaciju kalendara. Dodajte postojeće kalendare i vaša dostupnost ostaje sinhronizovana na svim platformama — bez dvostrukih rezervacija i ručnog rada."
  benefits_label: "Pogodnosti za osnivačke domaćine"
  benefits_title: "Zašto oglasiti sada?"
  benefits_subtitle: "Ove pogodnosti su ekskluzivne za naših prvih 100 domaćina. Kada se mesta popune, nema više."
  how_label: "Jednostavan proces"
  how_title: "Od oglasa do lansiranja u 3 koraka"
  proof_title: "Rani domaćini su oduševljeni"
  proof_subtitle: "Iz naše zajednice osnivačkih domaćina"
  final_label: "Ograničena ponuda"
  final_title: "Postanite osnivački domaćin."
  final_subtitle: "0% provizije. Top pozicioniranje. Direktan pristup našem timu."
  final_remaining: "Samo {count} mesta preostalo od 100."
  final_cta: "Rezervišite osnivačko mesto"
  final_no_cc: "Nije potrebna kreditna kartica. Traje manje od 5 minuta."
  footer_phase: "Platforma u fazi ranog pristupa"
  footer_terms: "Uslovi korišćenja"
  footer_privacy: "Privatnost"
  stat_any_type: "Bilo koji tip"
  stat_any_type_desc: "Stanovi, kuće, vile, kabine i još mnogo toga"
  stat_commission_desc: "Provizija za osnivačke domaćine 6 meseci od lansiranja"
  stat_time: "5 min"
  stat_time_desc: "Prosečno vreme za kreiranje oglasa"
  stat_control_desc: "Kontrola nad cenama, pravilima i gostima"
  b1_title: "0% provizije — 6 meseci od lansiranja"
  b1_desc: "Osnivački domaćini zadržavaju 100% svake rezervacije 6 meseci od lansiranja platforme. Bez odbitaka platforme od prvog dana."
  b2_title: "Top pozicioniranje na lansiranju"
  b2_desc: "Vaša nekretnina dobija prioritetno mesto u rezultatima pretrage. Uvek na prvom mestu."
  b3_title: "Prve rezervacije, garantovano"
  b3_desc: "Lično promovišemo nekretnine osnivačkih domaćina na pre-lansiranjoj listi čekanja od hiljada putnika."
  b4_title: "Oglasite za manje od 5 minuta"
  b4_desc: "Naš pojednostavljen proces kreira vaš oglas bez složenosti ostalih platformi."
  b5_title: "Sinhronizacija sa Airbnb, Booking i više"
  b5_desc: "Povežite kalendare putem iCal. Dostupnost se automatski sinhronizuje — bez dvostrukih rezervacija."
  b6_title: "Direktna linija sa našim timom"
  b6_desc: "Osnivački domaćini dobijaju poseban kanal i direktan pristup osnivačima. Vaše povratne informacije oblikuju produkt."
  s1_title: "Kreirajte oglas"
  s1_desc: "Dodajte detalje nekretnine, fotografije i cene u jednostavnom procesu korak-po-korak. Gotovo za minuta."
  s2_title: "Pregledamo i pripremamo"
  s2_desc: "Naš tim lično pregledava i optimizuje vaš oglas za maksimalnu vidljivost na lansiranju."
  s3_title: "Počnite da primate rezervacije"
  s3_desc: "Kada se lansiramo, vaš oglas ide live prvi — vidljiv hiljadama pre-registrovanih gostiju."
  plans_label: "Transparentno cenovnik"
  plans_title: "Jednostavni, pošteni planovi"
  plans_subtitle: "Registrujte se sada i dobijte 0% provizije 6 meseci od lansiranja platforme — benefit ranog domaćina, primenjen automatski."
  plan_free: "Besplatan"
  plan_free_commission: "12% provizije po rezervaciji"
  plan_early: "Rani domaćin"
  plan_early_badge: "Ovo dobijate vi"
  plan_early_commission: "0% provizije — 6 meseci od lansiranja platforme"
  plan_early_f_commission: "0% provizije 6 meseci od lansiranja"
  plan_club_commission: "6% provizije, uvek"
  plan_unlimited_listings: "Neograničeni smeštaji"
  plan_f_tools: "Kalendar i alati za rezervacije"
  plan_f_ai_limited: "AI recepcionista (ograničen)"
  plan_club_ai: "AI recepcionista (pun)"
  plan_club_ai_footnote: "* Korišćenje AI recepcioniste je praktično neograničeno. Da bismo osigurali kvalitet za sve domaćine, veoma visoka upotreba može zahtevati dodatne kredite."
  plan_club_link_in_bio: "Link in bio alat"
  plan_club_fair_price_meter: "Merač realne cene"
  faq_title: "Pitanja i odgovori"
  faq1_q: "Koliko dugo traje 0% provizija?"
  faq1_a: "6 meseci od datuma javnog lansiranja platforme. Nakon toga nalog prelazi na standardni besplatni plan (12% provizije), osim ako ne nadogradite na Club (€29/mes, 6% provizije)."
  faq2_q: "Da li mi je potrebna kreditna kartica?"
  faq2_a: "Ne. Registracija i kreiranje oglasa su potpuno besplatni. Nisu potrebni podaci o plaćanju."
  faq3_q: "Kada ističe ponuda za rane domaćine?"
  faq3_a: "Ponuda je dostupna dok je program aktivan. Obavestićemo vas unapred pre nego što istekne."
  faq4_q: "Koliko smeštaja mogu da postavim?"
  faq4_a: "I besplatni i Club plan podržavaju neograničene smeštaje. Na Club planu (€29/mes) dobijate i nižu proviziju od 6% i punog AI recepcionistu."

hr:
  badge: "Rani pristup — ograničeno na 100 osnivačkih domaćina"
  hero_line1: "Oglasi prvi."
  hero_line2: "Zarađuj od prvog dana."
  hero_subtitle: "Acomody se uskoro lansira. Osnivački domaćini dobivaju {highlight} i zajamčeno top pozicioniranje kada se platforma pokrene."
  hero_highlight: "0% provizije tijekom 6 mjeseci"
  hero_founding: "Budite među prvih 100 domaćina na platformi."
  hero_cta: "Oglasi svoju nekretninu"
  hero_no_cc: "Kreditna kartica nije potrebna. Kreiranje oglasa traje manje od 5 minuta."
  joined: "{count} domaćina se već pridružilo"
  spots_claimed: "Zauzeto osnivačkih mjesta"
  spots_left: "{count} preostalo"
  what_label: "Što je Acomody?"
  what_title: "Pametniji način iznajmljivanja vaše nekretnine."
  what_p1: "Acomody je platforma za iznajmljivanje nekretnina napravljena za domaćine koji žele više kontrole i manje naknada. Oglašajte stan, kuću, vilu, kolibu — bilo koji tip smještaja — i povežite se izravno s gostima."
  what_p2: "Za razliku od legacy platformi, Acomody je jednostavan: vi određujete cijene, birate pravila i izravno komunicirate s gostima prije potvrde rezervacije. Bez iznenađenja."
  what_p3: "Trenutno smo u pred-lansiranju — gradimo platformu zajedno s prvim domaćinima kako bismo osigurali da funkcionira onako kako vam stvarno treba."
  ical_title: "Već oglašavate na Airbnb-u ili Booking.com-u?"
  ical_desc: "Acomody podržava dvosmjernu iCal sinkronizaciju kalendara. Dodajte postojeće kalendare i vaša dostupnost ostaje sinkronizirana na svim platformama — bez dvostrukih rezervacija i ručnog rada."
  benefits_label: "Pogodnosti za osnivačke domaćine"
  benefits_title: "Zašto oglasiti sada?"
  benefits_subtitle: "Ove pogodnosti su ekskluzivne za naših prvih 100 domaćina. Kada se mjesta popune, nema više."
  how_label: "Jednostavan proces"
  how_title: "Od oglasa do lansiranja u 3 koraka"
  proof_title: "Rani domaćini su oduševljeni"
  proof_subtitle: "Iz naše zajednice osnivačkih domaćina"
  final_label: "Ograničena ponuda"
  final_title: "Postanite osnivački domaćin."
  final_subtitle: "0% provizije. Top pozicioniranje. Izravan pristup našem timu."
  final_remaining: "Samo {count} mjesta preostalo od 100."
  final_cta: "Rezervirajte osnivačko mjesto"
  final_no_cc: "Kreditna kartica nije potrebna. Traje manje od 5 minuta."
  footer_phase: "Platforma u fazi ranog pristupa"
  footer_terms: "Uvjeti korištenja"
  footer_privacy: "Privatnost"
  stat_any_type: "Bilo koji tip"
  stat_any_type_desc: "Stanovi, kuće, vile, kabine i još mnogo toga"
  stat_commission_desc: "Provizija za osnivačke domaćine 6 mjeseci od lansiranja"
  stat_time: "5 min"
  stat_time_desc: "Prosječno vrijeme za kreiranje oglasa"
  stat_control_desc: "Kontrola nad cijenama, pravilima i gostima"
  b1_title: "0% provizije — 6 mjeseci od lansiranja"
  b1_desc: "Osnivački domaćini zadržavaju 100% svake rezervacije 6 mjeseci od lansiranja platforme. Bez odbitaka platforme od prvog dana."
  b2_title: "Top pozicioniranje na lansiranju"
  b2_desc: "Vaša nekretnina dobiva prioritetno mjesto u rezultatima pretrage. Uvijek na prvom mjestu."
  b3_title: "Prve rezervacije, zajamčeno"
  b3_desc: "Osobno promoviramo nekretnine osnivačkih domaćina na pred-lansiranjoj listi čekanja od tisuća putnika."
  b4_title: "Oglasi za manje od 5 minuta"
  b4_desc: "Naš pojednostavljeni proces kreira vaš oglas bez složenosti ostalih platformi."
  b5_title: "Sinkronizacija s Airbnb, Booking i više"
  b5_desc: "Povežite kalendare putem iCal. Dostupnost se automatski sinkronizira — bez dvostrukih rezervacija."
  b6_title: "Izravna linija s našim timom"
  b6_desc: "Osnivački domaćini dobivaju poseban kanal i izravan pristup osnivačima. Vaše povratne informacije oblikuju produkt."
  s1_title: "Kreirajte oglas"
  s1_desc: "Dodajte detalje nekretnine, fotografije i cijene u jednostavnom procesu korak-po-korak. Gotovo za minuta."
  s2_title: "Pregledavamo i pripremamo"
  s2_desc: "Naš tim osobno pregledava i optimizira vaš oglas za maksimalnu vidljivost na lansiranju."
  s3_title: "Počnite primati rezervacije"
  s3_desc: "Kada se lansiramo, vaš oglas ide live prvi — vidljiv tisućama pred-registriranih gostiju."
  plans_label: "Transparentni cjenik"
  plans_title: "Jednostavni, pošteni planovi"
  plans_subtitle: "Registrirajte se sada i dobijte 0% provizije 6 mjeseci od lansiranja platforme — benefit ranog domaćina, primijenjen automatski."
  plan_free: "Besplatan"
  plan_free_commission: "12% provizije po rezervaciji"
  plan_early: "Rani domaćin"
  plan_early_badge: "Ovo dobivate vi"
  plan_early_commission: "0% provizije — 6 mjeseci od lansiranja platforme"
  plan_early_f_commission: "0% provizije 6 mjeseci od lansiranja"
  plan_club_commission: "6% provizije, uvijek"
  plan_unlimited_listings: "Neograničeni smještaji"
  plan_f_tools: "Kalendar i alati za rezervacije"
  plan_f_ai_limited: "AI recepcionar (ograničen)"
  plan_club_ai: "AI recepcionar (pun)"
  plan_club_ai_footnote: "* Korištenje AI recepcionara je praktički neograničeno. Kako bismo osigurali kvalitetu za sve domaćine, iznimno visoka upotreba može zahtijevati dodatne kredite."
  plan_club_link_in_bio: "Link in bio alat"
  plan_club_fair_price_meter: "Mjerač realne cijene"
  faq_title: "Pitanja i odgovori"
  faq1_q: "Koliko dugo traje 0% provizija?"
  faq1_a: "6 mjeseci od datuma javnog lansiranja platforme. Nakon toga račun prelazi na standardni besplatni plan (12% provizije), osim ako ne nadogradite na Club (€29/mj, 6% provizije)."
  faq2_q: "Je li mi potrebna kreditna kartica?"
  faq2_a: "Ne. Registracija i kreiranje oglasa su potpuno besplatni. Nisu potrebni podaci o plaćanju."
  faq3_q: "Kada istječe ponuda za rane domaćine?"
  faq3_a: "Ponuda je dostupna dok je program aktivan. Obavijestit ćemo vas unaprijed prije nego što istekne."
  faq4_q: "Koliko smještaja mogu oglasiti?"
  faq4_a: "I besplatni i Club plan podržavaju neograničene smještaje. Na Club planu (€29/mj) dobivate i nižu proviziju od 6% i punog AI recepcionara."

mk:
  badge: "Ран пристап — ограничено на 100 основачки домаќини"
  hero_line1: "Огласи прв."
  hero_line2: "Заработи од првиот ден."
  hero_subtitle: "Acomody се наскоро лансира. Основачките домаќини добиваат {highlight} и гарантирано топ позиционирање кога платформата ќе се покрене."
  hero_highlight: "0% провизија за 6 месеци"
  hero_founding: "Бидете меѓу првите 100 домаќини на платформата."
  hero_cta: "Огласете го вашиот имот"
  hero_no_cc: "Кредитна картичка не е потребна. Создавањето оглас трае помалку од 5 минути."
  joined: "{count} домаќини веќе се придружиле"
  spots_claimed: "Заземени основачки места"
  spots_left: "{count} преостанати"
  what_label: "Што е Acomody?"
  what_title: "Попаметен начин за издавање на вашиот имот."
  what_p1: "Acomody е платформа за издавање имот направена за домаќини кои сакаат повеќе контрола и помалку надоместоци. Огласете стан, куќа, вила, колиба — кој било тип на сместување — и поврзете се директно со гостите."
  what_p2: "За разлика од традиционалните платформи, Acomody е едноставна: вие ги одредувате цените, ги избирате правилата и директно комуницирате со гостите пред потврдата на резервацијата. Без изненадувања."
  what_p3: "Моментално сме во пред-лансирање — ја градиме платформата заедно со првите домаќини за да осигуриме дека функционира онака како на вас ви е потребно."
  ical_title: "Веќе огласувате на Airbnb или Booking.com?"
  ical_desc: "Acomody поддржува двонасочна iCal синхронизација на календарот. Додадете ги постоечките календари и вашата достапност останува синхронизирана на сите платформи — без двојни резервации и рачна работа."
  benefits_label: "Погодности за основачките домаќини"
  benefits_title: "Зошто да огласите сега?"
  benefits_subtitle: "Овие погодности се ексклузивни за нашите први 100 домаќини. Кога ќе се пополнат местата, нема повеќе."
  how_label: "Едноставен процес"
  how_title: "Од оглас до лансирање во 3 чекори"
  proof_title: "Раните домаќини се воодушевени"
  proof_subtitle: "Од нашата заедница на основачки домаќини"
  final_label: "Ограничена понуда"
  final_title: "Станете основачки домаќин."
  final_subtitle: "0% провизија. Топ позиционирање. Директен пристап до нашиот тим."
  final_remaining: "Само {count} места преостануваат од 100."
  final_cta: "Резервирајте основачко место"
  final_no_cc: "Кредитна картичка не е потребна. Трае помалку од 5 минути."
  footer_phase: "Платформа во фаза на ран пристап"
  footer_terms: "Услови за користење"
  footer_privacy: "Приватност"
  stat_any_type: "Кој било тип"
  stat_any_type_desc: "Станови, куќи, вили, кабини и уште многу"
  stat_commission_desc: "Провизија за основачките домаќини 6 месеци од лансирањето"
  stat_time: "5 мин"
  stat_time_desc: "Просечно време за создавање оглас"
  stat_control_desc: "Контрола над цените, правилата и гостите"
  b1_title: "0% провизија — 6 месеци од лансирањето"
  b1_desc: "Основачките домаќини задржуваат 100% на секоја резервација 6 месеци од лансирањето на платформата. Без одбитоци на платформата од првиот ден."
  b2_title: "Топ позиционирање на лансирањето"
  b2_desc: "Вашиот имот добива приоритетна позиција во резултатите на пребарувањето. Секогаш на прво место."
  b3_title: "Први резервации, гарантирано"
  b3_desc: "Лично ги промовираме имотите на основачките домаќини на нашата листа на чекање пред лансирањето."
  b4_title: "Огласете за помалку од 5 минути"
  b4_desc: "Нашиот поедноставен процес го создава вашиот оглас без сложеноста на другите платформи."
  b5_title: "Синхронизација со Airbnb, Booking и повеќе"
  b5_desc: "Поврзете ги календарите преку iCal. Достапноста автоматски се синхронизира — без двојни резервации."
  b6_title: "Директна линија со нашиот тим"
  b6_desc: "Основачките домаќини добиваат посебен канал и директен пристап до основачите. Вашите повратни информации го обликуваат производот."
  s1_title: "Создадете оглас"
  s1_desc: "Додадете детали за имотот, фотографии и цени во едноставен процес чекор по чекор. Готово за минути."
  s2_title: "Ние го прегледуваме и подготвуваме"
  s2_desc: "Нашиот тим лично го прегледува и оптимизира вашиот оглас за максимална видливост на лансирањето."
  s3_title: "Почнете да примате резервации"
  s3_desc: "Кога ќе се лансираме, вашиот оглас оди live прв — видлив на илјадници пред-регистрирани гости."
  plans_label: "Транспарентни цени"
  plans_title: "Едноставни, поштени планови"
  plans_subtitle: "Регистрирајте се сега и добијте 0% провизија 6 месеци од лансирањето на платформата — бенефит на ран домаќин, применет автоматски."
  plan_free: "Бесплатен"
  plan_free_commission: "12% провизија по резервација"
  plan_early: "Ран домаќин"
  plan_early_badge: "Ова го добивате вие"
  plan_early_commission: "0% провизија — 6 месеци од лансирањето на платформата"
  plan_early_f_commission: "0% провизија 6 месеци од лансирањето"
  plan_club_commission: "6% провизија, секогаш"
  plan_unlimited_listings: "Неограничени сместувања"
  plan_f_tools: "Календар и алати за резервации"
  plan_f_ai_limited: "AI рецепционер (ограничен)"
  plan_club_ai: "AI рецепционер (полн)"
  plan_club_ai_footnote: "* Користењето на AI рецепционерот е практично неограничено. За да обезбедиме квалитет за сите домаќини, многу висока употреба може да бара дополнителни кредити."
  plan_club_link_in_bio: "Link in bio aлатка"
  plan_club_fair_price_meter: "Мерач на фер цена"
  faq_title: "Прашања и одговори"
  faq1_q: "Колку долго трае 0% провизија?"
  faq1_a: "6 месеци од датумот на јавното лансирање на платформата. По тоа сметката се враќа на стандардниот бесплатен план (12% провизија), освен ако не надградите на Club (€29/мес, 6% провизија)."
  faq2_q: "Дали ми е потребна кредитна картичка?"
  faq2_a: "Не. Регистрацијата и создавањето на огласот се целосно бесплатни. Не се потребни податоци за плаќање."
  faq3_q: "Кога истекува понудата за рани домаќини?"
  faq3_a: "Понудата е достапна додека програмот е активен. Ќе ве известиме однапред пред да истече."
  faq4_q: "Колку сместувања можам да огласам?"
  faq4_a: "И бесплатниот и Club планот поддржуваат неограничени сместувања. На Club план (€29/мес) добивате и пониска провизија од 6% и полн AI рецепционер."

sl:
  badge: "Zgodnji dostop — omejeno na 100 ustanovnih gostiteljev"
  hero_line1: "Oglašaj prvi."
  hero_line2: "Zaslužuj od prvega dne."
  hero_subtitle: "Acomody se kmalu lansira. Ustanovni gostitelji dobijo {highlight} in zajamčeno vrhunsko pozicioniranje ob zagonu platforme."
  hero_highlight: "0% provizije za 6 mesecev"
  hero_founding: "Bodite med prvimi 100 gostitelji na platformi."
  hero_cta: "Oglasite svojo nepremičnino"
  hero_no_cc: "Kreditna kartica ni potrebna. Ustvarjanje oglasa traja manj kot 5 minut."
  joined: "{count} gostiteljev se je že pridružilo"
  spots_claimed: "Zasedenih ustanovnih mest"
  spots_left: "{count} preostalo"
  what_label: "Kaj je Acomody?"
  what_title: "Pametnejši način oddajanja vaše nepremičnine."
  what_p1: "Acomody je platforma za oddajanje nepremičnin za gostitelje, ki želijo več nadzora in manj stroškov. Oglasite stanovanje, hišo, vilo, kočo — katero koli vrsto nastanitve — in se neposredno povežite z gosti."
  what_p2: "Za razliko od uveljavljenih platform je Acomody preprost: vi določate cene, izbirate pravila in neposredno komunicirate z gosti pred potrditvijo rezervacije. Brez presenečenj."
  what_p3: "Trenutno smo v pred-lansiranju — gradimo platformo skupaj s prvimi gostitelji, da zagotovimo, da deluje tako, kot zares potrebujete."
  ical_title: "Že oglašujete na Airbnb ali Booking.com?"
  ical_desc: "Acomody podpira dvosmerno sinhronizacijo iCal kalendarjev. Dodajte obstoječe kalendarje in vaša razpoložljivost ostane sinhronizirana na vseh platformah — brez dvojnih rezervacij in ročnega dela."
  benefits_label: "Ugodnosti za ustanovne gostitelje"
  benefits_title: "Zakaj oglasite zdaj?"
  benefits_subtitle: "Te ugodnosti so ekskluzivne za naših prvih 100 gostiteljev. Ko so mesta zapolnjena, jih ni več."
  how_label: "Preprost postopek"
  how_title: "Od oglasa do lansiranja v 3 korakih"
  proof_title: "Zgodnji gostitelji so navdušeni"
  proof_subtitle: "Iz naše skupnosti ustanovnih gostiteljev"
  final_label: "Omejena ponudba"
  final_title: "Postanite ustanovni gostitelj."
  final_subtitle: "0% provizije. Vrhunsko pozicioniranje. Neposreden dostop do naše ekipe."
  final_remaining: "Samo {count} mest preostaja od 100."
  final_cta: "Rezervirajte ustanovno mesto"
  final_no_cc: "Kreditna kartica ni potrebna. Traja manj kot 5 minut."
  footer_phase: "Platforma v fazi zgodnjega dostopa"
  footer_terms: "Pogoji uporabe"
  footer_privacy: "Zasebnost"
  stat_any_type: "Katera koli vrsta"
  stat_any_type_desc: "Stanovanja, hiše, vile, koče in še več"
  stat_commission_desc: "Provizija za ustanovne gostitelje 6 mesecev od lansiranja"
  stat_time: "5 min"
  stat_time_desc: "Povprečen čas za ustvarjanje oglasa"
  stat_control_desc: "Nadzor nad cenami, pravili in gosti"
  b1_title: "0% provizije — 6 mesecev od lansiranja"
  b1_desc: "Ustanovni gostitelji obdržijo 100% vsake rezervacije 6 mesecev od lansiranja platforme. Brez odbitkov platforme od prvega dne."
  b2_title: "Vrhunsko pozicioniranje ob lansiranju"
  b2_desc: "Vaša nepremičnina dobi prioritetno mesto v rezultatih iskanja. Vedno na prvem mestu."
  b3_title: "Prve rezervacije, zajamčeno"
  b3_desc: "Osebno promoviramo nepremičnine ustanovnih gostiteljev na naši pred-lansiranjski čakalni listi tisočih popotnikov."
  b4_title: "Oglasite v manj kot 5 minutah"
  b4_desc: "Naš poenostavljen postopek ustvari vaš oglas brez zapletenosti drugih platform."
  b5_title: "Sinhronizacija z Airbnb, Booking in več"
  b5_desc: "Povežite kalendarje prek iCal. Razpoložljivost se samodejno sinhronizira — brez dvojnih rezervacij."
  b6_title: "Neposredna linija z našo ekipo"
  b6_desc: "Ustanovni gostitelji dobijo namenski kanal in neposreden dostop do ustanoviteljev. Vaše povratne informacije oblikujejo produkt."
  s1_title: "Ustvarite oglas"
  s1_desc: "Dodajte podrobnosti nepremičnine, fotografije in cene v preprostem postopku korak za korakom. Končano v minutah."
  s2_title: "Pregledamo in pripravimo"
  s2_desc: "Naša ekipa osebno pregleduje in optimizira vaš oglas za največjo vidljivost ob lansiranju."
  s3_title: "Začnite prejemati rezervacije"
  s3_desc: "Ko se lansiramo, vaš oglas gre live prvi — viden tisočim pred-registriranim gostom."
  plans_label: "Pregledne cene"
  plans_title: "Preprosti, pošteni načrti"
  plans_subtitle: "Registrirajte se zdaj in pridobite 0% provizije 6 mesecev od lansiranja platforme — ugodnost zgodnjega gostitelja, samodejno dodeljeno."
  plan_free: "Brezplačno"
  plan_free_commission: "12% provizije na rezervacijo"
  plan_early: "Zgodnji gostitelj"
  plan_early_badge: "To dobite vi"
  plan_early_commission: "0% provizije — 6 mesecev od lansiranja platforme"
  plan_early_f_commission: "0% provizije 6 mesecev od lansiranja"
  plan_club_commission: "6% provizije, vedno"
  plan_unlimited_listings: "Neomejene nastanitve"
  plan_f_tools: "Koledar in orodja za rezervacije"
  plan_f_ai_limited: "AI recepcionist (omejen)"
  plan_club_ai: "AI recepcionist (polni)"
  plan_club_ai_footnote: "* Uporaba AI recepcionista je praktično neomejena. Za zagotavljanje kakovosti za vse gostitelje lahko zelo visoka uporaba zahteva dodatne kredite."
  plan_club_link_in_bio: "Link in bio orodje"
  plan_club_fair_price_meter: "Merilnik realne cene"
  faq_title: "Vprašanja in odgovori"
  faq1_q: "Kako dolgo traja 0% provizija?"
  faq1_a: "6 mesecev od datuma javnega lansiranja platforme. Po tem se račun vrne na standardni brezplačni načrt (12% provizije), razen če nadgradite na Club (€29/mes, 6% provizije)."
  faq2_q: "Ali potrebujem kreditno kartico?"
  faq2_a: "Ne. Registracija in ustvarjanje oglasa sta popolnoma brezplačna. Podatki o plačilu niso potrebni."
  faq3_q: "Kdaj se konča ponudba za zgodnje gostitelje?"
  faq3_a: "Ponudba je na voljo, dokler je program aktiven. Obvestili vas bomo vnaprej, preden se konča."
  faq4_q: "Koliko nastanitev lahko oglasim?"
  faq4_a: "Tako brezplačni kot Club načrt podpirata neomejene nastanitve. Na Club načrtu (€29/mes) dobite tudi nižjo provizijo 6% in polnega AI recepcionista."
</i18n>
