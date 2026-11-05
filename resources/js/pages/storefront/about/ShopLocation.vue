<script setup lang="ts">
import { useAppConfig } from '@/composables/useAppConfig';

const { brandName, location } = useAppConfig();
</script>

<template>
    <section class="ShopLocation mb-15">
        <div class="container-fluid">
            <div class="shop-location-wrapper grid gap-8 lg:grid-cols-2">
                <div class="location-details space-y-2">
                    <p class="uppercase text-body-label-text">Visit {{ brandName }}</p>
                    <p class="text-heading-text font-bold">Shop with us in {{ location.city }}</p>
                    <p>Visit our physical store to explore the catalogue.</p>

                    <div class="location-cards mt-8">
                        <div class="location-card flex items-center justify-between border-b border-border py-4">
                            <span>Building</span>
                            <span>{{ location.address }}</span>
                        </div>
                        <div class="location-card flex items-center justify-between border-b border-border py-4">
                            <span>Shop</span>
                            <span>{{ location.shop }}</span>
                        </div>
                        <div class="location-card flex items-center justify-between border-b border-border py-4">
                            <span>Location</span>
                            <span>{{ location.city }}</span>
                        </div>
                    </div>
                </div>

                <div class="map-location bg-black">
                    <iframe src="https://www.google.com/maps/embed?pb=!4v1793871523046!6m8!1m7!1sRRUuzVIhxPkFtXQVISN2lw!2m2!1d-1.173487236081597!2d36.8410368194491!3f132.04980429621222!4f-2.4045210123156124!5f0.7820865974627469" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>
        </div>
    </section>
</template>