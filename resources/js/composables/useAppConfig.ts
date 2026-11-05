import { computed } from 'vue';

interface AppConfig {
    brand_name: string;
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
    brand_description: 'Best Online Shopping Experience',
    location: {
        city: 'Kiambu, Ruii',
        address: 'Opposite Harvest Hill Plaza',
        shop: 'TF, Room 4',
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
        brandDescription: computed(() => config.brand_description),
        location: computed(() => config.location),
        contacts: computed(() => config.contacts),
        socials: computed(() => config.socials),
    };
}