<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ProductGrid from '@/pages/storefront/components/ProductGrid.vue';

interface Props {
	new_arrivals: any[];
	most_popular: any[];
    search?: string;
};

defineProps<Props>();
</script>

<template>
    <div class="HomePage">
		<section class="Hero lg:h-[90dvh] flex justify-center items-center">
			<div class="container-fluid grid lg:grid-cols-12 gap-8">
				<div class="content lg:col-span-7 space-y-4">
					<h1 class="title font-bold text-l-text lg:text-xl-text uppercase">Best Online <br> Shopping Experience.</h1>
					<div class="actions mt-8 bg-amber-400 text-black font-bold py-4 px-8 inline-block rounded-sm">
						<Link href="/shop">Start Shopping</Link>
					</div>
				</div>

				<div class="image hidden lg:block lg:col-span-5 h-[30dvh] lg:h-full">
					<img src="/assets/images/alsuwa-hero-image.png" alt="Air Force shoe hero image" class="w-full h-full object-cover">
				</div>
			</div>
		</section>

		<section class="NewArrivals mb-15">
			<ProductGrid 
				v-if="new_arrivals?.length"
				title="New Arrivals" 
				:products="new_arrivals" 
				:columns="4"
				class="NewArrivals"
			/>
		</section>

		<section class="MostPopular mb-15">
			<ProductGrid 
				title="Most Popular" 
				:products="most_popular"
				:columns="4"
				class="MostPopular"
			/>
		</section>
	</div>
</template>