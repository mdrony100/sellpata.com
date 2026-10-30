<template>
    <section id="order" class="block order">
        <h2>অর্ডার করতে নিচের তথ্য পূরণ করুন</h2>
        <p v-if="landing.demo" class="order__demo" role="note">নমুনা পেজ — অর্ডার বা পেমেন্ট এখানে হবে না।</p>

        <div v-if="landing.done" class="order__done" role="status">
            <h3>ধন্যবাদ, অর্ডারটি পেয়েছি</h3>
            <p>অর্ডার নম্বর: <strong>{{ landing.done.reference }}</strong></p>
            <p>সর্বমোট: <strong>{{ landing.formatMoney(landing.done.total) }}</strong></p>
            <p>অর্ডারের অবস্থা: নতুন। পেমেন্টের অবস্থা: বাকি। ক্যাশ অন ডেলিভারিতে অর্ডার মানেই পরিশোধ নয়।</p>
            <button type="button" class="lp-cta lp-cta--ghost" @click="landing.resetForm">আরেকটি অর্ডার</button>
        </div>

        <div v-else class="order__grid">
            <form class="order__fields" novalidate @submit.prevent="landing.submitOrder">
                <label>আপনার নাম
                    <input v-model="landing.form.customer_name" type="text" autocomplete="name" required>
                </label>
                <label>মোবাইল নম্বর
                    <input v-model="landing.form.customer_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required>
                </label>
                <label>সম্পূর্ণ ডেলিভারি ঠিকানা
                    <textarea v-model="landing.form.address" rows="3" required></textarea>
                </label>
                <label v-if="landing.areas.length">ডেলিভারি এলাকা
                    <select v-model="landing.form.area_id" required>
                        <option value="">এলাকা বেছে নিন</option>
                        <option v-for="area in landing.areas" :key="area.id" :value="area.id">
                            {{ area.name }} — {{ landing.formatMoney(area.charge) }}
                        </option>
                    </select>
                </label>
                <label>নোট (ঐচ্ছিক)
                    <input v-model="landing.form.note" type="text">
                </label>
                <fieldset>
                    <legend>পেমেন্ট</legend>
                    <label v-for="method in landing.payments" :key="method.id" class="pay">
                        <input v-model="landing.form.payment_method" type="radio" name="payment" :value="method.id">
                        <span><strong>{{ method.label }}</strong><small>{{ method.hint }}</small></span>
                    </label>
                </fieldset>
            </form>

            <aside class="order__sum" aria-live="polite">
                <h3>অর্ডার সারাংশ</h3>
                <p class="order__item">{{ itemLabel }}</p>
                <label class="qty">পরিমাণ
                    <input :value="landing.quantity" type="number" min="1" max="99" inputmode="numeric" @input="landing.setQuantity($event.target.value)">
                </label>
                <dl>
                    <div><dt>পণ্যের দাম</dt><dd>{{ landing.formatMoney(landing.totals.subtotal) }}</dd></div>
                    <div><dt>ডেলিভারি</dt><dd>{{ areaChosen ? landing.formatMoney(landing.totals.delivery) : 'এলাকা বাকি' }}</dd></div>
                    <div v-if="landing.totals.discount"><dt>{{ landing.content.offer?.label || 'ছাড়' }}</dt><dd>− {{ landing.formatMoney(landing.totals.discount) }}</dd></div>
                    <div class="order__total"><dt>মোট পরিশোধ</dt><dd>{{ landing.formatMoney(landing.totals.total) }}</dd></div>
                </dl>
            </aside>

            <div class="order__submit">
                <p v-if="landing.error" class="order__error" role="alert">{{ landing.error }}</p>
                <button type="button" class="lp-cta" :disabled="landing.sending" @click="landing.submitOrder">
                    {{ landing.sending ? 'পাঠানো হচ্ছে…' : 'অর্ডার নিশ্চিত করুন' }}
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const itemLabel = computed(() => landing.selectedBundle?.name || landing.selectedVariant?.name || landing.content.product?.name || 'পণ্য');
const areaChosen = computed(() => !landing.areas.length || Boolean(landing.form.area_id));
</script>
