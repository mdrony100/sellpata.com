<template>
    <div style="min-height:100vh;display:grid;place-items:center;padding:24px">
        <div style="width:100%;max-width:420px">
            <RouterLink to="/" style="font-weight:700;font-size:1.15rem;text-decoration:none">SellPata</RouterLink>
            <h1 style="margin:18px 0 6px;font-size:1.6rem">কাস্টমার লগইন</h1>
            <p style="color:var(--muted);margin:0 0 18px">যে নম্বর দিয়ে অ্যাকাউন্ট খুলেছিলেন সেই নম্বর দিন।</p>

            <label style="display:block;margin-bottom:12px">
                <span style="font-size:.9rem;color:var(--muted)">মোবাইল নম্বর</span>
                <input v-model="phone" type="tel" inputmode="tel" placeholder="যেমন 01712345678"
                       style="width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:10px;margin-top:6px" :disabled="stage === 'code'">
            </label>

            <label v-if="stage === 'code'" style="display:block;margin-bottom:12px">
                <span style="font-size:.9rem;color:var(--muted)">৬ ডিজিটের কোড</span>
                <input v-model="code" type="text" inputmode="numeric" maxlength="6" placeholder="------"
                       style="width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:10px;margin-top:6px">
            </label>

            <button v-if="stage === 'phone'" type="button" class="btn btn--primary" style="width:100%" :disabled="busy" @click="send">
                {{ busy ? 'পাঠানো হচ্ছে…' : 'কোড পাঠান' }}
            </button>
            <button v-else type="button" class="btn btn--primary" style="width:100%" :disabled="busy" @click="verify">
                {{ busy ? 'যাচাই হচ্ছে…' : 'লগইন করুন' }}
            </button>

            <p v-if="message" style="margin-top:12px;color:var(--muted);font-size:.92rem">{{ message }}</p>
            <p style="margin-top:18px;font-size:.9rem">
                <RouterLink to="/start">নতুন? ল্যান্ডিং পেজ বানিয়ে শুরু করুন</RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../lib/api';

const router = useRouter();
const phone = ref('');
const code = ref('');
const stage = ref('phone');
const busy = ref(false);
const message = ref('');

async function send() {
    busy.value = true;
    message.value = '';
    try {
        const data = await api('/api/auth/send-otp', { method: 'POST', body: { phone: phone.value.trim() } });
        stage.value = 'code';
        message.value = data.test_code ? `${data.message} (ডেমো কোড: ${data.test_code})` : data.message;
    } catch (error) {
        message.value = error.data?.message ?? 'কোড পাঠানো যায়নি।';
    } finally {
        busy.value = false;
    }
}

async function verify() {
    busy.value = true;
    message.value = '';
    try {
        const data = await api('/api/auth/verify', { method: 'POST', body: { phone: phone.value.trim(), code: code.value.trim() } });
        await router.push(data.redirect || '/dashboard');
    } catch (error) {
        message.value = error.data?.message ?? 'যাচাই করা যায়নি।';
    } finally {
        busy.value = false;
    }
}
</script>
