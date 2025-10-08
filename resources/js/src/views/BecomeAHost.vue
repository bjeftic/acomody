<template>
    <base-wrapper>
        <template v-slot:content>
        <div class="hero">
            <div class="hero-text">
                <h1>List your property on Booking.com</h1>
                <p>
                    Reach millions of travelers and grow your business with the
                    world’s leading travel platform.
                </p>
                <n-button type="primary" size="large">Get started</n-button>
            </div>
            <img
                class="hero-img"
                src="https://cf.bstatic.com/static/img/join/host-hero-image/2e2e5e2e7e7e7e7e7e7e7e7e7e7e7e7e7e7e7e7e.png"
                alt="Become a host"
            />
        </div>
        <n-divider />
        <div class="steps">
            <n-grid :cols="3" :x-gap="32">
                <n-gi>
                    <n-card>
                        <n-icon size="36"><i class="fas fa-home"></i></n-icon>
                        <h3>1. Tell us about your place</h3>
                        <p>
                            Share details about your property to attract the
                            right guests.
                        </p>
                    </n-card>
                </n-gi>
                <n-gi>
                    <n-card>
                        <n-icon size="36"
                            ><i class="fas fa-calendar-check"></i
                        ></n-icon>
                        <h3>2. Set up your listing</h3>
                        <p>
                            Add photos, set your prices, and choose your
                            availability.
                        </p>
                    </n-card>
                </n-gi>
                <n-gi>
                    <n-card>
                        <n-icon size="36"><i class="fas fa-rocket"></i></n-icon>
                        <h3>3. Go live</h3>
                        <p>Start welcoming guests and earning income!</p>
                    </n-card>
                </n-gi>
            </n-grid>
        </div>
        <n-divider />
        <div class="cta-section">
            <n-card>
                <h2>Ready to get started?</h2>
                <n-button type="primary" size="large"
                    >List your property</n-button
                >
            </n-card>
        </div>
        </template>
    </base-wrapper>
</template>

<script>
export default {
    name: "BecomeAHost",
};
</script>

<style scoped>
.hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    margin-bottom: 32px;
}
.hero-text {
    max-width: 400px;
}
.hero-img {
    width: 320px;
    border-radius: 12px;
}
.steps {
    margin: 48px 0;
}
.cta-section {
    display: flex;
    justify-content: center;
    margin: 48px 0;
}
</style>

<!--
Note: For icons, you can use Font Awesome CDN in your index.html or replace <n-icon> content with any SVG/icon you prefer.
-->
