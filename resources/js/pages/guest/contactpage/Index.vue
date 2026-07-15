<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Phone, Mail } from '@lucide/vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import contactPageRoutes from '@/routes/callback-messages';

const form = useForm({
    name: '',
    phone_number: '',
    message: '',
});

const submitForm = () => {
    form.post(contactPageRoutes.store.url(), {
        preserveScroll: true,
        onSuccess:() => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Contact Page" />

    <section class="ContactPage flex items-center lg:min-h-[70dvh]">
        <div class="contact-page-wrapper px-4 lg:px-16 grid lg:grid-cols-2 gap-8">
            <div class="container-fluid space-y-4">
                <h2 class="text-xl font-semibold">Get in touch!</h2>

                <p class="lg:max-w-[80%]">Use the following details to contact us or fill in the form with your question or queries and we will be sure to give you a callback within 24 hours.</p>

                <div class="contact-details space-y-4">
                    <p class="flex items-center gap-1">
                        <Phone class="w-4 h-4" />
                        <span>+254 745 744 261</span>
                    </p>

                    <p class="flex items-center gap-1">
                        <Mail class="w-4 h-4" />
                        <span>info@alsuwa.com</span>
                    </p>
                </div>
            </div>

            <div class="container-fluid space-y-4">
                <h2 class="text-xl font-semibold">Request a callback!</h2>

                <form @submit.prevent="submitForm">
                    <div class="inputs-group">
                        <Label for="name" class="required">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Jane Doe"
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="inputs-group">
                        <Label for="name" class="required">Phone Number</Label>
                        <Input
                            id="phone_number"
                            v-model="form.phone_number"
                            type="text"
                            placeholder="+254 746 000 000"
                        />
                        <InputError :message="form.errors.phone_number" />
                    </div>

                    <div class="inputs-group">
                        <Label for="message" class="required">Message</Label>
                        <Textarea
                            id="message"
                            v-model="form.message"
                            rows="4"
                            placeholder="Enter your message..."
                        />
                        <InputError :message="form.errors.message" />
                    </div>

                    <div class="submit-buttons">
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ form.processing ? 'Sending your message...' : 'Request a callback' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
