<script setup>
import heroImage from '@/assets/images/retail-and-online-shopping.jpg';
</script>

<template>
    <section class="Hero relative w-full min-h-[80vh] md:min-h-[90vh] flex items-center justify-center bg-cover bg-center bg-no-repeat" :style="{ backgroundImage: `url(${heroImage})` }">
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-linear-to-r from-black/70 via-black/50 to-black/70 z-10"></div>
        
        <!-- Text Content -->
        <div class="relative z-20 container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6 leading-tight">
                    Custom e-commerce to fit your business needs
                </h1>
                
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-6 md:mb-8 max-w-2xl mx-auto text-gray-200">
                    Shop with us today and get the best products at the best price
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/shop" 
                       class="inline-block bg-white text-gray-900 hover:bg-gray-100 font-semibold px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-lg">
                        Start Shopping
                    </a>
                    <a href="/about" 
                       class="inline-block bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg transition duration-300 ease-in-out">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>