import { computed } from 'vue';

interface AppConfig {
    brand_name: string;
    brand_tagline: string;
    brand_description: string;
    location: {
        city: string;
        address: string;
        shop: string;
    };
    contacts: {
        phone: string;
        whatsapp_phone_number: string;
        whatsapp_phone: string;
    };
    socials: {
        tiktok_url: string;
        tiktok_username: string;
        whatsapp_url: string;
        site_url: string;
    };
}

const WHATSAPP_PHONE = '254745744261';

const config: AppConfig = {
    brand_name: 'Alsuwa',
    brand_tagline: 'Best Online Shopping Experience',
    brand_description: 'Alsuwa is an online store that helps you easily track your sales and market your product catalogue.',
    location: {
        city: 'Kiambu, Kirigiti',
        address: 'Opposite Kirigiti Golf Club',
        shop: 'F, Room 4',
    },
    contacts: {
        phone: '+254 745 744 261',
        whatsapp_phone_number: '+254 745 744 261',
        whatsapp_phone: WHATSAPP_PHONE,
    },
    socials: {
        tiktok_url: 'https://www.tiktok.com/@alsuwahq',
        tiktok_username: 'alsuwahq',
        whatsapp_url: `https://wa.me/${WHATSAPP_PHONE}`,
        site_url: 'https://ecommerce.alsuwa.com',
    },
};

export function useAppConfig() {
    return {
        brandName: computed(() => config.brand_name),
        brandTagline: computed(() => config.brand_tagline),
        brandDescription: computed(() => config.brand_description),
        location: computed(() => config.location),
        contacts: computed(() => config.contacts),
        socials: computed(() => config.socials),
    };
}