export interface ProductImage {
    url: string;
    alt: string;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    compare_price: number | null;
    stock: number;
    sku: string;
    category: string;
    tags: string[];
    images: ProductImage[];
    rating: number;
    reviews_count: number;
    is_featured: boolean;
    is_new: boolean;
    is_on_sale: boolean;
    created_at: string;
    updated_at: string;
}