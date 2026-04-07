import { useHead } from '@unhead/vue';

/**
 * Sets page <title>, meta description, and Open Graph tags.
 * Accepts plain strings or computed refs — all values are reactive.
 *
 * @param {object} options
 * @param {string|import('vue').ComputedRef<string>} options.title
 * @param {string|import('vue').ComputedRef<string>} options.description
 * @param {string|import('vue').ComputedRef<string>} [options.image]
 * @param {string} [options.type]  og:type, defaults to 'website'
 */
export function useSeoHead({ title, description, image, type = 'website' }) {
    useHead({
        title,
        meta: [
            { name: 'description', content: description },
            { property: 'og:title', content: title },
            { property: 'og:description', content: description },
            { property: 'og:type', content: type },
            ...(image ? [{ property: 'og:image', content: image }] : []),
        ],
    });
}
