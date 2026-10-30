<template>
    <main class="auth">
        <section class="auth__card">
            <p class="auth__eyebrow">SellPata</p>
            <h1 class="auth__title">কাস্টমার লগইন</h1>
            <p class="auth__lede">যে নম্বর দিয়ে অ্যাকাউন্ট খুলেছিলেন সেই নম্বর দিন।</p>

            <form class="auth__form" @submit.prevent="stage === 'phone' ? send() : verify()">
                <label class="field">
                    <span class="field__label">মোবাইল নম্বর</span>
                    <div class="field__group">
                        <span class="field__prefix">+880</span>
                        <input v-model="phone" class="field__input field__input--grouped" type="tel" inputmode="tel"
                               autocomplete="tel" placeholder="01712345678" maxlength="14" :disabled="stage === 'code' || busy">
                    </div>
                </label>

                <label v-if="stage === 'code'" class="field">
                    <span class="field__label">৬ ডিজিটের কোড</span>
                    <input ref="codeInput" v-model="code" class="field__input field__input--code"
                           type="text" inputmode="numeric" autocomplete="one-time-code"
                           maxlength="6" placeholder="------">
                </label>

                <div v-if="stage === 'code' && testCode" class="auth__demo" role="status">
                    <p class="auth__demo-title">ডেমো মোড — এসএমএস এখনো চালু হয়নি</p>
                    <p class="auth__demo-code">{{ testCode }}</p>
                    <button class="link" type="button" @click="fillCode">আবার বসিয়ে দিন</button>
                </div>

                <button class="auth__submit" type="submit" :disabled="busy">
                    {{ busy ? 'একটু অপেক্ষা করুন…' : (stage === 'phone' ? 'কোড পাঠান' : 'লগইন করুন') }}
                </button>
            </form>

            <p v-if="message" class="auth__note">{{ message }}</p>
            <p v-if="stage === 'code'" class="auth__note auth__note--muted">
                কোডটি পাননি?
                <button class="link" type="button" :disabled="busy" @click="send">আবার পাঠান</button>
            </p>

            <p class="auth__alt">
                <RouterLink to="/start">নতুন? ল্যান্ডিং পেজ বানিয়ে শুরু করুন</RouterLink>
            </p>
        </section>
    </main>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../lib/api';

const router = useRouter();
const phone = ref('');
const code = ref('');
const stage = ref('phone');
const busy = ref(false);
const message = ref('');
const codeInput = ref(null);
const testCode = ref(null);

async function send () {
    busy.value = true;
    message.value = '';
    try {
        const data = await api('/api/auth/send-otp', { method: 'POST', body: { phone: phone.value.trim() } });

        // Phone-only sign in: no code step while SMS is unwired.
        if (data.redirect) {
            await router.push(data.redirect);
            return;
        }

        stage.value = 'code';
        // Show the number the way it was matched, minus the prefix this field owns.
        if (data.phone) phone.value = data.phone.replace(/^\+?880/, '');
        testCode.value = data.test_code ? String(data.test_code) : null;
        message.value = testCode.value
            ? 'এই নম্বরে একটি কোড পাঠানো হয়েছে।'
            : 'এই নম্বরে কোনো অ্যাকাউন্ট পাওয়া যায়নি। নতুন? ল্যান্ডিং পেজ বানিয়ে শুরু করুন।';

        if (testCode.value) {
            code.value = testCode.value;
            await nextTick();
            codeInput.value?.focus();
        }
    } catch (error) {
        message.value = error.data?.message ?? 'কোড পাঠানো যায়নি।';
    } finally {
        busy.value = false;
    }
}

function fillCode () {
    if (!testCode.value) return;
    code.value = testCode.value;
    codeInput.value?.focus();
}

async function verify () {
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

<style scoped>
.auth{
    /* the gap under the header is deliberately tight so the form sits above the fold */
    padding:clamp(18px,3vw,34px) 20px 56px;
    background:#fbfaf5;
    min-height:calc(100vh - 72px);
    display:flex;
    justify-content:center;
    align-items:flex-start;
}
.auth__card{
    width:100%;
    max-width:432px;
    background:#fff;
    border:1px solid #e8e4d7;
    border-radius:18px;
    padding:30px 28px 26px;
    box-shadow:0 1px 2px rgba(15,42,36,.04);
}
.auth__eyebrow{
    margin:0 0 6px;
    font-size:.82rem;
    font-weight:700;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:#5d6763;
}
.auth__title{
    margin:0 0 8px;
    font-size:1.62rem;
    line-height:1.35;
    color:#0f2a24;
}
.auth__lede{
    margin:0 0 22px;
    color:#666;
    font-size:.95rem;
}
.auth__form{display:grid;gap:16px}
.field{display:grid;gap:7px}
.field__group{display:flex;align-items:stretch;border:1px solid #ddd8c9;border-radius:10px;background:#f4f4f2;overflow:hidden}
.field__group:focus-within{border-color:#16423c;box-shadow:0 0 0 3px rgba(22,66,60,.1)}
.field__prefix{display:flex;align-items:center;padding:0 12px;background:#eceade;color:#3c4a45;font-weight:600;font-size:.95rem;border-right:1px solid #ddd8c9}
.field__input--grouped{border:0!important;background:transparent!important;border-radius:0!important;box-shadow:none!important}
.auth__demo{margin-top:2px;padding:12px 14px;border:1px dashed #cbd8d2;border-radius:10px;background:#f2f8f5;text-align:center}
.auth__demo-title{margin:0 0 4px;font-size:.78rem;color:#4a625a}
.auth__demo-code{margin:0 0 6px;font-family:ui-monospace,Menlo,monospace;font-size:1.4rem;font-weight:700;letter-spacing:.28em;color:#0f2a24}
.field__label{font-size:.9rem;color:#4a5450}
.field__input{
    width:100%;
    padding:13px 15px;
    border:1px solid #e6e6e6;
    border-radius:12px;
    background:#f5f5f5;
    font-size:1rem;
    color:#0f2a24;
    transition:border-color .15s ease,background .15s ease;
}
.field__input::placeholder{color:#8a8f98}
.field__input:focus{
    outline:none;
    background:#fff;
    border-color:#0f2a24;
    box-shadow:0 0 0 3px rgba(15,42,36,.08);
}
.field__input:disabled{color:#8a8f98}
.field__input--code{
    letter-spacing:.42em;
    text-align:center;
    font-weight:600;
    font-variant-numeric:tabular-nums;
}
.auth__submit{
    width:100%;
    padding:14px 18px;
    border:0;
    border-radius:12px;
    background:#0f2a24;
    color:#fff;
    font-size:1rem;
    font-weight:600;
    cursor:pointer;
    transition:background .15s ease,transform .05s ease;
}
.auth__submit:hover{background:#17423a}
.auth__submit:active{transform:translateY(1px)}
.auth__submit:disabled{opacity:.6;cursor:not-allowed}
.auth__note{
    margin:14px 0 0;
    font-size:.9rem;
    color:#0f2a24;
}
.auth__note--muted{color:#666}
.auth__alt{
    margin:22px 0 0;
    padding-top:16px;
    border-top:1px solid #eee8d9;
    font-size:.9rem;
}
.auth__alt a{color:#155e47}
.link{
    border:0;
    background:none;
    padding:0;
    color:#155e47;
    font:inherit;
    text-decoration:underline;
    cursor:pointer;
}
.link:disabled{opacity:.5;cursor:not-allowed}
</style>
