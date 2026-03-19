<template>
    <div>
        <!-- Locations type: horizontal strip of location cards -->
        <locations-row
            v-if="section.type === 'locations' && section.locations.length"
            :title="sectionTitle"
            :locations="section.locations"
        />

        <!-- Accommodations type: grid of accommodations for the first location -->
        <div
            v-else-if="section.type === 'accommodations' && firstLocation"
            class="max-w-7xl mx-auto px-4 py-8"
        >
            <location-section :location="firstLocation" :title="sectionTitle" />
        </div>
    </div>
</template>

<script>
import LocationsRow from './LocationsRow.vue';
import LocationSection from './LocationSection.vue';

export default {
    name: 'HomeSectionComponent',
    components: {
        LocationsRow,
        LocationSection,
    },
    props: {
        section: {
            type: Object,
            required: true,
        },
    },
    computed: {
        sectionTitle() {
            const t = this.section.title;
            if (typeof t === 'string') {
                return t;
            }
            return t?.en || Object.values(t ?? {})[0] || '';
        },
        firstLocation() {
            const loc = this.section.locations?.[0];
            if (!loc) {
                return null;
            }
            return {
                ...loc,
                name: typeof loc.name === 'string'
                    ? loc.name
                    : (loc.name?.en || Object.values(loc.name ?? {})[0] || ''),
            };
        },
    },
};
</script>
