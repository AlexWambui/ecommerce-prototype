<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="Hero relative w-full min-h-[80vh] md:min-h-[90vh] flex items-center justify-center bg-cover bg-center bg-no-repeat" :style="{ backgroundImage: `url(assets/images/alsuwa-hero-image.jpg)` }">
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-linear-to-r from-black/70 via-black/80 to-black/70 z-10"></div>
        
        <!-- Text Content -->
        <div class="relative z-20 container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <div class="max-w-4xl mx-auto">
                <h1 class="title font-bold text-l-text uppercase mb-4 md:mb-6 leading-tight">
                    Custom e-commerce to fit your business needs
                </h1>
                
                <p class="mb-6 md:mb-8 max-w-2xl mx-auto text-gray-200">
                    E-commerce should fit your business, not the other way round
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/shop" class="inline-block bg-white text-gray-900 hover:bg-gray-100 font-semibold px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-lg">Start Shopping</Link>

                    <Link href="/about" 
                       class="inline-block bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg transition duration-300 ease-in-out">
                        Learn More
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>