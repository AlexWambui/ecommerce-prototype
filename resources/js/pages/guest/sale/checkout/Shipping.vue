<template>
  <GuestLayout>
    <div class="checkout-container">
      <h1>Shipping Information</h1>
      
      <div class="checkout-steps">
        <div class="step completed">✓ Email</div>
        <div class="step active">2. Shipping</div>
        <div class="step">3. Payment</div>
      </div>
      
      <div class="cart-summary-mini">
        <p>Items: {{ cart.items_count }}</p>
        <p>Total: ${{ formatPrice(cart.total) }}</p>
      </div>
      
      <form @submit.prevent="submitShipping" class="shipping-form">
        <div class="form-row">
          <div class="form-group">
            <label for="first_name">First Name</label>
            <input
              id="first_name"
              type="text"
              v-model="form.first_name"
              required
              :disabled="loading"
            />
            <div v-if="errors.first_name" class="error">{{ errors.first_name }}</div>
          </div>
          
          <div class="form-group">
            <label for="last_name">Last Name</label>
            <input
              id="last_name"
              type="text"
              v-model="form.last_name"
              required
              :disabled="loading"
            />
            <div v-if="errors.last_name" class="error">{{ errors.last_name }}</div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input
            id="phone"
            type="tel"
            v-model="form.phone"
            required
            :disabled="loading"
          />
          <div v-if="errors.phone" class="error">{{ errors.phone }}</div>
        </div>
        
        <div class="form-group">
          <label for="address_line1">Address Line 1</label>
          <input
            id="address_line1"
            type="text"
            v-model="form.address_line1"
            required
            :disabled="loading"
          />
          <div v-if="errors.address_line1" class="error">{{ errors.address_line1 }}</div>
        </div>
        
        <div class="form-group">
          <label for="address_line2">Address Line 2 (Optional)</label>
          <input
            id="address_line2"
            type="text"
            v-model="form.address_line2"
            :disabled="loading"
          />
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="city">City</label>
            <input
              id="city"
              type="text"
              v-model="form.city"
              required
              :disabled="loading"
            />
            <div v-if="errors.city" class="error">{{ errors.city }}</div>
          </div>
          
          <div class="form-group">
            <label for="state">State/Province</label>
            <input
              id="state"
              type="text"
              v-model="form.state"
              :disabled="loading"
            />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input
              id="postal_code"
              type="text"
              v-model="form.postal_code"
              required
              :disabled="loading"
            />
            <div v-if="errors.postal_code" class="error">{{ errors.postal_code }}</div>
          </div>
          
          <div class="form-group">
            <label for="country">Country</label>
            <select
              id="country"
              v-model="form.country"
              required
              :disabled="loading"
            >
              <option value="">Select Country</option>
              <option value="KE">Kenya</option>
              <option value="US">United States</option>
              <option value="UK">United Kingdom</option>
              <option value="CA">Canada</option>
            </select>
            <div v-if="errors.country" class="error">{{ errors.country }}</div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="shipping_method">Shipping Method</label>
          <select
            id="shipping_method"
            v-model="form.shipping_method"
            required
            :disabled="loading"
          >
            <option value="standard">Standard (5-7 days) - $5.00</option>
            <option value="express">Express (2-3 days) - $15.00</option>
          </select>
          <div v-if="errors.shipping_method" class="error">{{ errors.shipping_method }}</div>
        </div>
        
        <div class="form-group">
          <label for="notes">Order Notes (Optional)</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            :disabled="loading"
          ></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">Processing...</span>
          <span v-else>Continue to Payment</span>
        </button>
      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  cart: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  step: {
    type: Number,
    required: true
  }
})

const toast = useToast()
const loading = ref(false)
const errors = ref({})

const form = ref({
  first_name: '',
  last_name: '',
  phone: props.user.phone || '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'KE',
  shipping_method: 'standard',
  notes: ''
})

const submitShipping = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const response = await fetch(route('checkout.process.shipping'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(form.value)
    })
    
    const data = await response.json()
    
    if (data.success) {
      router.visit(data.redirect)
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        toast.error(data.message || 'Something went wrong')
      }
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  } finally {
    loading.value = false
  }
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}
</script>

<style scoped>
.checkout-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 40px 20px;
}

.checkout-steps {
  display: flex;
  justify-content: space-between;
  margin: 30px 0;
  position: relative;
}

.checkout-steps::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: #ddd;
  transform: translateY(-50%);
  z-index: 0;
}

.step {
  background: white;
  padding: 10px 20px;
  border-radius: 20px;
  border: 2px solid #ddd;
  z-index: 1;
  font-weight: bold;
  color: #999;
}

.step.active {
  border-color: #4CAF50;
  color: #4CAF50;
  background: #f0fff0;
}

.step.completed {
  border-color: #4CAF50;
  color: #4CAF50;
  background: #f0fff0;
}

.cart-summary-mini {
  background: #f5f5f5;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 30px;
  display: flex;
  justify-content: space-between;
}

.shipping-form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input, select, textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: #4CAF50;
}

.error {
  color: #ff4444;
  font-size: 14px;
  margin-top: 5px;
}

.btn {
  width: 100%;
  padding: 15px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 18px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn:hover:not(:disabled) {
  background: #45a049;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>