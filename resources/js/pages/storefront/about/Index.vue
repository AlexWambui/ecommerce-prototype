<script setup lang="ts">
import { TruckElectric, PhoneCall, ShoppingBag } from '@lucide/vue';
import {useAppConfig} from '@/composables/useAppConfig.js';
import ShopLocation from './ShopLocation.vue';

const {brandName, brandTagline, brandDescription} = useAppConfig();
</script>

<template>
    <div class="AboutPage">
        <section class="Header py-16 mb-15">
            <div class="container-fluid space-y-4 text-center">
                <h1 class="uppercase text-body-label-text">About {{ brandName }}</h1>
                <p class="text-heading-text font-bold">{{ brandTagline }}</p>
                <p class="lg:w-[50%] lg:mx-auto">{{ brandDescription }}</p>
            </div>
        </section>

        <section class="WhyUs mb-15">
            <div class="container-fluid">
                <div class="content-wrapper grid gap-4 lg:grid-cols-2">
                    <div class="content lg:space-y-4">
                        <p class="uppercase text-body-label-text">Why {{ brandName }}</p>
                        <p class="font-bold text-heading-text lg:w-[50%]">We deliver a better shopping experience</p>
                        <p class="lg:w-[60%]">{{ brandDescription }}</p>
                    </div>

                    <div class="cards grid gap-4 lg:grid-cols-2">
                        <div class="card bg-accent rounded-sm p-4 space-y-2">
                            <TruckElectric class="w-8 h-8" />
                            <p class="font-bold">Contry-wide Delivery</p>
                            <p>Same day delivery across the whole country.</p>
                        </div>

                        <div class="card bg-accent rounded-sm p-4 space-y-2">
                            <PhoneCall class="w-8 h-8" />
                            <p class="font-bold">Personalized Support</p>
                            <p>Need help? Just reach out to us on Whatsapp.</p>
                        </div>

                        <div class="card bg-accent rounded-sm p-4 space-y-2">
                            <ShoppingBag class="w-8 h-8" />
                            <p class="font-bold">Curated Style</p>
                            <p>Our catalogue focuses on timeless everyday pieces.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <ShopLocation />

        <section class="CTA container-fluid mb-15">
            <div class="container-fluid py-12! space-y-2 text-center bg-blue-200 text-black rounded-lg">
                <p class="uppercase text-body-label-text">Discover {{ brandName }}</p>
                <p class="font-bold text-heading-text">{{ brandTagline }}</p>
                <p>{{ brandDescription }}</p>
                <div class="action_btn mt-8">
                    <a href="/shop" class="py-4 px-4 rounded-sm bg-amber-400">Discover {{ brandName }}</a>
                </div>
            </div>
        </section>
    </div>
</template>