<template>
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <!-- Top row -->
            <div class="flex flex-col md:flex-row md:justify-between gap-8">
                <!-- Brand -->
                <div class="flex-shrink-0">
                    <router-link :to="{ name: 'page-welcome' }" class="text-xl font-bold text-primary-600 dark:text-primary-400 tracking-tight">
                        Acomody
                    </router-link>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                        Find and book unique accommodations around the world.
                    </p>
                </div>

                <!-- Links -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Company</h3>
                        <ul class="flex flex-col gap-3">
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">About</a></li>
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Careers</a></li>
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Press</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Support</h3>
                        <ul class="flex flex-col gap-3">
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Help center</a></li>
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Safety</a></li>
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Contact us</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Legal</h3>
                        <ul class="flex flex-col gap-3">
                            <li>
                                <router-link :to="{ name: 'page-privacy-policy' }" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
                                    Privacy policy
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{ name: 'page-terms' }" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
                                    Terms of service
                                </router-link>
                            </li>
                            <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition">Cookie policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    &copy; {{ year }} Acomody. All rights reserved.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: "Footer",
    computed: {
        year() {
            return new Date().getFullYear();
        },
    },
};
</script>
