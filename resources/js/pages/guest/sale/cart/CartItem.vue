<template>
  <div class="cart-item">
    <div class="item-image">
      <img 
        :src="item.product?.image || '/placeholder.jpg'" 
        :alt="item.product_name"
        @error="handleImageError"
      />
    </div>
    
    <div class="item-details">
      <h3>{{ item.product_name }}</h3>
      <div v-if="item.attributes" class="item-attributes">
        <span v-for="(value, key) in item.attributes" :key="key">
          {{ key }}: {{ value }}
        </span>
      </div>
      <p class="item-sku">SKU: {{ item.product_sku }}</p>
      <p class="item-price">${{ formatPrice(item.unit_price) }}</p>
    </div>
    
    <div class="item-quantity">
      <label for="quantity-{{ item.id }}">Quantity:</label>
      <input
        :id="`quantity-${item.id}`"
        type="number"
        v-model.number="localQuantity"
        min="1"
        max="999"
        @change="handleQuantityChange"
        :disabled="loading"
      />
    </div>
    
    <div class="item-subtotal">
      <p>Subtotal: ${{ formatPrice(item.subtotal) }}</p>
    </div>
    
    <button 
      class="remove-btn" 
      @click="$emit('remove', item.id)"
      :disabled="loading"
    >
      Remove
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update-quantity', 'remove'])

const localQuantity = ref(props.item.quantity)

const handleQuantityChange = () => {
  if (localQuantity.value !== props.item.quantity) {
    emit('update-quantity', props.item.id, localQuantity.value)
  }
}

const handleImageError = (event) => {
  event.target.src = '/placeholder.jpg'
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}

// Watch for external quantity changes
watch(() => props.item.quantity, (newVal) => {
  localQuantity.value = newVal
})
</script>

<style scoped>
.cart-item {
  display: flex;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid #eee;
  padding: 20px 0;
  flex-wrap: wrap;
}

.item-image {
  width: 120px;
  height: 120px;
  flex-shrink: 0;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.item-details {
  flex: 1;
  min-width: 200px;
}

.item-details h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
}

.item-attributes {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.item-attributes span {
  background: #f5f5f5;
  padding: 2px 10px;
  border-radius: 12px;
}

.item-sku {
  font-size: 14px;
  color: #999;
  margin: 5px 0;
}

.item-price {
  font-weight: bold;
  font-size: 18px;
  margin: 5px 0;
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: 10px;
}

.item-quantity input {
  width: 60px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  text-align: center;
}

.item-subtotal {
  min-width: 120px;
  text-align: right;
}

.remove-btn {
  background: #ff4444;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s;
}

.remove-btn:hover:not(:disabled) {
  background: #cc0000;
}

.remove-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .cart-item {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  
  .item-image {
    width: 100%;
    height: 200px;
  }
  
  .item-subtotal {
    text-align: left;
  }
}
</style>